<?php

/**
 * @author Carlos García Gómez         neorazorx@gmail.com
 * @author Francesc Pineda Segarra     shawe.ewahs@gmail.com
 * @copyright 2015-2017, Carlos García Gómez. All Rights Reserved.
 * @copyright 2015-2016, Francesc Pineda Segarra. All Rights Reserved.
 */

use AbcAeffchen\SepaUtilities\SepaUtilities;
use AbcAeffchen\Sephpa\SephpaDirectDebit;

require_once 'plugins/tesoreria/lib/sephpa/SepaUtilities.php';
require_once 'plugins/tesoreria/lib/sephpa/SephpaDirectDebit.php';
require_once 'plugins/tesoreria/lib/sephpa/payment-collections/SepaPaymentCollection.php';
require_once 'plugins/tesoreria/lib/sephpa/payment-collections/SepaDirectDebit00800102.php';

require_model('cliente.php');
require_model('cuenta_banco.php');
require_model('forma_pago.php');
require_model('pago_recibo_cliente.php');
require_model('recibo_cliente.php');
require_model('recibo_factura.php');
require_model('remesa.php');

/**
 * Description of remesas
 *
 * @author Carlos García Gómez
 */
class remesas extends fs_controller
{
   public $allow_delete;
   public $b_desde;
   public $b_estado;
   public $b_hasta;
   public $codsubcuenta_pago;
   public $cuentab;
   public $cuentab_s;
   public $forma_pago;
   public $num_resultados;
   public $offset;
   public $remesa;
   public $resultados;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Remesas', 'contabilidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $this->codsubcuenta_pago = $this->codsubcuenta_pago();
      $this->cuentab = new cuenta_banco();
      $this->cuentab_s = FALSE;
      $this->forma_pago = new forma_pago();
      $this->resultados = array();
      
