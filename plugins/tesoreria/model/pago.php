<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2014-2016  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of pago
 *
 * @author Carlos García Gómez
 */
class pago extends fs_model
{
   public $id;
   public $idfactura;
   public $idalbaran;
   public $idpedido;
   public $idrecibo;
   public $fase;
   public $fecha;
   public $importe;
   public $nota;
   
   public $pendiente;
   
   public function __construct($p = FALSE)
   {
      parent::__construct('pagos');
      if($p)
      {
         $this->id = $this->intval($p['id']);
         $this->idfactura = $this->intval($p['idfactura']);
         $this->idalbaran = $this->intval($p['idalbaran']);
         $this->idpedido = $this->intval($p['idpedido']);
         $this->idrecibo = $this->intval($p['idrecibo']);
         $this->fase = $p['fase'];
         $this->fecha = Date('d-m-Y', strtotime($p['fecha']));
         $this->importe = floatval($p['importe']);
         $this->nota = $p['nota'];
      }
      else
      {
         $this->id = NULL;
         $this->idfactura = NULL;
         $this->idalbaran = NULL;
         $this->idpedido = NULL;
         $this->idrecibo = NULL;
         $this->fase = '-';
         $this->fecha = Date('d-m-Y');
         $this->importe = 0;
         $this->nota = '';
      }
      
      $this->pendiente = 0;
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      if( !is_null($this->idrecibo) )
      {
         return "index.php?page=ventas_recibo&id=".$this->idrecibo;
      }
      else if( !is_null($this->idfactura) )
      {
         return "index.php?page=ventas_factura&id=".$this->idfactura;
      }
      else if( !is_null($this->idalbaran) )
      {
         return "index.php?page=ventas_albaran&id=".$this->idalbaran;
      }
      else if( !is_null($this->idpedido) )
      {
         return "index.php?page=ventas_pedido&id=".$this->idpedido;
      }
      else
         return '#';
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM pagos WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new pago($data[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM pagos WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function save()
   {
      $this->nota = $this->no_html($this->nota);
      
      if( $this->exists() )
      {
         $sql = "UPDATE pagos SET idfactura = ".$this->var2str($this->idfactura).
                 ", idalbaran = ".$this->var2str($this->idalbaran).
                 ", idpedido = ".$this->var2str($this->idpedido).
                 ", fase = ".$this->var2str($this->fase).
                 ", fecha = ".$this->var2str($this->fecha).
                 ", importe = ".$this->var2str($this->importe).
                 ", nota = ".$this->var2str($this->nota).
                 ", idrecibo = ".$this->var2str($this->idrecibo).
                 "  WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO pagos (idfactura,idalbaran,idpedido,fase,fecha,importe,nota,idrecibo) VALUES
                 (".$this->var2str($this->idfactura).
                 ",".$this->var2str($this->idalbaran).
                 ",".$this->var2str($this->idpedido).
                 ",".$this->var2str($this->fase).
                 ",".$this->var2str($this->fecha).
                 ",".$this->var2str($this->importe).
                 ",".$this->var2str($this->nota).
                 ",".$this->var2str($this->idrecibo).");";
         
         if( $this->db->exec($sql) )
         {
            $this->id = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM pagos WHERE id = ".$this->var2str($this->id).";");
   }
   
   /**
    * Devuelve todos los pagos previos de la factura.
    * @param type $id
    * @return \pago
    */
   public function all_from_factura($id)
   {
      $plist = array();
      
      $data = $this->db->select("SELECT * FROM pagos WHERE idfactura = ".$this->var2str($id)." ORDER BY fecha ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            $plist[] = new pago($d);
         }
      }
      
      return $plist;
   }
   
   public function all_from_albaran($id)
   {
      $plist = array();
      
      $data = $this->db->select("SELECT * FROM pagos WHERE idalbaran = ".$this->var2str($id)." ORDER BY fecha ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            $plist[] = new pago($d);
         }
      }
      
      return $plist;
   }
   
   public function all_from_pedido($id)
   {
      $plist = array();
      
      $data = $this->db->select("SELECT * FROM pagos WHERE idpedido = ".$this->var2str($id)." ORDER BY fecha ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            $plist[] = new pago($d);
         }
      }
      
      return $plist;
   }
   
   public function all_from_recibo($id)
   {
      $plist = array();
      
      $data = $this->db->select("SELECT * FROM pagos WHERE idrecibo = ".$this->var2str($id)." ORDER BY fecha ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            $plist[] = new pago($d);
         }
      }
      
      return $plist;
   }
   
   public function all()
   {
      $plist = array();
      
      $data = $this->db->select("SELECT * FROM pagos ORDER BY fecha ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            $plist[] = new pago($d);
         }
      }
      
      return $plist;
   }
   
   public function cron_job()
   {
      /**
       * eliminamos relaciones con elementos eliminados.
       */
      $sql = "UPDATE pagos SET idrecibo = NULL WHERE idrecibo NOT IN (SELECT idrecibo FROM reciboscli);";
      $this->db->exec($sql);
      
      $sql = "UPDATE pagos SET idfactura = NULL WHERE idfactura NOT IN (SELECT idfactura FROM facturascli);";
      $this->db->exec($sql);
      
      $sql = "UPDATE pagos SET idalbaran = NULL WHERE idalbaran NOT IN (SELECT idalbaran FROM albaranescli);";
      $this->db->exec($sql);
      
      if( $this->db->table_exists('pedidoscli') )
      {
         $sql = "UPDATE pagos SET idpedido = NULL WHERE idpedido NOT IN (SELECT idpedido FROM pedidoscli);";
         $this->db->exec($sql);
      }
      
      /// eliminamos pagos sin relaciones.
      $sql = "DELETE FROM pagos WHERE idpedido IS NULL AND idalbaran IS NULL AND idfactura IS NULL;";
      $this->db->exec($sql);
      
      if( $this->db->table_exists('pedidoscli') )
      {
         /**
          * Asignamos las nuevas relaciones con albaranes.
          */
         $sql = "SELECT * FROM pagos WHERE idalbaran IS NULL;";
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               $pago = new pago($d);
               
               /// buscamos el idpedido en las líneas de albaranes
               $sql2 = "select * from lineasalbaranescli where idpedido = ".$this->var2str($pago->idpedido).";";
               $data2 = $this->db->select($sql2);
               if($data2)
               {
                  $pago->idalbaran = $this->intval($data2[0]['idalbaran']);
                  $pago->save();
               }
            }
         }
      }
      
      /**
       * Asignamos las nuevas relaciones con facturas.
       */
      $sql = "SELECT * FROM pagos WHERE idfactura IS NULL AND idalbaran IS NOT NULL;";
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $pago = new pago($d);
            
            /// buscamos el idalbaran en las líneas de facturas
            $sql2 = "select * from lineasfacturascli where idalbaran = ".$this->var2str($pago->idalbaran).";";
            $data2 = $this->db->select($sql2);
            if($data2)
            {
               $pago->idfactura = $this->intval($data2[0]['idfactura']);
               $pago->save();
            }
         }
      }
   }
}
