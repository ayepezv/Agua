<?php

/**
 * @author Carlos GarcÃ­a GÃ³mez      neorazorx@gmail.com
 * @copyright 2014-2016, Carlos GarcÃ­a GÃ³mez. All Rights Reserved. 
 */

require_model('asiento.php');
require_model('cliente.php');
require_model('cuenta_banco.php');
require_model('divisa.php');
require_model('factura_cliente.php');
require_model('pago_recibo_cliente.php');
require_model('recibo_cliente.php');
require_model('recibo_factura.php');
require_model('subcuenta.php');

/**
 * Description of pagar_facturas
 *
 * @author Carlos GarcÃ­a GÃ³mez
 */
class pagar_facturas extends fs_controller
{
   public $cliente;
   public $codcliente;
   public $codserie;
   public $codsubcuenta_pago;
   public $desde;
   public $fecha_pago;
   public $hasta;
   public $resultados;
   public $serie;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Pagar facturas', 'ventas', FALSE, FALSE);
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
      
      $this->cliente = FALSE;
      $this->codcliente = FALSE;
      if( isset($_REQUEST['codcliente']) AND !isset($_POST['todos']) )
      {
         $this->codcliente = $_REQUEST['codcliente'];
         
         $cli0 = new cliente();
         $this->cliente = $cli0->get($this->codcliente);
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
      
      if( isset($_REQUEST['buscar_cliente']) )
      {
         $this->buscar_cliente();
      }
      else if( isset($_POST['idfactura']) )
      {
         if($this->codcliente)
         {
            $this->pagar_facturas_cliente();
         }
         else
         {
            $this->pagar_facturas_cliente();
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
      $fsext->to = 'ventas_facturas';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>';
      $fsext->save();
      
      $fsext2 = new fs_extension();
      $fsext2->name = 'pagar_recibos';
      $fsext2->from = __CLASS__;
      $fsext2->to = 'ventas_recibos';
      $fsext2->type = 'button';
      $fsext2->text = '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>';
      $fsext2->save();
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
   
   private function buscar_facturas()
   {
      $facturas = array();
      $sql = "SELECT * FROM facturascli WHERE pagada = false"
              ." AND fecha >= ".$this->serie->var2str($this->desde)
              ." AND fecha <= ".$this->serie->var2str($this->hasta)
              ." AND codserie = ".$this->serie->var2str($this->codserie);
      
      if($this->codcliente)
      {
         $sql .= " AND codcliente = ".$this->serie->var2str($this->codcliente);
      }
      
      $sql .= " ORDER BY fecha ASC, codigo ASC";
      
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $facturas[] = new factura_cliente($d);
         }
      }
      
      return $facturas;
   }
   
   private function pagar_facturas()
   {
      $num = 0;
      $pagos ="";
      
      $fac0 = new factura_cliente();
      $rec0 = new recibo_cliente();
      $ref0 = new recibo_factura();
      foreach($_POST['idfactura'] as $id)
      {
         $error = FALSE;
         
         $recibos = $rec0->all_from_factura($id);
         foreach($recibos as $recibo)
         {
            if($recibo->estado != 'Pagado')
            {
               if( !$ref0->nuevo_pago_cli($recibo, $this->codsubcuenta_pago, 'Pago', $this->fecha_pago) )
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
                  $pagos = $factura->observaciones;
               }
            }
         }
      }
      
      $this->new_message($num.' facturas marcadas como pagadas, estas son las siguientes. Último Pago.'.$pagos);
   }
   
