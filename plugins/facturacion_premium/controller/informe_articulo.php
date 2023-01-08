<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016, Carlos García Gómez. All Rights Reserved.
 */

require_model('articulo.php');

/**
 * Description of informe_articulo
 *
 * @author Carlos García Gómez
 */
class informe_articulo extends fs_controller
{
   public $articulo;
   public $stats_precios;
   public $stats_unidades;
   public $total_compras;
   public $total_ventas;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Informe de artículo', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->share_extensions();
      
      $this->articulo = FALSE;
      if( isset($_REQUEST['ref']) )
      {
         $art0 = new articulo();
         $this->articulo = $art0->get($_REQUEST['ref']);
      }
      
      if($this->articulo)
      {
         $this->stats_precios();
         $this->stats_unidades();
      }
      else
      {
         $this->new_error_msg('Artículo no encontrado.', 'error', FALSE, FALSE);
      }
   }
   
   private function stats_precios()
   {
      $this->stats_precios = array();
      $years = array();
      for($i=4; $i>=0; $i--)
      {
         $years[] = intval(Date('Y')) - $i;
      }
      
      $meses = array('Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
      
      foreach($years as $year)
      {
         if( $year == intval(Date('Y')) )
         {
            /// año actual
            for($i = 1; $i <= intval(Date('m')); $i++)
            {
               $this->stats_precios[$year.'-'.$i]['mes'] = $meses[$i-1].' '.$year;
               $this->stats_precios[$year.'-'.$i]['compras'] = 0;
               $this->stats_precios[$year.'-'.$i]['ventas'] = 0;
            }
         }
         else
         {
            /// años anteriores
            for($i = 1; $i <= 12; $i++)
            {
               $this->stats_precios[$year.'-'.$i]['mes'] = $meses[$i-1].' '.$year;
               $this->stats_precios[$year.'-'.$i]['compras'] = 0;
               $this->stats_precios[$year.'-'.$i]['ventas'] = 0;
            }
         }
         
         if( strtolower(FS_DB_TYPE) == 'postgresql')
         {
            $sql_aux = "to_char(f.fecha,'FMMM')";
         }
         else
            $sql_aux = "DATE_FORMAT(f.fecha, '%m')";
         
         $sql = "SELECT ".$sql_aux." as mes, avg(l.pvptotal/l.cantidad) as total FROM lineasfacturasprov l, facturasprov f"
                 ." WHERE l.idfactura = f.idfactura"
                 ." AND f.fecha >= ".$this->empresa->var2str(Date('1-1-'.$year))
                 ." AND f.fecha <= ".$this->empresa->var2str(Date('31-12-'.$year))
                 ." AND l.referencia = ".$this->empresa->var2str($this->articulo->referencia)
                 ." GROUP BY mes ORDER BY mes ASC;";
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               if( isset($this->stats_precios[$year.'-'.intval($d['mes'])]['compras']) )
               {
                  $total = floatval($d['total']);
                  $this->stats_precios[$year.'-'.intval($d['mes'])]['compras'] = round($total, FS_NF0_ART);
               }
            }
         }
         
         $sql = "SELECT ".$sql_aux." as mes, avg(l.pvptotal/l.cantidad) as total FROM lineasfacturascli l, facturascli f"
                 ." WHERE l.idfactura = f.idfactura"
                 ." AND f.fecha >= ".$this->empresa->var2str(Date('1-1-'.$year))
                 ." AND f.fecha <= ".$this->empresa->var2str(Date('31-12-'.$year))
                 ." AND l.referencia = ".$this->empresa->var2str($this->articulo->referencia)
                 ." GROUP BY mes ORDER BY mes ASC;";
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               if( isset($this->stats_precios[$year.'-'.intval($d['mes'])]['ventas']) )
               {
                  $total = floatval($d['total']);
                  $this->stats_precios[$year.'-'.intval($d['mes'])]['ventas'] = round($total, FS_NF0_ART);
               }
            }
         }
      }
      
      /// ahora rellenamos los huecos
      $pcompra = $pventa = 0;
      foreach($this->stats_precios as $i => $value)
      {
         if($value['compras'] == 0)
         {
            $this->stats_precios[$i]['compras'] = $pcompra;
         }
         else
         {
            $pcompra = $value['compras'];
         }
         
         if($value['ventas'] == 0)
         {
            $this->stats_precios[$i]['ventas'] = $pventa;
         }
         else
         {
            $pventa = $value['ventas'];
         }
      }
   }
   
   private function stats_unidades()
   {
      $this->stats_unidades = array();
      $years = array();
      for($i=4; $i>=0; $i--)
      {
         $years[] = intval(Date('Y')) - $i;
      }
      
      $meses = array('Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
      
      foreach($years as $year)
      {
         if( $year == intval(Date('Y')) )
         {
            /// año actual
            for($i = 1; $i <= intval(Date('m')); $i++)
            {
               $this->stats_unidades[$year.'-'.$i]['mes'] = $meses[$i-1].' '.$year;
               $this->stats_unidades[$year.'-'.$i]['compras'] = 0;
               $this->stats_unidades[$year.'-'.$i]['ventas'] = 0;
            }
         }
         else
         {
            /// años anteriores
            for($i = 1; $i <= 12; $i++)
            {
               $this->stats_unidades[$year.'-'.$i]['mes'] = $meses[$i-1].' '.$year;
               $this->stats_unidades[$year.'-'.$i]['compras'] = 0;
               $this->stats_unidades[$year.'-'.$i]['ventas'] = 0;
            }
         }
         
         if( strtolower(FS_DB_TYPE) == 'postgresql')
         {
            $sql_aux = "to_char(f.fecha,'FMMM')";
         }
         else
            $sql_aux = "DATE_FORMAT(f.fecha, '%m')";
         
         $sql = "SELECT ".$sql_aux." as mes, sum(l.cantidad) as total FROM lineasfacturasprov l, facturasprov f"
                 ." WHERE l.idfactura = f.idfactura"
                 ." AND f.fecha >= ".$this->empresa->var2str(Date('1-1-'.$year))
                 ." AND f.fecha <= ".$this->empresa->var2str(Date('31-12-'.$year))
                 ." AND l.referencia = ".$this->empresa->var2str($this->articulo->referencia)
                 ." GROUP BY mes ORDER BY mes ASC;";
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               if( isset($this->stats_unidades[$year.'-'.intval($d['mes'])]['compras']) )
               {
                  $total = floatval($d['total']);
                  $this->stats_unidades[$year.'-'.intval($d['mes'])]['compras'] = round($total, FS_NF0);
               }
            }
         }
         
         $sql = "SELECT ".$sql_aux." as mes, sum(l.cantidad) as total FROM lineasfacturascli l, facturascli f"
                 ." WHERE l.idfactura = f.idfactura"
                 ." AND f.fecha >= ".$this->empresa->var2str(Date('1-1-'.$year))
                 ." AND f.fecha <= ".$this->empresa->var2str(Date('31-12-'.$year))
                 ." AND l.referencia = ".$this->empresa->var2str($this->articulo->referencia)
                 ." GROUP BY mes ORDER BY mes ASC;";
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               if( isset($this->stats_unidades[$year.'-'.intval($d['mes'])]['ventas']) )
               {
                  $total = floatval($d['total']);
                  $this->stats_unidades[$year.'-'.intval($d['mes'])]['ventas'] = round($total, FS_NF0);
               }
            }
         }
      }
      
      /// ahora calculamos los totales
      $this->total_compras = $this->total_ventas = 0;
      foreach($this->stats_unidades as $value)
      {
         $this->total_compras += $value['compras'];
         $this->total_ventas += $value['ventas'];
      }
   }
   
   private function share_extensions()
   {
      $fsext = new fs_extension();
      $fsext->name = 'tab_articulo';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_articulo';
      $fsext->type = 'button';
      $fsext->text = '<i class="fa fa-line-chart" aria-hidden="true" title="Estadísticas"></i>';
      $fsext->save();
   }
}
