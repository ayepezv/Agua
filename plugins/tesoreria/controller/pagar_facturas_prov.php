<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('asiento.php');
require_model('cuenta_banco.php');
require_model('divisa.php');
require_model('factura_proveedor.php');
require_model('pago_recibo_proveedor.php');
require_model('proveedor.php');
require_model('recibo_factura.php');
require_model('recibo_proveedor.php');
require_model('subcuenta.php');

/**
 * Description of pagar_facturas
 *
 * @author Carlos García Gómez
 */
class pagar_facturas_prov extends fs_controller
{
   public $codproveedor;
   public $codserie;
   public $codsubcuenta_pago;
   public $cuenta_banco;
   public $desde;
   public $fecha_pago;
   public $hasta;
   public $proveedor;
   public $resultados;
   public $serie;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Pagar facturas', 'compras', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->desde = Date('01-01-Y');
      if( isset($_REQUEST['desde']) )
      {
         $this->desde = $_REQUEST['desde'];
      }
      
      $this->hasta = Date('t-m-Y');
      if( isset($_REQUEST['hasta']) )
      {
         $this->hasta = $_REQUEST['hasta'];
      }
      
      $this->codproveedor = FALSE;
      if( isset($_REQUEST['codproveedor']) AND !isset($_POST['todos']) )
      {
         $this->codproveedor = $_REQUEST['codproveedor'];
         
         $pro0 = new proveedor();
         $this->proveedor = $pro0->get($this->codproveedor);
      }
      
      $this->serie = new serie();
      $this->codserie = $this->empresa->codserie;
      if( isset($_REQUEST['codserie']) )
      {
         $this->codserie = $_REQUEST['codserie'];
      }
      
      $this->cuenta_banco = new cuenta_banco();
      $this->codsubcuenta_pago = FALSE;
      if( isset($_POST['codsubcuenta']) )
      {
         $this->codsubcuenta_pago = $_POST['codsubcuenta'];
      }
      
      $this->fecha_pago = $this->today();
      if( isset($_POST['fecha']) )
      {
         $this->fecha_pago = $_POST['fecha'];
      }
      
      if( isset($_REQUEST['buscar_proveedor']) )
      {
         $this->buscar_proveedor();
      }
      else if( isset($_POST['idfactura']) )
      {
         if($this->codproveedor)
         {
            $this->pagar_facturas_proveedor();
         }
         else
         {
            $this->pagar_facturas();
         }
      }
      else
      {
         $this->share_extensions();
      }
      
