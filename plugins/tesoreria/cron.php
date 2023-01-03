<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('pago.php');
require_model('pago_recibo_cliente.php');
require_model('pago_recibo_proveedor.php');
require_model('recibo_factura.php');

class tesoreria_cron
{
   private $db;
   
   public function __construct(&$db)
   {
      $this->db = $db;
      ///$this->fix_db();
      $recibo_factura = new recibo_factura();
      
      /// comprobamos los recibos de proveedores
      $recibo_prov = new recibo_proveedor();
      $recibo_prov->cron_job(TRUE);
      
      /// comprobamos los pagos
      $pago_prov = new pago_recibo_proveedor();
      $pago_prov->cron_job();
      
      /// alternamos distintas consultas
      $opcion = mt_rand(0, 3);
      switch ($opcion)
      {
         case 0:
            $sql = "SELECT * FROM facturasprov WHERE NOT pagada AND idfactura NOT IN (SELECT idfactura FROM recibosprov)";
            break;
         
         case 1:
            $sql = "SELECT * FROM facturasprov WHERE idfactura IN (SELECT idfactura FROM recibosprov WHERE estado != 'Pagado')";
            break;
         
         default:
            if( strtolower(FS_DB_TYPE) == 'mysql' )
            {
               $sql = "SELECT * FROM facturasprov WHERE NOT pagada ORDER BY rand()";
            }
            else
            {
               $sql = "SELECT * FROM facturasprov WHERE NOT pagada ORDER BY random()";
            }
            break;
      }
      
      $data = $this->db->select_limit($sql, 500, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $factura = new factura_proveedor($d);
            $recibo_factura->sync_factura_prov($factura);
            echo '.';
         }
      }
      
      /// comprobamos los anticipos
      $pago = new pago();
      $pago->cron_job();
      
      /// comprobamos los recibos de clientes
      $recibo_cli = new recibo_cliente();
      $recibo_cli->cron_job(TRUE);
      
      /// comprobamos los pagos
      $pago_cli = new pago_recibo_cliente();
      $pago_cli->cron_job();
      
      /// alternamos distintas consultas
      $opcion = mt_rand(0, 3);
      switch ($opcion)
      {
         case 0:
            $sql = "SELECT * FROM facturascli WHERE NOT pagada AND idfactura NOT IN (SELECT idfactura FROM reciboscli)";
            break;
         
         case 1:
            $sql = "SELECT * FROM facturascli WHERE idfactura IN (SELECT idfactura FROM reciboscli WHERE estado != 'Pagado')";
            break;
         
         default:
            if( strtolower(FS_DB_TYPE) == 'mysql' )
            {
               $sql = "SELECT * FROM facturascli WHERE NOT pagada ORDER BY rand()";
            }
            else
            {
               $sql = "SELECT * FROM facturascli WHERE NOT pagada ORDER BY random()";
            }
            break;
      }
      
      $data = $this->db->select_limit($sql, 500, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $factura = new factura_cliente($d);
            $recibo_factura->sync_factura_cli($factura);
            echo '.';
         }
      }
      
      /// ¿Errores?
      foreach($recibo_factura->errors as $err)
      {
         echo $err."\n";
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
}

new tesoreria_cron($db);