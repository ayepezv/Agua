<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('asiento.php');
require_model('asiento_factura.php');
require_model('ejercicio.php');
require_model('proveedor.php');

/**
 * Description of pagar_facturas
 *
 * @author Carlos García Gómez
 */
class pagar_facturas_prov extends fs_controller
{
   public $codproveedor;
   public $codserie;
   public $desde;
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
      
      $pro0 = new proveedor();
      $this->codproveedor = FALSE;
      $this->proveedor = FALSE;
      if( !isset($_POST['todos']) AND isset($_REQUEST['codproveedor']) )
      {
         $this->codproveedor = $_REQUEST['codproveedor'];
         $this->proveedor = $pro0->get($this->codproveedor);
      }
      
      $this->serie = new serie();
      $this->codserie = $this->empresa->codserie;
      if( isset($_REQUEST['codserie']) )
      {
         $this->codserie = $_REQUEST['codserie'];
      }
      
      if( isset($_REQUEST['buscar_proveedor']) )
      {
         $this->buscar_proveedor();
      }
      else if( in_array('tesoreria', $GLOBALS['plugins']) )
      {
         $this->new_error_msg('Si usas el <b>plugin Tesorería</b> no puedes usar este'
                 . ' asistente para pagar todas las facturas.');
      }
      else if( isset($_POST['idfactura']) )
      {
         /// ¿Marcamos ya las facturas?
         $num = 0;
         
         /// necesitamos el ejercicio actual
         $eje0 = new ejercicio();
         $ejercicio = $eje0->get_by_fecha($this->today());
         
         $asi0 = new asiento();
         $asifac = new asiento_factura();
         $fact0 = new factura_proveedor();
         foreach($_POST['idfactura'] as $id)
         {
            $factura = $fact0->get($id);
            if($factura)
            {
               $asiento = $asi0->get($factura->idasiento);
               if($asiento)
               {
                  /// comprobamos la subcuenta para el ejercicio actual
                  $proveedor = $pro0->get($factura->codproveedor);
                  if($proveedor AND $ejercicio)
                  {
                     $proveedor->get_subcuenta($ejercicio->codejercicio);
                  }
                  
                  $factura->idasientop = $asifac->generar_asiento_pago($asiento, $factura->codpago);
                  if($factura->idasientop)
                  {
                     $factura->pagada = TRUE;
                     if( $factura->save() )
                     {
                        $num++;
                     }
                  }
               }
               else
               {
                  $factura->pagada = TRUE;
                  if( $factura->save() )
                  {
                     $num++;
                  }
               }
            }
         }
         
         foreach($asifac->errors as $err)
         {
            $this->new_error_msg($err);
         }
         
         $this->new_message($num.' facturas marcadas como pagadas, estas son las siguientes.');
      }
      else
      {
         $this->share_extensions();
      }
      
      $this->resultados = $this->buscar_facturas();
   }
   
   private function share_extensions()
   {
      $extension = array(
          'name' => 'pagar_facturas',
          'page_from' => __CLASS__,
          'page_to' => 'compras_facturas',
          'type' => 'button',
          'text' => '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>',
          'params' => ''
      );
      $fsext = new fs_extension($extension);
      $fsext->save();
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
      $sql = "SELECT * FROM facturasprov WHERE pagada = false AND fecha >= ".$this->serie->var2str($this->desde).
              " AND fecha <= ".$this->serie->var2str($this->hasta).
              " AND codserie = ".$this->serie->var2str($this->codserie);
      
      if($this->codproveedor)
      {
         $sql .= " AND codproveedor = ".$this->serie->var2str($this->codproveedor);
      }
      
      $sql .= " ORDER BY fecha ASC, hora ASC";
      
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
}
