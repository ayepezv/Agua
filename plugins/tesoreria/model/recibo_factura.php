<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016-2017, Carlos García Gómez. All Rights Reserved. 
 */

require_model('asiento.php');
require_model('cuenta_banco_cliente.php');
require_model('cuenta_banco_proveedor.php');
require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('forma_pago.php');
require_model('forma_pago_plazo.php');
require_model('pago.php');
require_model('recibo_cliente.php');
require_model('recibo_proveedor.php');
require_model('subcuenta.php');

/**
 * Description of recibo_factura
 *
 * @author Carlos García Gómez
 */
class recibo_factura
{
   private $cbc;
   private $cbp;
   private $empresa;
   private $factura_cliente;
   private $factura_proveedor;
   private $forma_pago;
   private $plazo_pago;
   private $recibo_cliente;
   private $recibo_proveedor;
   
   public $errors;
   
   public function __construct()
   {
      $this->cbc = new cuenta_banco_cliente();
      $this->cbp = new cuenta_banco_proveedor();
      $this->empresa = new empresa();
      $this->errors = array();
      $this->factura_cliente = new factura_cliente();
      $this->factura_proveedor = new factura_proveedor();
      $this->forma_pago = new forma_pago();
      $this->plazo_pago = new forma_pago_plazo();
      $this->recibo_cliente = new recibo_cliente();
      $this->recibo_proveedor = new recibo_proveedor();
      
      /**
       * Para solucionar los efectos del antiguo bug al calcular el vencimiento
       * de las formas de pago, forzamos la comprobación de las mismas.
       * 
       * Eliminar esta comprobación en la siguiente actualización.
       */
      foreach($this->forma_pago->all() as $fp)
      {
         $vencimiento = $fp->vencimiento;
         $fp->test();
         
         if($fp->vencimiento != $vencimiento)
         {
            $fp->save();
         }
      }
   }
   
   private function new_error_msg($msg)
   {
      $this->errors[] = $msg;
   }
   
