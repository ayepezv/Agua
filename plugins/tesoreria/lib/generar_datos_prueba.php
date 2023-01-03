<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016-2017, Carlos García Gómez. All Rights Reserved.
 */

require_once 'plugins/FSDK/lib/generar_datos_prueba.php';
require_model('cuenta_banco.php');
require_model('remesa.php');

/**
 * Clase con todo tipo de funciones para generar datos aleatorios.
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class generar_datos_prueba_crm extends generar_datos_prueba
{
   public function __construct(&$db, &$empresa)
   {
      parent::__construct($db, $empresa);
   }
   
   public function remesas($max = 50)
   {
      $num = 0;
      
      /// comprobamos las formas de pago
      $encontrada = FALSE;
      foreach($this->formas_pago as $fp)
      {
         if($fp->domiciliado)
         {
            $encontrada = TRUE;
         }
      }
      if(!$encontrada OR mt_rand(0, 19) == 0)
      {
         $this->nueva_forma_pago();
      }
      
      $cb0 = new cuenta_banco();
      $div0 = new divisa();
      while($num < $max)
      {
         $reme = new remesa();
         $reme->descripcion = $this->empresa();
         $reme->fecha = date( mt_rand(1, 28).'-'.mt_rand(1, 12).'-'.mt_rand(2013, 2016) );
         $reme->fechacargo = date('d-m-Y', strtotime($reme->fecha.' +3 months'));
         
         foreach($this->formas_pago as $fp)
         {
            if($fp->domiciliado)
            {
               $reme->codpago = $fp->codpago;
               
               $cuentab = $cb0->get($fp->codcuenta);
               if($cuentab)
               {
                  $reme->codcuenta = $cuentab->codcuenta;
                  $reme->iban = $cuentab->iban;
                  $reme->swift = $cuentab->swift;
               }
               
               $divisa = $div0->get($this->empresa->coddivisa);
               if($divisa)
               {
                  $reme->coddivisa = $divisa->coddivisa;
                  $reme->tasaconv = $divisa->tasaconv;
               }
               break;
            }
         }
         
         if( $reme->save() )
         {
            $num++;
         }
         else
         {
            break;
         }
      }
      
      return $num;
   }
   
   private function nueva_forma_pago()
   {
      $cuentab = FALSE;
      $cb0 = new cuenta_banco();
      foreach($cb0->all() as $cb)
      {
         $cuentab = $cb;
      }
      if(!$cuentab OR mt_rand(0, 4) == 0)
      {
         $cuentab = $this->nueva_cuenta_bancaria();
      }
      
      $fp = new forma_pago();
      $fp->codpago = mt_rand(99, 9999);
      $fp->descripcion = 'Forma pago '.$fp->codpago;
      $fp->domiciliado = TRUE;
      $fp->codcuenta = $cuentab->codcuenta;
      if( $fp->save() )
      {
         $this->formas_pago = $fp->all();
      }
   }
   
   private function nueva_cuenta_bancaria()
   {
      $cb = new cuenta_banco();
      $cb->descripcion = 'Cuenta '.mt_rand(9, 999);
      $cb->iban = 'ES'.mt_rand(1111, 9999).mt_rand(1111, 9999).mt_rand(1111, 9999).mt_rand(1111, 9999);
      $cb->save();
      
      return $cb;
   }
}