      $this->resultados = $this->buscar_facturas();
   }
   
   private function share_extensions()
   {
      $fsext = new fs_extension();
      $fsext->name = 'pagar_facturas';
      $fsext->from = __CLASS__;
      $fsext->to = 'compras_facturas';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>';
      $fsext->save();
      
      $fsext2 = new fs_extension();
      $fsext2->name = 'pagar_recibos';
      $fsext2->from = __CLASS__;
      $fsext2->to = 'compras_recibos';
      $fsext2->type = 'button';
      $fsext2->text = '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>';
      $fsext2->save();
   }
   
   private function buscar_proveedor()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $proveedor = new proveedor();
      $json = array();
      foreach($proveedor->search($_REQUEST['buscar_proveedor']) as $pro)
      {
         $json[] = array('value' => $pro->razonsocial, 'data' => $pro->codproveedor);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_proveedor'], 'suggestions' => $json) );
   }
   
   private function buscar_facturas()
   {
      $facturas = array();
      $sql = "SELECT * FROM facturasprov WHERE pagada = false"
              . " AND fecha >= ".$this->serie->var2str($this->desde)
              . " AND fecha <= ".$this->serie->var2str($this->hasta)
              . " AND codserie = ".$this->serie->var2str($this->codserie);
      
      if($this->codproveedor)
      {
         $sql .= " AND codproveedor = ".$this->serie->var2str($this->codproveedor);
      }
      
      $sql .= " ORDER BY fecha ASC, codigo ASC";
      
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $facturas[] = new factura_proveedor($d);
         }
      }
      
      return $facturas;
   }
   
   private function pagar_facturas()
   {
      $num = 0;
      
      $fac0 = new factura_proveedor();
      $ref0 = new recibo_factura();
      $rec0 = new recibo_proveedor();
      foreach($_POST['idfactura'] as $id)
      {
         $error = FALSE;
         
         $recibos = $rec0->all_from_factura($id);
         foreach($recibos as $recibo)
         {
            if($recibo->estado != 'Pagado')
            {
               if( !$ref0->nuevo_pago_prov($recibo, $this->codsubcuenta_pago, 'Pago', $this->fecha_pago) )
               {
                  $error = TRUE;
                  break;
               }
            }
         }
         
         /// mostramos los errores al generar los asientos
         foreach($ref0->errors as $err)
         {
            $this->new_error_msg($err);
            $error = TRUE;
         }
         
         if($error)
         {
            break;
         }
         else
         {
            /// marcamos la factura como pagada
            $factura = $fac0->get($id);
            if($factura)
            {
               $factura->pagada = TRUE;
               if( $factura->save() )
               {
                  $num++;
               }
            }
         }
      }
      
      $this->new_message($num.' facturas marcadas como pagadas, estas son las siguientes.');
   }
   
   private function pagar_facturas_proveedor()
   {
      $rec0 = new recibo_proveedor();
      $error = FALSE;
      $num = 0;
      
      /// ¿Generamos el asiento de pago?
      $asientop = NULL;
      if($this->empresa->contintegrada)
      {
         /// ¿Cuanto es el total?
         $coddivisa = NULL;
         $importe = 0;
         $tasaconv = 1;
         foreach($_POST['idfactura'] as $id)
         {
            $recibos = $rec0->all_from_factura($id);
            foreach($recibos as $recibo)
            {
               if($recibo->estado != 'Pagado')
               {
                  if( is_null($coddivisa) OR $recibo->coddivisa == $coddivisa )
                  {
                     $coddivisa = $recibo->coddivisa;
                     $importe += $recibo->importe;
                     $tasaconv = $recibo->tasaconv;
                  }
                  else
                  {
                     $this->new_error_msg('Todos los recibos a pagar deben ser de la misma divisa.');
                     $error = TRUE;
                     break;
                  }
               }
            }
         }
         
         if(!$error)
         {
            $ref0 = new recibo_factura();
            $asientop = $ref0->nuevo_asiento_pago_prov(
                    $importe,
                    $coddivisa,
                    $tasaconv,
                    'Pago facturas de compra a '.$this->proveedor->nombre,
                    $this->proveedor,
                    $this->codsubcuenta_pago,
                    FALSE,
                    $this->fecha_pago
            );
            if($asientop)
            {
               $this->new_message('<a href="'.$asientop->url().'">Asiento de pago</a> generado.');
               
               /// ponemos algo en el tipo, para que no lo tengan en cuenta los informes
               $asientop->tipodocumento = 'Facturas de proveedor';
               $asientop->documento = $this->proveedor->codproveedor;
               $asientop->save();
            }
            else
            {
               /// mostramos los errores al generar el asiento
               foreach($ref0->errors as $err)
               {
                  $this->new_error_msg($err);
               }
               
               $this->new_error_msg('Error al generar el asiento.');
               $error = TRUE;
            }
         }
      }
      
      if(!$error)
      {
         $fac0 = new factura_proveedor();
         foreach($_POST['idfactura'] as $id)
         {
            $recibos = $rec0->all_from_factura($id);
            foreach($recibos as $recibo)
            {
               if($recibo->estado != 'Pagado')
               {
                  $pago = new pago_recibo_proveedor();
                  $pago->idrecibo = $recibo->idrecibo;
                  $pago->fecha = $this->fecha_pago;
                  
                  if($asientop)
                  {
                     $pago->idasiento = $asientop->idasiento;
                     
                     /// nos guardamos la subcuenta
                     foreach($asientop->get_partidas() as $lin)
                     {
                        /**
                         * Por si acaso no se ha seleccionado una subcuenta de pago,
                         * nos guardamos alguna.
                         */
                        $pago->codsubcuenta = $lin->codsubcuenta;
                        $pago->idsubcuenta = $lin->idsubcuenta;
                        
                        if($lin->codsubcuenta == $this->codsubcuenta_pago)
                        {
                           /// salimos del bucle, ya no se asigna ninguna otra subcuenta
                           break;
                        }
                     }
                  }
                  
                  if( $pago->save() )
                  {
                     $recibo->estado = 'Pagado';
                     $recibo->fechap = $this->fecha_pago;
                     
                     if( !$recibo->save() )
                     {
                        $error = TRUE;
                        break;
                     }
                  }
               }
            }
            
            if($error)
            {
               break;
            }
            else
            {
               /// marcamos la factura como pagada
               $factura = $fac0->get($id);
               if($factura)
               {
                  $factura->pagada = TRUE;
                  if( $factura->save() )
                  {
                     $num++;
                  }
               }
            }
         }
      }
      
      $this->new_message($num.' facturas marcadas como pagadas, estas son las siguientes.');
   }
   
   public function get_subcuentas_pago()
   {
      $subcuentas_pago = array();
      
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($this->today());
      if($ejercicio)
      {
         /// añadimos todas las subcuentas de caja
         $sql = "SELECT * FROM co_subcuentas WHERE idcuenta IN "
                 . "(SELECT idcuenta FROM co_cuentas WHERE codejercicio = "
                 . $ejercicio->var2str($ejercicio->codejercicio)." AND idcuentaesp = 'CAJA');";
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               $subcuentas_pago[] = new subcuenta($d);
            }
         }
      }
      
      return $subcuentas_pago;
   }
}