   /**
    * 
    * @param factura_cliente $factura
    */
   public function sync_factura_cli(&$factura)
   {
      if($factura)
      {
         $sc0 = new subcuenta();
         $pago0 = new pago();
         $pago0->cron_job();
         
         /// calculamos el saldo de la factura
         $saldo = 0;
         $recibos = $this->recibo_cliente->all_from_factura($factura->idfactura);
         foreach($recibos as $re)
         {
            $saldo += $re->importe;
            
            /// comprobamos nombre y cifnif
            if($re->cifnif != $factura->cifnif OR $re->nombrecliente = $factura->nombrecliente)
            {
               $re->cifnif = $factura->cifnif;
               $re->nombrecliente = $factura->nombrecliente;
            }
            
            /// además comprobamos la dirección
            if($re->direccion != $factura->direccion)
            {
               $re->direccion = $factura->direccion;
               $re->codpostal = $factura->codpostal;
               $re->apartado = $factura->apartado;
               $re->ciudad = $factura->ciudad;
               $re->provincia = $factura->provincia;
               $re->codpais = $factura->codpais;
               $re->save();
            }
         }
         
         /// ¿Estamos seguros de que no hay pagos previos?
         foreach($pago0->all_from_factura($factura->idfactura) as $pago)
         {
            if( is_null($pago->idrecibo) )
            {
               $recibo = new recibo_cliente();
               $recibo->cifnif = $factura->cifnif;
               $recibo->coddivisa = $factura->coddivisa;
               $recibo->tasaconv = $factura->tasaconv;
               $recibo->codpago = $factura->codpago;
               $recibo->codserie = $factura->codserie;
               $recibo->codcliente = $factura->codcliente;
               $recibo->nombrecliente = $factura->nombrecliente;
               $recibo->estado = 'Pagado';
               $recibo->fecha = $recibo->fechav = $recibo->fechap = $pago->fecha;
               $recibo->idfactura = $factura->idfactura;
               $recibo->importe = $pago->importe;
               $recibo->numero = $recibo->new_numero($recibo->idfactura);
               $recibo->codigo = $factura->codigo.'-'.sprintf('%02s', $recibo->numero);
               $recibo->observaciones = $pago->nota;
               
               if( $recibo->save() )
               {
                  $pago->idrecibo = $recibo->idrecibo;
                  $pago->save();
                  
                  $sc_pago = $sc0->get_cuentaesp('CAJA', $factura->codejercicio);
                  if($sc_pago)
                  {
                     $this->nuevo_pago_cli($recibo, $sc_pago->codsubcuenta);
                  }
                  else
                  {
                     $this->new_error_msg('No se ha encontrado una subcuenta de caja para generar el asiento del pago.');
                  }
                  
                  $recibos[] = $recibo;
                  $saldo += $recibo->importe;
               }
               else
               {
                  $this->new_error_msg('Error al generar el recibo.');
               }
            }
         }
         
         if($factura->pagada AND count($recibos) == 0)
         {
            /// no hacemos nada
         }
         else if($saldo < $factura->total)
         {
            $formap = $this->forma_pago->get($factura->codpago);
            if($formap AND !$this->empresa->floatcmp($saldo, $factura->total) )
            {
               $pendiente = $factura->total - $saldo;
               if($factura->total < 0)
               {
                  $pendiente = $factura->total + $saldo;
               }
               
               $plazos = $this->plazo_pago->all_from($formap->codpago);
               if($plazos)
               {
                  $pendiente2 = $pendiente;
                  foreach($plazos as $i => $pla)
                  {
                     $recibo = new recibo_cliente();
                     $recibo->cifnif = $factura->cifnif;
                     $recibo->coddivisa = $factura->coddivisa;
                     $recibo->tasaconv = $factura->tasaconv;
                     $recibo->codpago = $factura->codpago;
                     $recibo->codserie = $factura->codserie;
                     $recibo->codcliente = $factura->codcliente;
                     $recibo->nombrecliente = $factura->nombrecliente;
                     $recibo->estado = 'Emitido';
                     $recibo->fecha = $factura->fecha;
                     $recibo->fechav = Date('d-m-Y', strtotime($factura->fecha.' +'.$pla->dias.'days'));
                     $recibo->idfactura = $factura->idfactura;
                     
                     if( $i+1 == count($plazos) )
                     {
                        $recibo->importe = round($pendiente2, FS_NF0);
                     }
                     else
                     {
                        $recibo->importe = round($pendiente*$pla->aplazado/100, FS_NF0);
                        $pendiente2 -= $recibo->importe;
                     }
                     
                     $recibo->numero = $recibo->new_numero($recibo->idfactura);
                     $recibo->codigo = $factura->codigo.'-'.sprintf('%02s', $recibo->numero);
                     
                     foreach($this->cbc->all_from_cliente($factura->codcliente) as $cuenta)
                     {
                        if( is_null($recibo->codcuenta) OR $cuenta->principal )
                        {
                           $recibo->codcuenta = $cuenta->codcuenta;
                           $recibo->iban = $cuenta->iban;
                           $recibo->swift = $cuenta->swift;
                           $recibo->fmandato = $cuenta->fmandato;
                        }
                     }
                     
                     if( $recibo->save() )
                     {
                        $recibos[] = $recibo;
                     }
                     else
                     {
                        $this->new_error_msg('Error al generar el recibo.');
                     }
                  }
               }
               else
               {
                  $recibo = new recibo_cliente();
                  $recibo->cifnif = $factura->cifnif;
                  $recibo->coddivisa = $factura->coddivisa;
                  $recibo->tasaconv = $factura->tasaconv;
                  $recibo->codpago = $factura->codpago;
                  $recibo->codserie = $factura->codserie;
                  $recibo->codcliente = $factura->codcliente;
                  $recibo->nombrecliente = $factura->nombrecliente;
                  $recibo->estado = 'Emitido';
                  $recibo->fecha = $factura->fecha;
                  $recibo->fechav = Date('d-m-Y', strtotime($factura->fecha.' '.$formap->vencimiento));
                  $recibo->idfactura = $factura->idfactura;
                  
                  $recibo->importe = $pendiente;
                  
                  $recibo->numero = $recibo->new_numero($recibo->idfactura);
                  $recibo->codigo = $factura->codigo.'-'.sprintf('%02s', $recibo->numero);
                  
                  foreach($this->cbc->all_from_cliente($factura->codcliente) as $cuenta)
                  {
                     if( is_null($recibo->codcuenta) OR $cuenta->principal )
                     {
                        $recibo->codcuenta = $cuenta->codcuenta;
                        $recibo->iban = $cuenta->iban;
                        $recibo->swift = $cuenta->swift;
                        $recibo->fmandato = $cuenta->fmandato;
                     }
                  }
                  
                  if( $recibo->save() )
                  {
                     $recibos[] = $recibo;
                  }
                  else
                  {
                     $this->new_error_msg('Error al generar el recibo.');
                  }
               }
            }
         }
         
         if($recibos)
         {
            $pagado = 0;
            foreach($recibos as $res)
            {
               if($res->estado == 'Pagado')
               {
                  $pagado += $res->importe;
               }
            }
            
            if($factura->total == 0)
            {
               $factura->pagada = TRUE;
            }
            else
            {
               $factura->pagada = ( abs($factura->total - $pagado) <= 0.01 );
               if( !$factura->pagada AND $factura->idasientop )
               {
                  $asi0 = new asiento();
                  $asientop = $asi0->get($factura->idasientop);
                  if($asientop)
                  {
                     $asientop->delete();
                  }
                  
                  $factura->idasientop = NULL;
               }
            }
            
            $factura->save();
         }
         
         return $recibos;
      }
      else
      {
         return array();
      }
   }
   
