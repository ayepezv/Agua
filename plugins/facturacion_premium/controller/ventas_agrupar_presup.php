<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2017, Carlos García Gómez. All Rights Reserved. 
 */

require_model('cliente.php');
require_model('divisa.php');
require_model('ejercicio.php');
require_model('pedido_cliente.php');
require_model('presupuesto_cliente.php');
require_model('serie.php');

/**
 * Description of ventas_agrupar_presup
 *
 * @author Carlos García Gómez
 */
class ventas_agrupar_presup extends fs_controller
{
   public $cliente;
   public $coddivisa;
   public $codserie;
   public $desde;
   public $divisa;
   public $hasta;
   public $resultados;
   public $serie;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Agrupar '.FS_PRESUPUESTOS, 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->share_extension();
      $this->cliente = FALSE;
      $this->divisa = new divisa();
      $this->resultados = FALSE;
      $this->serie = new serie();
      
      $this->coddivisa = $this->empresa->coddivisa;
      if( isset($_REQUEST['coddivisa']) )
      {
         $this->coddivisa = $_REQUEST['coddivisa'];
      }
      
      $this->codserie = $this->empresa->codserie;
      if( isset($_REQUEST['codserie']) )
      {
         $this->codserie = $_REQUEST['codserie'];
      }
      
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
      
      /// el desde no puede ser mayor que el hasta
      if( strtotime($this->desde) > strtotime($this->hasta) )
      {
         $this->hasta = $this->desde;
      }
      
