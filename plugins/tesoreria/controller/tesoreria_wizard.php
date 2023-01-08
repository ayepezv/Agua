<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2017, Carlos García Gómez. All Rights Reserved. 
 */

require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('recibo_factura.php');
require_model('remesa.php');

/**
 * Description of tesoreria_wizard
 *
 * @author Carlos García Gómez
 */
class tesoreria_wizard extends fs_controller
{
   public $url_recarga;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Asistente', 'admin', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->url_recarga = FALSE;
      $offset = 0;
      
      if( !$this->cluf_ok() )
      {
         /// todavía no se ha aceptado el cluf
      }
      else if( isset($_GET['offset']) )
      {
         /**
          * Generamos recibos para todas las facturas sin pagar.
          * Si ya hay recibos asociados se comprueban.
          */
         $offset = intval($_GET['offset']);
         
         $offset1 = $offset2 = $offset;
         $rf = new recibo_factura();
         
         $fp0 = new factura_proveedor();
         foreach($fp0->all_sin_pagar($offset1, 20) as $fp)
         {
            $rf->sync_factura_prov($fp);
            $offset1++;
         }
         
         $fc0 = new factura_cliente();
         foreach($fc0->all_sin_pagar($offset2, 20) as $fp)
         {
            $rf->sync_factura_cli($fp);
            $offset2++;
         }
         
         if( max( array($offset1, $offset2) ) > $offset )
         {
            $offset = max( array($offset1, $offset2) );
            $this->new_message('Comprobando facturas... ('.$offset.') &nbsp; <i class="fa fa-refresh fa-spin"></i>');
            $this->url_recarga = $this->url().'&offset='.$offset;
         }
         else
         {
            $this->new_advice('Finalizado.');
            $this->check_menu();
         }
      }
      else
      {
         $this->fix_db();
         
         /// forzamos la comprobación de remesas
         $rem = new remesa();
         
         $this->new_message('Comprobando... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
         $this->url_recarga = $this->url().'&offset='.$offset;
      }
   }
   
   public function cluf_ok()
   {
      $fsvar = new fs_var();
      
      if( isset($_GET['cluf_ok']) )
      {
         $fsvar->simple_save('tesoreria_cluf', '1');
         return TRUE;
      }
      else if( $fsvar->simple_get('tesoreria_cluf') )
      {
         return TRUE;
      }
      else
      {
         return FALSE;
      }
   }
   
   private function fix_db()
   {
      /// eliminamos mierda
      if( $this->db->table_exists('reciboscli') )
      {
         $this->db->exec("DELETE FROM reciboscli WHERE idfactura NOT IN (SELECT idfactura FROM facturascli);");
         
         if( $this->db->table_exists('remesas_sepa') )
         {
            $this->db->exec("UPDATE reciboscli SET idremesa = NULL WHERE idremesa NOT IN (SELECT idremesa FROM remesas_sepa);");
         }
      }
      
      if( $this->db->table_exists('recibosprov') )
      {
         $this->db->exec("DELETE FROM recibosprov WHERE idfactura NOT IN (SELECT idfactura FROM facturasprov);");
      }
      
      if( $this->db->table_exists('plazos') )
      {
         $this->db->exec("delete from plazos where codpago not in (select codpago from formaspago);");
      }
      
      if( $this->db->table_exists('pagosdevolcli') )
      {
         $this->db->exec("update pagosdevolcli set idasiento = null where idasiento not in (select idasiento from co_asientos);");
      }
      
      if( $this->db->table_exists('pagosdevolprov') )
      {
         $this->db->exec("update pagosdevolprov set idasiento = null where idasiento not in (select idasiento from co_asientos);");
      }
   }
   
   private function check_menu()
   {
      if( !$this->page->get('ventas_recibos') )
      {
         if( file_exists(__DIR__) )
         {
            /// activamos las páginas del plugin
            foreach( scandir(__DIR__) as $f)
            {
               if( $f != '.' AND $f != '..' AND is_string($f) AND strlen($f) > 4 AND !is_dir($f) AND $f != __CLASS__.'.php' )
               {
                  $page_name = substr($f, 0, -4);
                  
                  require_once __DIR__.'/'.$f;
                  $new_fsc = new $page_name();
                  
                  if( !$new_fsc->page->save() )
                  {
                     $this->new_error_msg("Imposible guardar la página ".$page_name);
                  }
                  
                  unset($new_fsc);
               }
            }
         }
         else
         {
            $this->new_error_msg('No se encuentra el directorio '.__DIR__);
         }
         
         $this->load_menu(TRUE);
      }
   }
}