   /**
    * 
    * @param factura_proveedor $factura
    */
   public function sync_factura_prov(&$factura)
   {
      if($factura)
      {
         $recibos = $this->recibo_proveedor->all_from_factura($factura->idfactura);
         
         if( count($recibos) == 0 AND $factura->pagada )
         {
            /// no hacemos nada
         }
         else if( count($recibos) == 0 )
         {
            $formap = $this->forma_pago->get($factura->codpago);
            if($formap)
            {
               $plazos = $this->plazo_pago->all_from($formap->codpago);
               if($plazos)
               {
                  $pendiente = $factura->total;
                  foreach($plazos as $i => $pla)
                  {
                     $recibo = new recibo_proveedor();
                     $recibo->cifnif = $factura->cifnif;
                     $recibo->coddivisa = $factura->coddivisa;
                     $recibo->tasaconv = $factura->tasaconv;
                     $recibo->codproveedor = $factura->codproveedor;
                     $recibo->nombreproveedor = $factura->nombre;
                     $recibo->estado = 'Emitido';
                     $recibo->fecha = $factura->fecha;
                     $recibo->fechav = Date('d-m-Y', strtotime($factura->fecha.' +'.$pla->dias.'days'));
                     $recibo->idfactura = $factura->idfactura;
                     $recibo->codpago = $factura->codpago;
                     $recibo->codserie = $factura->codserie;
                     
                     if( $i+1 == count($plazos) )
                     {
                        $recibo->importe = round($pendiente, FS_NF0);
                     }
                     else
                     {
                        $recibo->importe = round($factura->total*$pla->aplazado/100, FS_NF0);
                        $pendiente -= $recibo->importe;
                     }
                     
                     $recibo->numero = $recibo->new_numero($recibo->idfactura);
                     $recibo->codigo = $factura->codigo.'-'.sprintf('%02s', $recibo->numero);
                     
                     foreach($this->cbp->all_from_proveedor($recibo->codproveedor) as $cuenta)
                     {
                        if( is_null($recibo->codcuenta) OR $cuenta->principal )
                        {
                           $recibo->codcuenta = $cuenta->codcuenta;
                           $recibo->iban = $cuenta->iban;
                           $recibo->swift = $cuenta->swift;
                        }
                     }
                     
                     if( $recibo->save() )
                     {
                        $recibos[] = $recibo;
                     }
                     else
                     {
                        $this->new_error_msg('Error al generar el recibo.');
                     }
                  }
               }
               else
               {
                  $recibo = new recibo_proveedor();
                  $recibo->cifnif = $factura->cifnif;
                  $recibo->coddivisa = $factura->coddivisa;
                  $recibo->tasaconv = $factura->tasaconv;
                  $recibo->codproveedor = $factura->codproveedor;
                  $recibo->nombreproveedor = $factura->nombre;
                  $recibo->estado = 'Emitido';
                  $recibo->fecha = $factura->fecha;
                  $recibo->fechav = Date('d-m-Y', strtotime($factura->fecha.' '.$formap->vencimiento));
                  $recibo->idfactura = $factura->idfactura;
                  $recibo->codpago = $factura->codpago;
                  $recibo->codserie = $factura->codserie;
                  
                  $recibo->importe = $factura->total;
                  
                  $recibo->numero = $recibo->new_numero($recibo->idfactura);
                  $recibo->codigo = $factura->codigo.'-'.sprintf('%02s', $recibo->numero);
                  
                  foreach($this->cbp->all_from_proveedor($recibo->codproveedor) as $cuenta)
                  {
                     if( is_null($recibo->codcuenta) OR $cuenta->principal )
                     {
                        $recibo->codcuenta = $cuenta->codcuenta;
                        $recibo->iban = $cuenta->iban;
                        $recibo->swift = $cuenta->swift;
                     }
                  }
                  
                  if( $recibo->save() )
                  {
                     $recibos[] = $recibo;
                  }
                  else
                  {
                     $this->new_error_msg('Error al generar el recibo.');
                  }
               }
            }
         }
         else
         {
            $pagado = 0;
            foreach($recibos as $res)
            {
               if($res->estado == 'Pagado')
               {
                  $pagado += $res->importe;
               }
            }
            
            if($factura->total == 0)
            {
               $factura->pagada = TRUE;
            }
            else
            {
               $factura->pagada = ( abs($factura->total - $pagado) <= 0.01 );
            }
            
            $factura->save();
         }
         
         return $recibos;
      }
      else
      {
         return array();
      }
   }
   
