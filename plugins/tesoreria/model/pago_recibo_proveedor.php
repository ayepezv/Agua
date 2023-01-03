<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('asiento.php');

/**
 * Description of pago_recibo_proveedor
 *
 * @author Carlos García Gómez
 */
class pago_recibo_proveedor extends fs_model
{
   /**
    * Clave primaria.
    * @var type 
    */
   public $idpagodevol;
   
   public $idrecibo;
   public $idasiento;
   
   /// subcuenta para el pago
   public $idsubcuenta;
   public $codsubcuenta;
   
   public $tipo;
   public $fecha;
   
   public function __construct($p = FALSE)
   {
      parent::__construct('pagosdevolprov');
      if($p)
      {
         $this->idpagodevol = $this->intval($p['idpagodevol']);
         $this->idasiento = $this->intval($p['idasiento']);
         $this->idrecibo = $this->intval($p['idrecibo']);
         $this->idsubcuenta = $this->intval($p['idsubcuenta']);
         $this->codsubcuenta = $p['codsubcuenta'];
         $this->fecha = date('d-m-Y', strtotime($p['fecha']));
         $this->tipo = $p['tipo'];
      }
      else
      {
         $this->idpagodevol = NULL;
         $this->idasiento = NULL;
         $this->idrecibo = NULL;
         $this->idsubcuenta = NULL;
         $this->codsubcuenta = NULL;
         $this->fecha = date('d-m-Y');
         $this->tipo = 'Pago';
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function asiento_url()
   {
      return 'index.php?page=contabilidad_asiento&id='.$this->idasiento;
   }
   
   public function subcuenta_url()
   {
      return 'index.php?page=contabilidad_subcuenta&id='.$this->idsubcuenta;
   }
   
   public function exists()
   {
      if( is_null($this->idpagodevol) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE idpagodevol = ".$this->var2str($this->idpagodevol).";");
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET idasiento = ".$this->var2str($this->idasiento)
                 .", idrecibo = ".$this->var2str($this->idrecibo)
                 .", idsubcuenta = ".$this->var2str($this->idsubcuenta)
                 .", codsubcuenta = ".$this->var2str($this->codsubcuenta)
                 .", fecha = ".$this->var2str($this->fecha)
                 .", tipo = ".$this->var2str($this->tipo)
                 ."  WHERE idpagodevol = ".$this->var2str($this->idpagodevol).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (codsubcuenta,idasiento,idrecibo,tipo,fecha,idsubcuenta)
            VALUES (".$this->var2str($this->codsubcuenta)
                 .",".$this->var2str($this->idasiento)
                 .",".$this->var2str($this->idrecibo)
                 .",".$this->var2str($this->tipo)
                 .",".$this->var2str($this->fecha)
                 .",".$this->var2str($this->idsubcuenta).");";
         
         if( $this->db->exec($sql) )
         {
            $this->idpagodevol = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      /// eliminamos los asientos correspondientes
      $asi0 = new asiento();
      $asiento = $asi0->get($this->idasiento);
      if($asiento)
      {
         $asiento->delete();
      }
      
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE idpagodevol = ".$this->var2str($this->idpagodevol).";");
   }
   
   public function all_from_recibo($id)
   {
      $plist = array();
      $sql = "SELECT * FROM ".$this->table_name." WHERE idrecibo = ".$this->var2str($id)
              ." ORDER BY fecha ASC, idpagodevol ASC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $plist[] = new pago_recibo_proveedor($d);
         }
      }
      
      return $plist;
   }
   
   public function cron_job()
   {
      /// eliminamos los pagos de recibos eliminados uno a uno
      $sql = "SELECT * FROM ".$this->table_name." WHERE idrecibo NOT IN (SELECT idrecibo FROM recibosprov);";
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            /// eliminamos uno a uno los pagos y los asientos asociados
            $pago = new pago_recibo_proveedor($d);
            $pago->delete();
         }
      }
   }
}
