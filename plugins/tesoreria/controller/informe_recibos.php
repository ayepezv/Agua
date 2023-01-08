<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('recibo_cliente.php');
require_model('recibo_proveedor.php');

/**
 * Description of informe_recibos
 *
 * @author Carlos García Gómez
 */
class informe_recibos extends fs_controller
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Recibos', 'informes');
   }
   
   protected function private_core()
   {
      /// forzamos la comprobación de las tablas
      $recibo_cli = new recibo_cliente();
      $recibo_pro = new recibo_proveedor();
   }
   
   /*
    * Devuelve un array con los datos estadísticos de los recibos de cliente
    * en los dos últimos años.
    */
   public function stats_from_recibos($table = 'reciboscli')
   {
      $stats = array();
      $years = array();
      for($i=1; $i>=0; $i--)
      {
         $years[] = intval(Date('Y')) - $i;
      }
      
      $meses = array('Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
      
      foreach($years as $year)
      {
         /// inicializamos los resultados
         for($i = 1; $i <= 12; $i++)
         {
            $stats[$year.'-'.$i]['mes'] = $meses[$i-1].' '.$year;
            $stats[$year.'-'.$i]['total'] = 0;
            $stats[$year.'-'.$i]['pagados'] = 0;
         }
         
         if( strtolower(FS_DB_TYPE) == 'postgresql')
         {
            $sql_aux = "to_char(fecha,'FMMM')";
         }
         else
            $sql_aux = "DATE_FORMAT(fecha, '%m')";
         
         /// totales en la divisa de la empresa
         $data = $this->db->select("SELECT ".$sql_aux." as mes, sum(importe) as total FROM ".$table
                 ." WHERE coddivisa = ".$this->empresa->var2str($this->empresa->coddivisa)
                 ." AND fecha >= ".$this->empresa->var2str(Date('1-1-'.$year))
                 ." AND fecha <= ".$this->empresa->var2str(Date('31-12-'.$year))
                 ." GROUP BY ".$sql_aux." ORDER BY mes ASC;");
         if($data)
         {
            foreach($data as $d)
            {
               $stats[$year.'-'.intval($d['mes'])]['total'] = floatval($d['total']);
            }
         }
         
         /// totales en el resto de divisas
         $data = $this->db->select("SELECT ".$sql_aux." as mes, sum(importeeuros) as total FROM ".$table
                 ." WHERE coddivisa != ".$this->empresa->var2str($this->empresa->coddivisa)
                 ." AND fecha >= ".$this->empresa->var2str(Date('1-1-'.$year))
                 ." AND fecha <= ".$this->empresa->var2str(Date('31-12-'.$year))
                 ." GROUP BY ".$sql_aux." ORDER BY mes ASC;");
         if($data)
         {
            foreach($data as $d)
            {
               $stats[$year.'-'.intval($d['mes'])]['total'] += $this->euro_convert( floatval($d['total']) );
            }
         }
         
         /// pagos en la divisa de la empresa
         $data = $this->db->select("SELECT ".$sql_aux." as mes, sum(importe) as total FROM ".$table
                 ." WHERE coddivisa = ".$this->empresa->var2str($this->empresa->coddivisa)
                 ." AND fecha >= ".$this->empresa->var2str(Date('1-1-'.$year))
                 ." AND fecha <= ".$this->empresa->var2str(Date('31-12-'.$year))
                 ." AND estado = 'Pagado' GROUP BY ".$sql_aux." ORDER BY mes ASC;");
         if($data)
         {
            foreach($data as $d)
            {
               $stats[$year.'-'.intval($d['mes'])]['pagados'] = floatval($d['total']);
            }
         }
         
         /// pagos el resto de divisas
         $data = $this->db->select("SELECT ".$sql_aux." as mes, sum(importe) as total FROM ".$table
                 ." WHERE coddivisa != ".$this->empresa->var2str($this->empresa->coddivisa)
                 ." AND fecha >= ".$this->empresa->var2str(Date('1-1-'.$year))
                 ." AND fecha <= ".$this->empresa->var2str(Date('31-12-'.$year))
                 ." AND estado = 'Pagado' GROUP BY ".$sql_aux." ORDER BY mes ASC;");
         if($data)
         {
            foreach($data as $d)
            {
               $stats[$year.'-'.intval($d['mes'])]['pagados'] += $this->euro_convert( floatval($d['total']) );
            }
         }
      }
      
      return $stats;
   }
}