   /**
    * 
    * @param recibo_cliente $recibo
    * @param type $codsubcuenta_pago
    * @param type $tipo
    * @param type $fecha
    */
   public function nuevo_pago_cli(&$recibo, $codsubcuenta_pago = FALSE, $tipo = 'Pago', $fecha = '', $genasiento = TRUE)
   {
      $error = FALSE;
      
      $pago = new pago_recibo_cliente();
      $pago->idrecibo = $recibo->idrecibo;
      $pago->tipo = $tipo;
      
      if($fecha)
      {
         $pago->fecha = $fecha;
      }
      
      if($this->empresa->contintegrada AND $codsubcuenta_pago AND $genasiento)
      {
         $cli0 = new cliente();
         $cliente = $cli0->get($recibo->codcliente);
         
         if($tipo == 'Pago')
         {
            $concepto = 'Cobro recibo '.$recibo->codigo.' - '.$recibo->nombrecliente;
            $inverso = FALSE;
         }
         else
         {
            $concepto = 'Devolución recibo '.$recibo->codigo.' - '.$recibo->nombrecliente;
            $inverso = TRUE;
         }
         
         $asientop = $this->nuevo_asiento_pago_cli(
                 $recibo->importe,
                 $recibo->coddivisa,
                 $recibo->tasaconv,
                 $concepto,
                 $cliente,
                 $codsubcuenta_pago,
                 $inverso,
                 $pago->fecha
         );
         if($asientop)
         {
            /// enlazamos el asiento con la factura
            $fact0 = new factura_cliente();
            $factura = $fact0->get($recibo->idfactura);
            if($factura)
            {
               $asientop->tipodocumento = "Factura de cliente";
               $asientop->documento = $factura->codigo;
               $asientop->save();
            }
            
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
               
               if($lin->codsubcuenta == $codsubcuenta_pago)
               {
                  /// salimos del bucle, ya no se asigna ninguna otra subcuenta
                  break;
               }
            }
         }
         else
         {
            $this->new_error_msg('Error al generar el asiento de pago.');
            $error = TRUE;
         }
      }
      