      if( isset($_REQUEST['buscar_cliente']) )
      {
         $this->buscar_cliente();
      }
      else if( isset($_REQUEST['codcliente']) )
      {
         $cli0 = new cliente();
         $this->cliente = $cli0->get($_REQUEST['codcliente']);
         
         if($this->cliente)
         {
            $this->resultados = $this->buscar_presupuestos();
            
            if( isset($_POST['cantidad_0']) )
            {
               $this->agrupar_presupuestos();
               $this->resultados = FALSE;
            }
         }
      }
   }
   
   private function share_extension()
   {
      $fsext = new fs_extension();
      $fsext->name = __CLASS__;
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_presupuestos';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-duplicate"></span><span class="hidden-xs">&nbsp; Agrupar</span>';
      $fsext->save();
   }
   
   private function buscar_cliente()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $cliente = new cliente();
      $json = array();
      foreach($cliente->search($_REQUEST['buscar_cliente']) as $cli)
      {
         $json[] = array('value' => $cli->razonsocial, 'data' => $cli->codcliente);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_cliente'], 'suggestions' => $json) );
   }
   
   private function buscar_presupuestos()
   {
      $plist = array();
      $sql = "SELECT * FROM presupuestoscli WHERE codcliente = ".$this->cliente->var2str($this->cliente->codcliente);
      $sql .= " AND fecha >= ".$this->cliente->var2str($this->desde);
      $sql .= " AND fecha <= ".$this->cliente->var2str($this->hasta);
      $sql .= " AND codserie = ".$this->cliente->var2str($this->codserie);
      $sql .= " AND coddivisa = ".$this->cliente->var2str($this->coddivisa);
      $sql .= " AND status = ".$this->cliente->var2str(0).' ORDER BY fecha DESC;';
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $plist[] = new presupuesto_cliente($d);
         }
      }
      
      return $plist;
   }
   
   private function agrupar_presupuestos()
   {
      $continuar = TRUE;
      $pedido = new pedido_cliente();
      $pedido_rellenado = FALSE;
      $num = 0;
      
      /// asignamos el ejercicio
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($pedido->fecha);
      if($ejercicio)
      {
         $pedido->codejercicio = $ejercicio->codejercicio;
      }
      
      foreach($this->resultados as $pres)
      {
         foreach($pres->get_lineas() as $lin)
         {
            if(!$continuar)
            {
               break;
            }
            else if( !isset($_POST['idl_'.$num]) )
            {
               /// línea no seleccionada
            }
            else if($lin->idlinea == intval($_POST['idl_'.$num]))
            {
               if(!$pedido_rellenado)
               {
                  $pedido->codagente = $this->user->codagente;
                  $pedido->codalmacen = $pres->codalmacen;
                  $pedido->coddivisa = $pres->coddivisa;
                  $pedido->tasaconv = $pres->tasaconv;
                  $pedido->codpago = $pres->codpago;
                  $pedido->codserie = $pres->codserie;
                  $pedido->irpf = $pres->irpf;
                  $pedido->cifnif = $this->cliente->cifnif;
                  $pedido->codcliente = $this->cliente->codcliente;
                  $pedido->nombrecliente = $this->cliente->razonsocial;
                  $pedido->apartado = '';
                  $pedido->ciudad = '';
                  $pedido->codpais = $this->empresa->codpais;
                  $pedido->codpostal = '';
                  $pedido->direccion = '';
                  $pedido->provincia = '';
                  
                  foreach($this->cliente->get_direcciones() as $dir)
                  {
                     if($dir->domfacturacion)
                     {
                        $pedido->apartado = $dir->apartado;
                        $pedido->ciudad = $dir->ciudad;
                        $pedido->coddir = $dir->id;
                        $pedido->codpais = $dir->codpais;
                        $pedido->codpostal = $dir->codpostal;
                        $pedido->direccion = $dir->direccion;
                        $pedido->provincia = $dir->provincia;
                        break;
                     }
                  }
                  
                  if( $pedido->save() )
                  {
                     $pedido_rellenado = TRUE;
                  }
                  else
                  {
                     $continuar = FALSE;
                     $this->new_error_msg('Error al agrupar el pedido.');
                  }
               }
               
               $linea = new linea_pedido_cliente();
               $linea->idlineapresupuesto = $lin->idlinea;
               $linea->idpedido = $pedido->idpedido;
               $linea->idpresupuesto = $pres->idpresupuesto;
               $linea->mostrar_cantidad = $lin->mostrar_cantidad;
               $linea->mostrar_precio = $lin->mostrar_precio;
               $linea->referencia = $lin->referencia;
               $linea->codcombinacion = $lin->codcombinacion;
               $linea->descripcion = $lin->descripcion;
               $linea->cantidad = floatval($_POST['cantidad_'.$num]);
               $linea->pvpunitario = $lin->pvpunitario;
               $linea->codimpuesto = $lin->codimpuesto;
               $linea->dtopor = $lin->dtopor;
               $linea->irpf = $lin->irpf;
               $linea->iva = $lin->iva;
               $linea->recargo = $lin->recargo;
               $linea->pvpsindto = $linea->pvpunitario * $linea->cantidad;
               $linea->pvptotal = $linea->pvpunitario * $linea->cantidad * (100 - $linea->dtopor) / 100;
               
               if(!$continuar)
               {
                  break;
               }
               else if( $linea->save() )
               {
                  $pedido->neto += $linea->pvptotal;
                  $pedido->totaliva += ($linea->pvptotal * $linea->iva/100);
                  $pedido->totalirpf += ($linea->pvptotal * $linea->irpf/100);
                  $pedido->totalrecargo += ($linea->pvptotal * $linea->recargo/100);
               }
               else
               {
                  $this->new_error_msg("¡Imposible guardar la linea con referencia: ".$linea->referencia);
                  $continuar = FALSE;
               }
            }
            
            $num++;
         }
         
         if( isset($_POST['aprobado']) AND $continuar )
         {
            if( in_array($pres->idpresupuesto, $_POST['aprobado']) )
            {
               $pres->editable = FALSE;
               $pres->idpedido = $pedido->idpedido;
               $pres->status = 1;
               $pres->save();
            }
         }
      }
      
      if($continuar)
      {
         /// redondeamos
         $pedido->neto = round($pedido->neto, FS_NF0);
         $pedido->totaliva = round($pedido->totaliva, FS_NF0);
         $pedido->totalirpf = round($pedido->totalirpf, FS_NF0);
         $pedido->totalrecargo = round($pedido->totalrecargo, FS_NF0);
         $pedido->total = $pedido->neto + $pedido->totaliva - $pedido->totalirpf + $pedido->totalrecargo;
         
         if( $pedido->save() )
         {
            $this->new_message('<a href="'.$pedido->url().'">'.ucfirst(FS_PEDIDO).'</a> generado correctamente.');
            header( 'Location: '.$pedido->url() );
         }
         else
         {
            $this->new_error_msg('Error al generar el '.FS_PEDIDO);
            $pedido->delete();
         }
      }
      else if( !is_null($pedido->idpedido) )
      {
         $pedido->delete();
      }
   }
   
   /// Devuelve la cantidad servida de esta línea
   public function linea_servida($idlinea)
   {
      $data = $this->db->select("SELECT * FROM lineaspedidoscli WHERE idlineapresupuesto = ".$this->empresa->var2str($idlinea).";");
      if($data)
      {
         return floatval($data[0]['cantidad']);
      }
      else
         return 0;
   }
   
   public function pendientes()
   {
      $pendientes = array();
      $presupuesto = new presupuesto_cliente();
      
      $offset = 0;
      $presupuestos = $presupuesto->all_ptepedir($offset);
      while($presupuestos)
      {
         foreach($presupuestos as $pre)
         {
            if($pre->codcliente)
            {
               /// Comprobamos si el cliente ya está en la lista.
               $encontrado = FALSE;
               foreach($pendientes as $i => $pe)
               {
                  if($pre->codcliente == $pe['codcliente'] AND $pre->codserie == $pe['codserie'] AND $pre->coddivisa == $pe['coddivisa'])
                  {
                     $encontrado = TRUE;
                     $pendientes[$i]['num']++;
                     
                     if( strtotime($pre->fecha) < strtotime($pe['desde']) )
                     {
                        $pendientes[$i]['desde'] = $pre->fecha;
                     }
                     
                     if( strtotime($pre->fecha) > strtotime($pe['hasta']) )
                     {
                        $pendientes[$i]['hasta'] = $pre->fecha;
                     }
                     
                     break;
                  }
               }
               
               /// Añadimos a la liusta de pendientes.
               if(!$encontrado)
               {
                  $pendientes[] = array(
                      'codcliente' => $pre->codcliente,
                      'nombre' => $pre->nombrecliente,
                      'codserie' => $pre->codserie,
                      'coddivisa' => $pre->coddivisa,
                      'desde' => date('d-m-Y', min( array( strtotime($pre->fecha), strtotime($this->desde) ) )),
                      'hasta' => date('d-m-Y', max( array( strtotime($pre->fecha), strtotime($this->hasta) ) )),
                      'num' => 1
                  );
               }
            }
            
            $offset++;
         }
         
         $presupuestos = $presupuesto->all_ptepedir($offset);
      }
      
      return $pendientes;
   }
}
