<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2017, Carlos García Gómez. All Rights Reserved. 
 */

require_model('cliente.php');
require_model('recibo_cliente.php');

/**
 * Description of informe_clientes
 *
 * @author Carlos García Gómez
 */
class informe_clientes extends fs_controller
{
   public $cliente;
   public $opcion;
   public $resultados;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Clientes', 'informes');
   }
   
   protected function private_core()
   {
      $this->cliente = new cliente();
      
      $this->opcion = 'distcli';
      if( isset($_REQUEST['opcion']) )
      {
         $this->opcion = $_REQUEST['opcion'];
      }
      
      if($this->opcion == 'nuevos')
      {
         $this->check_clientes();
         $this->resultados = $this->nuevos_clientes();
      }
      else if($this->opcion == 'top')
      {
         $this->resultados = $this->top_clientes();
      }
      else if($this->opcion == 'deudores')
      {
         $this->resultados = $this->clientes_deudores();
      }
   }
   
   private function nuevos_clientes()
   {
      $stats = array();
      $years = array();
      for($i=2; $i>=0; $i--)
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
               $stats[$year.'-'.$i]['mes'] = $meses[$i-1].' '.$year;
               $stats[$year.'-'.$i]['nuevos'] = 0;
               $stats[$year.'-'.$i]['total'] = 0;
            }
         }
         else
         {
            /// años anteriores
            for($i = 1; $i <= 12; $i++)
            {
               $stats[$year.'-'.$i]['mes'] = $meses[$i-1].' '.$year;
               $stats[$year.'-'.$i]['nuevos'] = 0;
               $stats[$year.'-'.$i]['total'] = 0;
            }
         }
         
         if( strtolower(FS_DB_TYPE) == 'postgresql')
         {
            $sql_aux = "to_char(fechaalta,'FMMM')";
         }
         else
            $sql_aux = "DATE_FORMAT(fechaalta, '%m')";
         
         $data = $this->db->select("SELECT ".$sql_aux." as mes, count(codcliente) as total"
                 ." FROM clientes WHERE fechaalta >= ".$this->empresa->var2str(Date('1-1-'.$year))
                 ." AND fechaalta <= ".$this->empresa->var2str(Date('31-12-'.$year))
                 ." GROUP BY ".$sql_aux." ORDER BY mes ASC;");
         if($data)
         {
            foreach($data as $d)
            {
               if( isset($stats[$year.'-'.intval($d['mes'])]['nuevos']) )
               {
                  $stats[$year.'-'.intval($d['mes'])]['nuevos'] = number_format($d['total'], FS_NF0, '.', '');
               }
            }
         }
         
         /// rellenamos los totales
         for($i = 1; $i <= 12; $i++)
         {
            $data = $this->db->select("SELECT count(codcliente) as total"." FROM clientes"
                    . " WHERE fechaalta <= ".$this->empresa->var2str(Date('31-'.$i.'-'.$year)));
            if($data)
            {
               foreach($data as $d)
               {
                  if( isset($stats[$year.'-'.$i]['total']) )
                  {
                     $stats[$year.'-'.$i]['total'] = number_format($d['total'], FS_NF0, '.', '');
                  }
               }
            }
         }
      }
      
      return $stats;
   }
   
   private function check_clientes()
   {
      $fechamin = '01-01-2000';
      
      $data = $this->db->select_limit("SELECT * FROM clientes WHERE fechaalta IS NULL", 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $nuevafecha = $fechamin;
            
            if( isset($d['fechaaltaweb']) )
            {
               $nuevafecha = date('d-m-Y', strtotime($d['fechaaltaweb']));
            }
            else
            {
               $sql = "SELECT fecha FROM facturascli WHERE codcliente = "
                       .$this->empresa->var2str($d['codcliente'])." ORDER BY fecha ASC";
               
               $data2 = $this->db->select_limit($sql, 1, 0);
               if($data2)
               {
                  $nuevafecha = date('d-m-Y', strtotime($data2[0]['fecha']));
                  
                  if( strtotime($fechamin) > strtotime($data2[0]['fecha']) )
                  {
                     $fechamin = date('d-m-Y', strtotime($data2[0]['fecha']));
                  }
               }
            }
            
            $sql = "UPDATE clientes SET fechaalta = ".$this->empresa->var2str($nuevafecha)
                    ." WHERE codcliente = ".$this->empresa->var2str($d['codcliente']).";";
            $this->db->exec($sql);
         }
      }
   }
   
   public function nuevos_clientes_dias()
   {
      $stats = array();
      
      $sql = "SELECT fechaalta, COUNT(codcliente) as total FROM clientes WHERE fechaalta > "
              .$this->empresa->var2str( date('d-m-Y', strtotime('-2years')) )
              ." GROUP BY fechaalta ORDER BY fechaalta ASC;";
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $stats[] = array(
                'dia' => date('d', strtotime($d['fechaalta'])),
                'mes' => date('m', strtotime($d['fechaalta'])),
                'anyo' => date('Y', strtotime($d['fechaalta'])),
                'total' => intval($d['total'])
            );
         }
      }
      
      return $stats;
   }
   
   private function top_clientes()
   {
      $tclist = array();
      
      /// consultamos para la divisa de la empresa
      $sql = "SELECT codcliente,SUM(total) as total, MAX(fecha) as fecha FROM facturascli"
              . " WHERE pagada = true AND coddivisa = ".$this->empresa->var2str($this->empresa->coddivisa)
              . " GROUP BY codcliente ORDER BY total DESC";
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $cliente = $this->cliente->get($d['codcliente']);
            if($cliente)
            {
               $pagos = floatval($d['total']);
               
               if( !$cliente->debaja AND $pagos > 0 )
               {
                  $meses = ( time() - strtotime($cliente->fechaalta) ) / 2592000;
                  $rendimiento = 0;
                  if($meses > 0)
                  {
                     $rendimiento = min( array($pagos, $pagos / $meses) );
                  }
                  
                  $ultima_venta = date('d-m-Y', strtotime($d['fecha']));
                  
                  $tclist[$d['codcliente']] = array($cliente, $pagos, $rendimiento, $ultima_venta);
               }
            }
         }
      }
      
      /// consultamos para el resto de divisas
      $sql = "SELECT codcliente,SUM(totaleuros) as total, MAX(fecha) as fecha FROM facturascli"
              . " WHERE pagada = true AND coddivisa != ".$this->empresa->var2str($this->empresa->coddivisa)
              . " GROUP BY codcliente ORDER BY total DESC";
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $cliente = $this->cliente->get($d['codcliente']);
            if($cliente)
            {
               $pagos = $this->euro_convert( floatval($d['total']) );
               if( isset($tclist[$d['codcliente']]) )
               {
                  /// recuperamos y sumamos los pagos en la divisa de la empresa
                  $pagos += $tclist[$d['codcliente']][1];
               }
               
               if( !$cliente->debaja AND $pagos > 0 )
               {
                  $meses = ( time() - strtotime($cliente->fechaalta) ) / 2592000;
                  $rendimiento = 0;
                  if($meses > 0)
                  {
                     $rendimiento = min( array($pagos, $pagos / $meses) );
                  }
                  
                  $ultima_venta = date('d-m-Y', strtotime($d['fecha']));
                  
                  $tclist[$d['codcliente']] = array($cliente, $pagos, $rendimiento, $ultima_venta);
               }
            }
         }
      }
      
      /// reordenamos
      usort($tclist, function($a,$b) {
         if($a[2] == $b[2])
         {
            return 0;
         }
         else if($a[2] > $b[2])
         {
            return -1;
         }
         else
            return 1;
      });
      
      return $tclist;
   }
   
   private function clientes_deudores()
   {
      $tclist = array();
      
      /// consultamos para la divisa de la empresa
      if( class_exists('recibo_cliente') )
      {
         $sql = "SELECT codcliente,SUM(importe) as total FROM reciboscli"
                 . " WHERE estado != 'Pagado' AND coddivisa = ".$this->empresa->var2str($this->empresa->coddivisa)
                 . " GROUP BY codcliente ORDER BY total DESC";
      }
      else
      {
         $sql = "SELECT codcliente,SUM(total) as total FROM facturascli"
                 . " WHERE pagada = false AND coddivisa = ".$this->empresa->var2str($this->empresa->coddivisa)
                 . " GROUP BY codcliente ORDER BY total DESC";
      }
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $cliente = $this->cliente->get($d['codcliente']);
            if($cliente)
            {
               if(!$cliente->debaja)
               {
                  $deudas = floatval($d['total']);
                  $tclist[$d['codcliente']] = array($cliente, $deudas);
               }
            }
         }
      }
      
      /// consultamos para el resto de divisas
      if( class_exists('recibo_cliente') )
      {
         $sql = "SELECT codcliente,sum(importeeuros) as total FROM reciboscli"
                 . " WHERE estado != 'Pagado' AND coddivisa != ".$this->empresa->var2str($this->empresa->coddivisa)
                 . " GROUP BY codcliente ORDER BY total DESC";
      }
      else
      {
         $sql = "SELECT codcliente,sum(totaleuros) as total FROM facturascli"
                 . " WHERE pagada = false AND coddivisa != ".$this->empresa->var2str($this->empresa->coddivisa)
                 . " GROUP BY codcliente ORDER BY total DESC";
      }
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            if( isset($tclist[$d['codcliente']]) )
            {
               $deudas = $this->euro_convert( floatval($d['total']) );
               $tclist[$d['codcliente']][1] += $deudas;
            }
            else
            {
               $cliente = $this->cliente->get($d['codcliente']);
               if($cliente)
               {
                  if(!$cliente->debaja)
                  {
                     $deudas = $this->euro_convert( floatval($d['total']) );
                     $tclist[$d['codcliente']] = array($cliente, $deudas);
                  }
               }
            }
         }
      }
      
      /// reordenamos
      usort($tclist, function($a,$b) {
         if($a[1] == $b[1])
         {
            return 0;
         }
         else if($a[1] > $b[1])
         {
            return -1;
         }
         else
            return 1;
      });
      
      return $tclist;
   }
   
   public function distribucion_clientes($tabla, $provincias = FALSE)
   {
      $lista = array();
      
      if($provincias)
      {
         if($tabla == 'facturascli')
         {
            $sql = "SELECT codpais,lower(provincia) as provincia,SUM(totaleuros) as total "
                    . "FROM ".$tabla." GROUP BY codpais,lower(provincia) "
                    . "ORDER BY total DESC;";
         }
         else
         {
            $sql = "SELECT codpais,lower(provincia) as provincia,COUNT(*) as total "
                    . "FROM ".$tabla." GROUP BY codpais,lower(provincia) "
                    . "ORDER BY total DESC;";
         }
         
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               if($d['codpais'] != '' AND $d['provincia'] != '')
               {
                  $lista[] = array(
                      'codpais' => $d['codpais'],
                      'provincia' => $d['codpais'].'/'.$d['provincia'],
                      'total' => floatval($d['total'])
                  );
               }
            }
         }
      }
      else
      {
         if($tabla == 'facturascli')
         {
            $sql = "SELECT codpais,SUM(totaleuros) as total FROM ".$tabla." GROUP BY codpais;";
         }
         else
         {
            $sql = "SELECT codpais,COUNT(*) as total FROM ".$tabla." GROUP BY codpais;";
         }
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               if($d['codpais'] != '')
               {
                  $lista[] = array(
                      'codpais' => $d['codpais'],
                      'total' => floatval($d['total'])
                  );
               }
            }
         }
      }
      
      if($tabla == 'facturascli')
      {
         /// convertimos los totales a la moneda local
         foreach($lista as $i => $value)
         {
            $lista[$i]['total'] = $this->euro_convert($value['total']);
         }
      }
      
      return $lista;
   }
   
   public function facturacion_media_cliente()
   {
      $media = 0;
      $sql = "select codcliente, sum(totaleuros) as total from facturascli group by codcliente";
      
      $offset = 0;
      $total = 0;
      $data = $this->db->select_limit($sql, 1000, $offset);
      while($data)
      {
         foreach($data as $d)
         {
            $media += $this->euro_convert( floatval($d['total']) );
            $total++;
            $offset++;
         }
         
         $data = $this->db->select_limit($sql, 1000, $offset);
      }
      
      if($media > 0 AND $total > 0)
      {
         $media = $media/$total;
      }
      
      return $media;
   }
}