      if(!$error)
      {
         if( $pago->save() )
         {
            if($pago->tipo == 'Pago')
            {
               $recibo->estado = 'Pagado';
               $recibo->fechap = $pago->fecha;
            }
            else
            {
               $recibo->estado = 'Devuelto';
            }
            
            if( !$recibo->save() )
            {
               $error = TRUE;
            }
         }
      }
      
      return !$error;
   }
   
   /**
    * 
    * @param type $importe
    * @param type $coddivisa
    * @param type $tasaconv
    * @param type $concepto
    * @param type $cliente
    * @param type $codsubcuenta_pago
    * @param type $inverso
    * @param type $fecha
    * @return \asiento
    */
   public function nuevo_asiento_pago_cli($importe, $coddivisa, $tasaconv, $concepto, $cliente = FALSE, $codsubcuenta_pago = FALSE, $inverso = FALSE, $fecha = '')
   {       
       $tasaconv2 = $tasaconv;
      $tasaconv = 1;
      if($coddivisa != $this->empresa->coddivisa)
      {
         $div0 = new divisa();
         $divisa = $div0->get($this->empresa->coddivisa);
         if($divisa)
         {
            $tasaconv = $divisa->tasaconv / $tasaconv2;
            $tasaconv2 = $divisa->tasaconv;
         }
      }
      
      $nasiento = FALSE;
      $asiento = new asiento();
      $asiento->editable = FALSE;
      $asiento->importe = round($importe*$tasaconv, FS_NF0);
      $asiento->concepto = $concepto;
      
      if($fecha)
      {
         $asiento->fecha = $fecha;
      }
      
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($asiento->fecha);
      if($ejercicio)
      {
         $asiento->codejercicio = $ejercicio->codejercicio;
      }
      
      if($cliente)
      {
         $subcuenta_cli = $cliente->get_subcuenta($ejercicio->codejercicio);
      }
      else
      {
         /// buscamos la cuenta 0 de clientes
         $sc0 = new subcuenta();
         $subcuenta_cli = $sc0->get_cuentaesp('CLIENT', $ejercicio->codejercicio);
      }
      
      $subc0 = new subcuenta();
      if($codsubcuenta_pago)
      {
         $subcaja = $subc0->get_by_codigo($codsubcuenta_pago, $ejercicio->codejercicio);
      }
      else
      {
         $subcaja = $subc0->get_cuentaesp('CAJA', $ejercicio->codejercicio);
      }
      
      if(!$ejercicio)
      {
         $this->new_error_msg('Ningún ejercico encontrado.');
      }
      else if( !$ejercicio->abierto() )
      {
         $this->new_error_msg('El ejercicio '.$ejercicio->codejercicio.' está cerrado.');
      }
      else if( !$subcuenta_cli )
      {
         $this->new_error_msg("No se ha podido generar una subcuenta para el cliente "
                 . "<a href='".$ejercicio->url()."'>¿Has importado los datos del ejercicio?</a>");
      }
      else if( !$subcaja )
      {
         $this->new_error_msg("No se ha encontrado la subcuenta de caja "
                 . "<a href='".$ejercicio->url()."'>¿Has importado los datos del ejercicio?</a>");
      }
      else if( $asiento->save() )
      {
         $partida1 = new partida();
         $partida1->idasiento = $asiento->idasiento;
         $partida1->concepto = $asiento->concepto;
         $partida1->idsubcuenta = $subcuenta_cli->idsubcuenta;
         $partida1->codsubcuenta = $subcuenta_cli->codsubcuenta;
         
         if($inverso)
         {
            $partida1->debe = round($importe*$tasaconv, FS_NF0);
         }
         else
         {
            $partida1->haber = round($importe*$tasaconv, FS_NF0);
         }
         
         $partida1->coddivisa = $this->empresa->coddivisa;
         $partida1->tasaconv = $tasaconv2;
         $partida1->save();
         
         $partida2 = new partida();
         $partida2->idasiento = $asiento->idasiento;
         $partida2->concepto = $asiento->concepto;
         $partida2->idsubcuenta = $subcaja->idsubcuenta;
         $partida2->codsubcuenta = $subcaja->codsubcuenta;
         
         if($inverso)
         {
            $partida2->haber = round($importe*$tasaconv, FS_NF0);
         }
         else
         {
            $partida2->debe = round($importe*$tasaconv, FS_NF0);
         }
         
         $partida2->coddivisa = $this->empresa->coddivisa;
         $partida2->tasaconv = $tasaconv2;
         $partida2->save();
         
         $nasiento = $asiento;
      }
      else
      {
         $this->new_error_msg('Error al guardar el asiento.');
      }
      
      return $nasiento;
   }
   
   /**
    * 
    * @param recibo_proveedor $recibo
    * @param type $codsubcuenta_pago
    * @param type $tipo
    * @param type $fecha
    */
   public function nuevo_pago_prov(&$recibo, $codsubcuenta_pago = FALSE, $tipo = 'Pago', $fecha = '', $genasiento = TRUE)
   {
      $error = FALSE;
      
      $pago = new pago_recibo_proveedor();
      $pago->idrecibo = $recibo->idrecibo;
      $pago->tipo = $tipo;
      
      if($fecha)
      {
         $pago->fecha = $fecha;
      }
      
      if($this->empresa->contintegrada AND $codsubcuenta_pago AND $genasiento)
      {
         $pro0 = new proveedor();
         $proveedor = $pro0->get($recibo->codproveedor);
         
         if($tipo == 'Pago')
         {
            $concepto = 'Pago recibo de compra '.$recibo->codigo.' - '.$recibo->nombreproveedor;
            $inverso = FALSE;
         }
         else
         {
            $concepto = 'Devolución recibo de compra '.$recibo->codigo.' - '.$recibo->nombreproveedor;
            $inverso = TRUE;
         }
         
         $asientop = $this->nuevo_asiento_pago_prov(
                 $recibo->importe,
                 $recibo->coddivisa,
                 $recibo->tasaconv,
                 $concepto,
                 $proveedor,
                 $codsubcuenta_pago,
                 $inverso,
                 $pago->fecha
         );
         if($asientop)
         {
            /// enlazamos el asiento con la factura
            $fact0 = new factura_proveedor();
            $factura = $fact0->get($recibo->idfactura);
            if($factura)
            {
               $asientop->tipodocumento = "Factura de proveedor";
               $asientop->documento = $factura->codigo;
               $asientop->save();
            }
            
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
               
               if($lin->codsubcuenta == $codsubcuenta_pago)
               {
                  /// salimos del bucle, ya no se asigna ninguna otra subcuenta
                  break;
               }
            }
         }
         else
         {
            $this->new_error_msg('Error al generar el asiento de pago.');
            $error = TRUE;
         }
      }
      
      if(!$error)
      {
         if( $pago->save() )
         {
            if($pago->tipo == 'Pago')
            {
               $recibo->estado = 'Pagado';
               $recibo->fechap = $pago->fecha;
            }
            else
            {
               $recibo->estado = 'Devuelto';
            }
            
            if( !$recibo->save() )
            {
               $this->new_error_msg('Error al guardar el pago.');
               $error = TRUE;
            }
         }
      }
      
      return !$error;
   }
   
   /**
    * Genera un asiento de pago a proveedor.
    * @param type $importe
    * @param type $coddivisa
    * @param type $tasaconv
    * @param type $concepto
    * @param type $proveedor
    * @param type $codsubcuenta_pago
    * @param type $inverso
    * @param type $fecha
    * @return \asiento
    */
   public function nuevo_asiento_pago_prov($importe, $coddivisa, $tasaconv, $concepto, $proveedor = FALSE, $codsubcuenta_pago = FALSE, $inverso = FALSE, $fecha = '')
   {
      $tasaconv2 = $tasaconv;
      $tasaconv = 1;
      if($coddivisa != $this->empresa->coddivisa)
      {
         $div0 = new divisa();
         $divisa = $div0->get($this->empresa->coddivisa);
         if($divisa)
         {
            $tasaconv = $divisa->tasaconv_compra / $tasaconv2;
            $tasaconv2 = $divisa->tasaconv_compra;
         }
      }
      
      $nasiento = FALSE;
      $asiento = new asiento();
      $asiento->editable = FALSE;
      $asiento->importe = round($importe*$tasaconv, FS_NF0);
      $asiento->concepto = $concepto;
      
      if($fecha)
      {
         $asiento->fecha = $fecha;
      }
      
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($asiento->fecha);
      if($ejercicio)
      {
         $asiento->codejercicio = $ejercicio->codejercicio;
      }
      
      if($proveedor)
      {
         $subcuenta_pro = $proveedor->get_subcuenta($ejercicio->codejercicio);
      }
      else
      {
         /// buscamos la cuenta 0 de proveedores
         $sc0 = new subcuenta();
         $subcuenta_pro = $sc0->get_cuentaesp('PROVEE', $ejercicio->codejercicio);
      }
      
      $subc0 = new subcuenta();
      if($codsubcuenta_pago)
      {
         $subcaja = $subc0->get_by_codigo($codsubcuenta_pago, $ejercicio->codejercicio);
      }
      else
      {
         $subcaja = $subc0->get_cuentaesp('CAJA', $ejercicio->codejercicio);
      }
      
      if(!$ejercicio)
      {
         $this->new_error_msg('Ningún ejercico encontrado.');
      }
      else if( !$ejercicio->abierto() )
      {
         $this->new_error_msg('El ejercicio '.$ejercicio->codejercicio.' está cerrado.');
      }
      else if( !$subcuenta_pro )
      {
         $this->new_error_msg("No se ha podido generar una subcuenta para el proveedor "
                 . "<a href='".$ejercicio->url()."'>¿Has importado los datos del ejercicio?</a>");
      }
      else if( !$subcaja )
      {
         $this->new_error_msg("No se ha encontrado la subcuenta de caja "
                 . "<a href='".$ejercicio->url()."'>¿Has importado los datos del ejercicio?</a>");
      }
      else if( $asiento->save() )
      {
         $partida1 = new partida();
         $partida1->idasiento = $asiento->idasiento;
         $partida1->concepto = $asiento->concepto;
         $partida1->idsubcuenta = $subcuenta_pro->idsubcuenta;
         $partida1->codsubcuenta = $subcuenta_pro->codsubcuenta;
         
         if($inverso)
         {
            $partida1->haber = round($importe*$tasaconv, FS_NF0);
         }
         else
         {
            $partida1->debe = round($importe*$tasaconv, FS_NF0);
         }
         
         $partida1->coddivisa = $this->empresa->coddivisa;
         $partida1->tasaconv = $tasaconv2;
         $partida1->save();
         
         $partida2 = new partida();
         $partida2->idasiento = $asiento->idasiento;
         $partida2->concepto = $asiento->concepto;
         $partida2->idsubcuenta = $subcaja->idsubcuenta;
         $partida2->codsubcuenta = $subcaja->codsubcuenta;
         
         if($inverso)
         {
            $partida2->debe = round($importe*$tasaconv, FS_NF0);
         }
         else
         {
            $partida2->haber = round($importe*$tasaconv, FS_NF0);
         }
         
         $partida2->coddivisa = $this->empresa->coddivisa;
         $partida2->tasaconv = $tasaconv2;
         $partida2->save();
         
         $nasiento = $asiento;
      }
      else
      {
         $this->new_error_msg('Error al guardar el asiento.');
      }
      
      return $nasiento;
   }
}