   private function pagar_facturas_cliente()
   {
      $rec0 = new recibo_cliente();
      $error = FALSE;
      $num = 0;
      $pagos = "";
      
      /// Â¿Generamos el asiento de pago?
      $asientop = NULL;
      if($this->empresa->contintegrada)
      {
         /// Â¿Cuanto es el total?
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
            $asientop = $ref0->nuevo_asiento_pago_cli(
                    $importe,
                    $coddivisa,
                    $tasaconv,
                    'Cobro de facturas de '.$this->cliente->nombre,
                    $this->cliente,
                    $this->codsubcuenta_pago,
                    FALSE,
                    $this->fecha_pago
            );
            if($asientop)
            {
               $fac1 = new factura_cliente();
               $meses = 0;
               $facturas = '';
               $mes='';
                foreach($_POST['idfactura'] as $id)
                {       
                        $factura = $fac1->get($id);
                        if($factura)
                        {      $meses ++;
                               $pagos=$factura->observaciones;
                               //$cadena_buscada ="Consumo:";
                               //$posicion_coincidencia = strpos($pagos, $cadena_buscada);
                               //$longitud_cadena = strlen($pagos);
                               //$text1= substr($pagos,0,$posicion_coincidencia-1);
                               //$text2= substr($pagos, $posicion_coincidencia, $longitud_cadena);
                               //$mes=$mes.$factura->mes_texto."-".substr($factura->anio,-2)."; "; -> así estaba antes, con esto solo saca los dos últimos caracteres del año
							   $mes=$mes.$factura->mes_texto."-".substr($factura->anio,-0)."; ";
							   $mesunico=$factura->mes_texto."-".substr($factura->anio,-0)."; ";
                               $sql = "SELECT referencia FROM lineasfacturascli WHERE idfactura =".$factura->idfactura;
                               $rubro = $this->db->select_limit($sql,1,0);
                               $consumo = "Cuenta:".$factura->cuenta." Lec Act: ".$factura->lec_actual." - Lec Ant: ".$factura->lec_anterior." = Consumo: ".$factura->consumo." m3 - Mes: ".$mesunico;                               
                               $facturas = $facturas.$factura->idfactura.",";
                               $factura->idasientop = $asientop->idasiento;
                               $factura->fecha_pago = date("Y-m-d");
                               $factura->hora_pago = date("H:i:s");
                               $nombre = $factura->nombrecliente;
                               $cifnif = $factura->cifnif;
							   $codigo_cliente=$factura->codcliente;
                               $factura->save();
                        }                        
                }
                if (current($rubro[0])<>'Agua Variable') {
                                $meses=0;
                                $mes='';
                                $consumo=$pagos;
                               }
                ///$this->new_message('<a href="'.$asientop->url().'">Asiento de pago</a> generados. <a href="page=ventas_imprimir&factura=TRUE&tipo=simple&id='.$asientop->idasiento.'"> .Imprimir');
               ///$this->new_message('Nombre Cliente:'.$this->cliente->nombre.' Cedula cliente'.$this->cliente->cifnif.' Valor:'.$importe);
               $this->new_message('<a target="_new" href="factura_batan.php?cliente='.$nombre.'&cedula='.$cifnif.'&valor='.$importe.'&fecha1='.$this->fecha_pago.'&Tex='.$mes.'&Tex1='.$consumo.'&Me='.$meses.'&Det='.current($rubro[0]).'">IMPRIMIR FACTURA');
               $this->new_message('<a href="'.$asientop->url().'">Asiento de pago</a> generado.');
			   $this->new_message('<a target="_new" href="plugins/JRSA/factura_electronica.php?asiento='.$factura->idasientop.'&codcliente='.$codigo_cliente.'">Generar Factura Electrónica</a>.');               
			   $sql3 = "SELECT importe FROM co_asientos WHERE idasiento =".$factura->idasientop;			   			   
               $neto = $this->db->select_limit($sql3,1,0);				
				$base_imponible=0;
				foreach ($neto as $bike)
				{
				 foreach ($bike as $bikes)
					{
					 $base_imponible=$bikes[0];					 
					}
				}
				$elimina=array('"','=','-',' ');
				$cifnif=str_replace($elimina,'',$cifnif);
				$sql2="INSERT INTO ecomprobantes_jrsa(id_asiento, id_cliente, nombre_cliente, ci_ruc_cliente, base_impoble, fecha_crea, tipo, estado)VALUES (".$factura->idasientop.",'".$codigo_cliente."','".$nombre."','".$cifnif."',".$base_imponible.",now(),1,0);";
				$this->new_message($sql2);
				//$id_secuencia=$this->db->exec($sql2);
				//$this->new_message("Id secuencia ".$id_secuencia);
			   ///$this->new_message('<button class="btn btn-sm btn-primary" type="button" data-toggle="modal" data-target="#modal_imprimir"><span class="glyphicon glyphicon-ok"></span><span class="hidden-xs">&nbsp; Imprimir</span></button>');
               
               /// ponemos algo en el tipo, para que no lo tengan en cuenta los informes
               $asientop->tipodocumento = 'Facturas de cliente';
               $asientop->documento = $facturas;
               $asientop->observacion = $consumo;
               $asientop->rubro = current($rubro[0]);
               $asientop->meses = $mes;
               $asientop->num_meses = $meses;
               $asientop->nombre = $nombre;
               $asientop->cifnif = $cifnif;
               $asientop->save();
			   
            }
            else
            {
               /// mostramos los errores al generar los asientos
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
         $fac0 = new factura_cliente();
         foreach($_POST['idfactura'] as $id)
         {
            $recibos = $rec0->all_from_factura($id);
            foreach($recibos as $recibo)
            {
               if($recibo->estado != 'Pagado')
               {
                  $pago = new pago_recibo_cliente();
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
         /// aÃ±adimos todas las subcuentas de caja
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