      $reme = new remesa();
      if( isset($_POST['nueva']) )
      {
         $this->nueva_remesa($reme);
         $this->resultados = $reme->all();
      }
      else if( isset($_REQUEST['id']) )
      {
         $this->cargar_remesa($reme);
      }
      else if( isset($_GET['delete']) )
      {
         $remesa = $reme->get($_GET['delete']);
         if($remesa)
         {
            if( $remesa->delete() )
            {
               $this->new_message('Remesa eliminada correctamente.');
            }
            else
            {
               $this->new_error_msg('Imposible eliminar la remesa.');
            }
         }
         
         $this->resultados = $reme->all();
         $this->liberar_recibos();
      }
      else if( isset($_GET['gdp']) )
      {
         $this->generar_datos_prueba();
      }
      else
      {
         $this->share_extensions();
         
         $this->liberar_recibos();
         $this->buscar();
      }
   }
   
   private function buscar()
   {
      $this->num_resultados = 0;
      $this->resultados = array();
      
      $this->b_desde = FALSE;
      if( isset($_REQUEST['b_desde']) )
      {
         $this->b_desde = $_REQUEST['b_desde'];
      }
      
      $this->b_estado = FALSE;
      if( isset($_REQUEST['b_estado']) )
      {
         $this->b_estado = $_REQUEST['b_estado'];
      }
      
      $this->b_hasta = FALSE;
      if( isset($_REQUEST['b_hasta']) )
      {
         $this->b_hasta = $_REQUEST['b_hasta'];
      }
      
      $this->offset = 0;
      if( isset($_REQUEST['offset']) )
      {
         $this->offset = intval($_REQUEST['offset']);
      }
      
      $sql = 'FROM remesas_sepa';
      $and = ' WHERE';
      
      if($this->b_desde)
      {
         $sql .= $and.' fecha >= '.$this->empresa->var2str($this->b_desde);
         $and = ' AND';
      }
      
      if($this->b_hasta)
      {
         $sql .= $and.' fecha <= '.$this->empresa->var2str($this->b_hasta);
         $and = ' AND';
      }
      
      if($this->b_estado)
      {
         $sql .= $and.' estado = '.$this->empresa->var2str($this->b_estado);
         $and = ' AND';
      }
      
      if($this->query)
      {
         $sql .= $and." lower(descripcion) LIKE '%".$this->empresa->no_html($this->query)."%'";
         $and = ' AND';
      }
      
      $data = $this->db->select(' SELECT COUNT(*) AS num '.$sql);
      if($data)
      {
         $this->num_resultados = intval($data[0]['num']);
         
         $sql .= ' ORDER BY fecha DESC';
         $data2 = $this->db->select_limit('SELECT * '.$sql, FS_ITEM_LIMIT, $this->offset);
         if($data2)
         {
            foreach($data2 as $d)
            {
               $this->resultados[] = new remesa($d);
            }
         }
      }
   }
   
   public function paginas()
   {
      $paginas = array();
      $i = 0;
      $num = 0;
      $actual = 1;
      $total = $this->num_resultados;
      
      $url = $this->url().'&query='.$this->query
              .'&b_desde='.$this->b_desde
              .'&b_estado='.$this->b_estado
              .'&b_hasta='.$this->b_hasta;
      
      /// añadimos todas la página
      while($num < $total)
      {
         $paginas[$i] = array(
             'url' => $url."&offset=".($i*FS_ITEM_LIMIT),
             'num' => $i + 1,
             'actual' => ($num == $this->offset)
         );
         
         if($num == $this->offset)
         {
            $actual = $i;
         }
         
         $i++;
         $num += FS_ITEM_LIMIT;
      }
      
      /// ahora descartamos
      foreach($paginas as $j => $value)
      {
         $enmedio = intval($i/2);
         
         /**
          * descartamos todo excepto la primera, la última, la de enmedio,
          * la actual, las 5 anteriores y las 5 siguientes
          */
         if( ($j>1 AND $j<$actual-5 AND $j!=$enmedio) OR ($j>$actual+5 AND $j<$i-1 AND $j!=$enmedio) )
         {
            unset($paginas[$j]);
         }
      }
      
      if( count($paginas) > 1 )
      {
         return $paginas;
      }
      else
      {
         return array();
      }
   }
   
   /**
    * 
    * @param remesa $reme
    */
   private function nueva_remesa(&$reme)
   {
      $reme->descripcion = $_POST['descripcion'];
      $reme->fechacargo = $_POST['fechacargo'];
      
      $formap = $this->forma_pago->get($_POST['codpago']);
      if($formap)
      {
         $reme->codpago = $formap->codpago;
         
         /// buscamos la cuenta bancaria asociada a la forma de pago
         $cuentab = $this->cuentab->get($formap->codcuenta);
         if($cuentab)
         {
            $reme->codcuenta = $cuentab->codcuenta;
            $reme->iban = $cuentab->iban;
            $reme->swift = $cuentab->swift;
         }
      }
      
      $div0 = new divisa();
      $divisa = $div0->get($this->empresa->coddivisa);
      if($divisa)
      {
         $reme->coddivisa = $divisa->coddivisa;
         $reme->tasaconv = $divisa->tasaconv;
      }
      
      $reme->new_creditorid();
      
      if( is_null($reme->codcuenta) )
      {
         $this->new_error_msg('La <a href="'.$this->forma_pago->url().'">forma de pago</a>'
                 . ' seleccionada no tiene una cuenta bancaria asociada.');
      }
      else if( $reme->save() )
      {
         $this->new_message('Datos guardados correctamente.');
         header('Location: '.$reme->url());
      }
      else
      {
         $this->new_error_msg('Error al guardar los datos.');
      }
   }
   
   /**
    * 
    * @param remesa $reme
    */
   private function cargar_remesa(&$reme)
   {
      $this->remesa = $reme->get($_REQUEST['id']);
      if($this->remesa)
      {
         $this->cuentab_s = $this->cuentab->get($this->remesa->codcuenta);
         if($this->cuentab_s)
         {
            if($this->cuentab_s->codsubcuenta)
            {
               $this->codsubcuenta_pago = $this->cuentab_s->codsubcuenta;
            }
         }
         
         if( isset($_GET['download']) )
         {
            $recli = new recibo_cliente();
            $this->resultados = $recli->all_from_remesa($this->remesa->idremesa);
            
            $this->download();
         }
         else
         {
            $this->template = 'editar_remesa';
            $this->modificar_remesa();
            
            $recli = new recibo_cliente();
            $this->resultados = $recli->all_from_remesa($this->remesa->idremesa);
            
            /// calculamos el total
            $this->remesa->total = 0;
            foreach($this->resultados as $res)
            {
               $this->remesa->total += $res->importe;
            }
            $this->remesa->save();
         }
      }
      else
      {
         $this->new_error_msg('Remesa no encontrada.');
      }
   }
   
   private function codsubcuenta_pago()
   {
      $codsubcuenta = FALSE;
      
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
               $subcuentas_pago = new subcuenta($d);
               $codsubcuenta = $subcuentas_pago->codsubcuenta;
               break;
            }
         }
      }
      
      return $codsubcuenta;
   }
   
   public function formas_pago_domiciliadas()
   {
      $lista = array();
      
      foreach($this->forma_pago->all() as $fp)
      {
         if($fp->domiciliado)
         {
            $lista[] = $fp;
         }
      }
      
      return $lista;
   }
   
   public function recibos_disponibles()
   {
      $lista = array();
      
      $sql = "SELECT * FROM reciboscli WHERE idremesa IS NULL AND estado != 'Pagado'"
              . " AND fechav <= ".$this->remesa->var2str($this->remesa->fechacargo)
              . " AND coddivisa = ".$this->remesa->var2str($this->empresa->coddivisa)
              . " AND (codpago = ".$this->remesa->var2str($this->remesa->codpago)
              . " OR codpago IN (SELECT codpago FROM formaspago WHERE domiciliado"
              . " AND codcuenta = ".$this->remesa->var2str($this->remesa->codcuenta)."))"
              . " ORDER BY fechav ASC;";
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new recibo_cliente($d);
         }
      }
      
      return $lista;
   }
   
   private function modificar_remesa()
   {
      if( isset($_POST['descripcion']) )
      {
         $this->remesa->descripcion = $_POST['descripcion'];
         $this->remesa->creditorid = $_POST['creditorid'];
         $this->remesa->fecha = $_POST['fecha'];
         $this->remesa->fechacargo = $_POST['fechacargo'];
         $this->remesa->estado = $_POST['estado'];
         
         if( $this->remesa->save() )
         {
            $this->new_message('Datos guardados correctamente.');
         }
         else
         {
            $this->new_error_msg('Error al guardar los datos.');
         }
      }
      else if( isset($_GET['pagar']) )
      {
         $this->pagar_recibos();
      }
      else if( !$this->remesa->editable() AND (isset($_POST['addrecibo']) OR isset($_GET['sacar'])) )
      {
         $this->new_error_msg('Solamente se pueden hacer cambios en remesas <b>en trámite</b>.');
      }
      else if( isset($_POST['addrecibo']) )
      {
         $nuevos = 0;
         $recli = new recibo_cliente();
         foreach($_POST['addrecibo'] as $id)
         {
            $recibo = $recli->get($id);
            if($recibo)
            {
               $recibo->idremesa = $this->remesa->idremesa;
               if( $recibo->save() )
               {
                  $nuevos++;
               }
            }
         }
         
         $this->new_message($nuevos.' recibos añadidos a la remesa.');
      }
      else if( isset($_GET['sacar']) )
      {
         $recli = new recibo_cliente();
         $recibo = $recli->get($_GET['sacar']);
         if($recibo)
         {
            $recibo->idremesa = NULL;
            if( $recibo->save() )
            {
               $this->new_message('Recibo '.$recibo->codigo.' excluido.');
            }
            else
            {
               $this->new_error_msg('Error al excluir el recibo '.$recibo->codigo);
            }
         }
      }
   }
   
   private function download()
   {
      // ID único de la remesa: id de remesa + fecha de generación
      $paymentInfoId = $this->empresa->cifnif.'-'.date('dmy-H:i', strtotime($this->remesa->fecha.' '.$this->hour()));
      
      // Formato de documento a utilizar
      $sepaPAIN = SephpaDirectDebit::SEPA_PAIN_008_001_02;
      
      // Comprobar y sanear valores, permite evitar validación de IBAN, útil para pruebas con IBANs falsos
      $checkAndSanitize = FALSE;
      
      /**
       * normal direct debit : LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT = 'CORE';
       * urgent direct debit : LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT_D_1 = 'COR1';
       * business direct debit : LOCAL_INSTRUMENT_BUSINESS_2_BUSINESS = 'B2B';
       */
      $localInstrument = SepaUtilities::LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT;
      
      /**
      * first direct debit : SEQUENCE_TYPE_FIRST = 'FRST';
      * recurring direct debit : SEQUENCE_TYPE_RECURRING = 'RCUR';
      * one time direct debit : SEQUENCE_TYPE_ONCE = 'OOFF';
      * final direct debit : SEQUENCE_TYPE_FINAL = 'FNAL';
      */
      $sequenceType = SepaUtilities::SEQUENCE_TYPE_RECURRING;

      $directDebitFile = new SephpaDirectDebit($this->empresa->nombre, $paymentInfoId, $sepaPAIN, $checkAndSanitize);

      $creationDateTime = date('Y-m-d\TH:i:s', strtotime($this->remesa->fecha.' '.$this->hour()));

      // at least one in every SEPA file. No limit.
      $directDebitCollection = $directDebitFile->addCollection(
              array(
                  'pmtInfId' => $paymentInfoId,
                  'lclInstrm' => $localInstrument,
                  'seqTp' => $sequenceType,
                  'cdtr' => substr($this->empresa->nombre, 0, 70),
                  'iban' => $this->remesa->iban(),
                  'bic' => $this->remesa->swift,
                  'ci' => $this->remesa->creditorid,
                  'ccy' => $this->remesa->coddivisa,
                  'reqdColltnDt' => date('Y-m-d', strtotime($this->remesa->fechacargo)),
      ));
      
      // at least one in every DirectDebitCollection. No limit.
      foreach($this->resultados as $recibo)
      {
         $directDebitCollection->addPayment(
                 array(
                     'pmtId' => $recibo->codigo,
                     'instdAmt' => $recibo->importe,
                     'mndtId' => $recibo->codigo,
                     'dtOfSgntr' => date('Y-m-d', strtotime($recibo->fmandato)),
                     'dbtr' => $this->sanitize_name($recibo->nombrecliente),
                     'bic' => $recibo->swift,
                     'iban' => $recibo->iban(),
                     'rmtInf' => $recibo->codigo,
                 )
         );
      }
      
      $this->remesa->estado = "En trámite";
      if( $this->remesa->save() )
      {
         $this->template = FALSE;
         $directDebitFile->downloadSepaFile('Remesa_'.$this->remesa->idremesa.'_'.$this->remesa->fecha
                 .'_SEPA_'.$localInstrument.''.'.xml', $creationDateTime, $this->remesa->creditorid);
      }
      else
      {
         $this->new_error_msg("¡Imposible modificar la remesa!");
      }
   }
   
   private function sanitize_name($name)
   {
      return substr( str_replace(' & ', ' &amp; ', $name), 0, 70);
   }
   
   private function liberar_recibos()
   {
      $sql = "UPDATE reciboscli SET idremesa = NULL WHERE idremesa NOT IN (SELECT idremesa FROM remesas_sepa);";
      $this->db->exec($sql);
   }
   
   private function pagar_recibos()
   {
      $pagados = 0;
      
      $cuentab = $this->cuentab->get($this->remesa->codcuenta);
      if($cuentab)
      {
         $ref0 = new recibo_factura();
         $recli = new recibo_cliente();
         foreach($recli->all_from_remesa($this->remesa->idremesa) as $recibo)
         {
            if( $ref0->nuevo_pago_cli($recibo, $this->codsubcuenta_pago, 'Pago', $this->remesa->fechacargo) )
            {
               $pagados++;
            }
            else
            {
               $this->new_error_msg('Imposible guardar el pago del recibo '.$recibo->codigo);
            }
         }
         
         /// mostramos los errores al generar los asientos
         foreach($ref0->errors as $err)
         {
            $this->new_error_msg($err);
         }
         
         $this->new_message($pagados.' recibos marcados como pagados.');
      }
      else
      {
         $this->new_error_msg('Cuenta bancaria no encontrada.');
      }
   }
   
   private function share_extensions()
   {
      /// botón para generar remesas en el FSDK
      $fsext = new fs_extension();
      $fsext->name = 'gen_remesas';
      $fsext->from = __CLASS__;
      $fsext->to = 'fsdk_home';
      $fsext->type = 'generate';
      $fsext->text = '<i class="fa fa-bank" aria-hidden="true"></i> Remesas';
      $fsext->params = '&gdp=remesas';
      $fsext->save();
   }
   
   private function generar_datos_prueba()
   {
      require_once __DIR__.'/../lib/generar_datos_prueba.php';
      $gdp = new generar_datos_prueba_crm($this->db, $this->empresa);
      
      switch($_GET['gdp'])
      {
         case 'remesas':
            $num = $gdp->remesas();
            $this->new_message('Generadas '.$num.' remesas.');
            $this->new_message('Recargando... &nbsp; <i class="fa fa-refresh fa-spin"></i><br/>'
                    . '<a href="index.php?page=fsdk_home">Detener</a>');
            
            $this->url_recarga = $this->url().'&gdp=remesas';
            break;
      }
   }
   
   public function estados()
   {
      return array(
          'Preparada', 'En trámite', 'Revisar', 'Realizada'
      );
   }
}
