<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2017, Carlos García Gómez. All Rights Reserved. 
 */

require_model('asiento.php');
require_model('cliente.php');
require_model('cuenta_banco.php');
require_model('cuenta_banco_cliente.php');
require_model('ejercicio.php');
require_model('factura_cliente.php');
require_model('forma_pago.php');
require_model('pago.php');
require_model('pago_recibo_cliente.php');
require_model('partida.php');
require_model('recibo_cliente.php');
require_model('recibo_factura.php');
require_model('subcuenta.php');

/**
 * Description of ventas_recibo
 *
 * @author Carlos García Gómez
 */
class ventas_recibo extends fs_controller
{
   public $allow_delete;
   public $anticipo;
   public $cliente;
   public $cuenta_banco;
   public $ejercicio;
   public $factura;
   public $forma_pago;
   public $pagos;
   public $recibo;
   public $recibos;
   public $subcuenta_cli;
   public $subcuentas_pago;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Recibo', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $recibo = new recibo_cliente();
      $ref0 = new recibo_factura();
      $pago = new pago_recibo_cliente();
      $this->recibo = FALSE;
      if( isset($_REQUEST['id']) )
      {
         $this->recibo = $recibo->get($_REQUEST['id']);
      }
      
      if($this->recibo)
      {
         $this->page->title = 'Recibo '.$this->recibo->codigo;
         
         /// cargamos la factura y las subcuentas
         $this->cuenta_banco = new cuenta_banco();
         $fact = new factura_cliente();
         $this->factura = $fact->get($this->recibo->idfactura);
         $this->forma_pago = new forma_pago();
         $this->get_subcuentas();
         
         if( isset($_POST['fechav']) )
         {
            $this->recibo->importe = floatval($_POST['importe']);
            $this->recibo->tasaconv = floatval($_POST['tasaconv']);
            $this->recibo->fecha = $_POST['emitido'];
            $this->recibo->fechav = $_POST['fechav'];
            $this->recibo->iban = $_POST['iban'];
            $this->recibo->swift = $_POST['swift'];
            $this->recibo->observaciones = $_POST['observaciones'];
            
            if( isset($_POST['codpago']) )
            {
               $this->recibo->codpago = $_POST['codpago'];
            }
            
            if( $this->recibo->save() )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar los datos.');
         }
         else if( isset($_POST['nuevopago']) )
         {
            $codsubcuenta = FALSE;
            if( isset($_POST['codsubcuenta']) )
            {
               $codsubcuenta = $_POST['codsubcuenta'];
            }
            $genasiento = isset($_POST['generarasiento']);
            
            if( $ref0->nuevo_pago_cli($this->recibo, $codsubcuenta, $_POST['tipo'], $_POST['fecha'], $genasiento) )
            {
               $this->new_message('Pago guardado correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar los pagos.');
         }
         else if( isset($_GET['deletep']) )
         {
            foreach($pago->all_from_recibo($this->recibo->idrecibo) as $pg)
            {
               if( $pg->idpagodevol == intval($_GET['deletep']) )
               {
                  if( $pg->delete() )
                  {
                     $this->new_message($pg->tipo.' eliminado correctamente');
                     
                     $this->recibo->estado = 'Emitido';
                     $this->recibo->save();
                  }
                  else
                     $this->new_error_msg('Error al eliminar el '.$pg->tipo);
                  
                  break;
               }
            }
         }
         else if( isset($_GET['sacar_remesa']) )
         {
            $this->recibo->idremesa = NULL;
            
            if( $this->recibo->save() )
            {
               $this->new_message('Recibo desvinculado de la remesa correctamente.');
            }
            else
            {
               $this->new_error_msg('Error al desvincular de la remesa.');
            }
         }
         
         $this->pagos = $pago->all_from_recibo($this->recibo->idrecibo);
         $this->recibos = $this->recibo->all_from_factura($this->recibo->idfactura);
         $ref0->sync_factura_cli($this->factura);
      }
      else
      {
         $this->new_error_msg('Recibo no encontrado.', 'error', FALSE, FALSE);
      }
   }
   
   public function url()
   {
      if($this->recibo)
      {
         return $this->recibo->url();
      }
      else
         return parent::url();
   }
   
   public function iframe_xid()
   {
      $txt = "<div class='hidden'><iframe src='https://www.facturascripts.com/comm3/index.php?page=community_stats"
              . "&add=TRUE&version=".$this->version()."&xid=".$this->empresa->xid."&plugins=".join(',',$GLOBALS['plugins'])."'>"
              . "</iframe></div>";
      return $txt;
   }
   
   private function get_subcuentas()
   {
      $this->ejercicio = FALSE;
      $this->subcuenta_cli = FALSE;
      $this->subcuentas_pago = array();
      
      $eje0 = new ejercicio();
      if( isset($_POST['fecha']) )
      {
         $this->ejercicio = $eje0->get_by_fecha($_POST['fecha']);
      }
      else
         $this->ejercicio = $eje0->get_by_fecha($this->today());
      
      if($this->ejercicio)
      {
         $subcuenta = new subcuenta();
         $cli = new cliente();
         $this->cliente = $cli->get($this->recibo->codcliente);
         if($this->cliente)
         {
            if($this->empresa->contintegrada)
            {
               $this->subcuenta_cli = $this->cliente->get_subcuenta($this->ejercicio->codejercicio);
            }
         }
         
         /// añadimos la subcuenta de la cuenta bancaria
         $formap = $this->forma_pago->get($this->recibo->codpago);
         if($formap)
         {
            if($formap->codcuenta)
            {
               $cuentab = $this->cuenta_banco->get($formap->codcuenta);
               if($cuentab)
               {
                  $subc = $subcuenta->get_by_codigo($cuentab->codsubcuenta, $this->ejercicio->codejercicio);
                  if($subc)
                  {
                     $this->subcuentas_pago[] = $subc;
                  }
               }
            }
         }
         
         /// añadimos todas las subcuentas de caja
         $sql = "SELECT * FROM co_subcuentas WHERE idcuenta IN "
                 . "(SELECT idcuenta FROM co_cuentas WHERE codejercicio = "
                 . $this->ejercicio->var2str($this->ejercicio->codejercicio)." AND idcuentaesp = 'CAJA');";
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               $this->subcuentas_pago[] = new subcuenta($d);
            }
         }
      }
   }
}
