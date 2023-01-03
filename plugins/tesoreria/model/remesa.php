<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('empresa.php');
require_model('pais.php');

/**
 * Description of remesa
 *
 * @author Carlos García Gómez
 */
class remesa extends fs_model
{
   /**
    * Clave primaria.
    * @var type 
    */
   public $idremesa;
   public $descripcion;
   public $codpago;
   
   /**
    * Creditor identifier de SEPA.
    * @var type 
    */
   public $creditorid;
   
   /**
    * Código de la cuenta bancaria de la empresa.
    * @var type 
    */
   public $codcuenta;
   public $iban;
   public $swift;
   
   public $total;
   public $coddivisa;
   public $tasaconv;
   
   public $estado;
   public $fechacargo;
   public $fecha;
   
   public function __construct($r = FALSE)
   {
      parent::__construct('remesas_sepa');
      if($r)
      {
         $this->idremesa = $this->intval($r['idremesa']);
         $this->descripcion = $r['descripcion'];
         $this->codpago = $r['codpago'];
         $this->creditorid = $r['creditorid'];
         $this->codcuenta = $r['codcuenta'];
         $this->iban = $r['iban'];
         $this->swift = $r['swift'];
         $this->total = floatval($r['total']);
         $this->coddivisa = $r['coddivisa'];
         $this->tasaconv = floatval($r['tasaconv']);
         $this->estado = $r['estado'];
         $this->fechacargo = date('d-m-Y', strtotime($r['fechacargo']));
         $this->fecha = date('d-m-Y', strtotime($r['fecha']));
      }
      else
      {
         $this->idremesa = NULL;
         $this->descripcion = NULL;
         $this->codpago = NULL;
         $this->creditorid = NULL;
         $this->codcuenta = NULL;
         $this->iban = NULL;
         $this->swift = NULL;
         $this->total = 0;
         $this->coddivisa = NULL;
         $this->tasaconv = 1;
         $this->estado = 'Preparada';
         $this->fechacargo = date('d-m-Y');
         $this->fecha = date('d-m-Y');
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      return 'index.php?page=remesas&id='.$this->idremesa;
   }
   
   public function editable()
   {
      return ($this->estado == 'Preparada');
   }
   
   /**
    * Devuelve el IBAN con o sin espacios.
    * @param type $espacios
    * @return type
    */
   public function iban($espacios = FALSE)
   {
      if($espacios)
      {
         $txt = '';
         $iban = str_replace(' ', '', $this->iban);
         for($i = 0; $i < strlen($iban); $i += 4)
         {
            $txt .= substr($iban, $i, 4).' ';
         }
         return $txt;
      }
      else
      {
         return str_replace(' ', '', $this->iban);
      }
   }
   
   public function new_creditorid()
   {
      $this->creditorid = NULL;
      
      /// intentamos obtener el último utilizado
      foreach($this->all() as $reme)
      {
         $this->creditorid = $reme->creditorid;
         break;
      }
      
      if( is_null($this->creditorid) )
      {
         $empresa = new empresa();
         $pais0 = new pais();
         
         /// necesitamos el iso del país
         $pais = $pais0->get($empresa->codpais);
         if($pais)
         {
            $codiso = $pais->codiso;
         }
         else
         {
            $codiso = substr($empresa->codpais, 0, 2);
         }
         
         /// necesitamos el cif de la empresa sin espacios ni guiones
         $cif = str_replace( array(' ','-'), array('',''), strtoupper($empresa->cifnif) );
         
         /// ahora hay que calcular los dígitos de control
         $cif_aux = $this->letras_a_numeros($cif.$codiso.'00');
         $total = 98 - ($cif_aux % 97);
         
         $this->creditorid = $codiso.sprintf('%02s', $total).'000'.$cif;
      }
   }
   
   private function letras_a_numeros($txt)
   {
      $data = array(
          'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17,
          'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 'M' => 22, 'N' => 23, 'O' => 24, 'P' => 25,
          'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29, 'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33,
          'Y' => 34, 'Z' => 35
      );
      
      $nuevo = '';
      $i = 0;
      while($i < strlen($txt))
      {
         $t = substr($txt, $i, 1);
         
         if( isset($data[$t]) )
         {
            $nuevo .= $data[$t];
         }
         else
         {
            $nuevo .= $t;
         }
         
         $i++;
      }
      
      return $nuevo;
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM remesas_sepa WHERE idremesa = ".$this->var2str($id));
      if($data)
      {
         return new remesa($data[0]);
      }
      else
      {
         return FALSE;
      }
   }
   
   public function exists()
   {
      if( is_null($this->idremesa) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM remesas_sepa WHERE idremesa = ".$this->var2str($this->idremesa));
      }
   }
   
   public function save()
   {
      $this->descripcion = $this->no_html($this->descripcion);
      
      if( $this->exists() )
      {
         $sql = "UPDATE remesas_sepa SET codcuenta = ".$this->var2str($this->codcuenta)
                 .", creditorid = ".$this->var2str($this->creditorid)
                 .", codpago = ".$this->var2str($this->codpago)
                 .", iban = ".$this->var2str($this->iban)
                 .", swift = ".$this->var2str($this->swift)
                 .", descripcion = ".$this->var2str($this->descripcion)
                 .", total = ".$this->var2str($this->total)
                 .", coddivisa = ".$this->var2str($this->coddivisa)
                 .", tasaconv = ".$this->var2str($this->tasaconv)
                 .", estado = ".$this->var2str($this->estado)
                 .", fechacargo = ".$this->var2str($this->fechacargo)
                 .", fecha = ".$this->var2str($this->fecha)
                 ."  WHERE idremesa = ".$this->var2str($this->idremesa).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO remesas_sepa (codpago,creditorid,codcuenta,iban,swift,total,"
                 . "coddivisa,tasaconv,estado,fechacargo,fecha,descripcion) VALUES "
                 . "(".$this->var2str($this->codpago)
                 . ",".$this->var2str($this->creditorid)
                 . ",".$this->var2str($this->codcuenta)
                 . ",".$this->var2str($this->iban)
                 . ",".$this->var2str($this->swift)
                 . ",".$this->var2str($this->total)
                 . ",".$this->var2str($this->coddivisa)
                 . ",".$this->var2str($this->tasaconv)
                 . ",".$this->var2str($this->estado)
                 . ",".$this->var2str($this->fechacargo)
                 . ",".$this->var2str($this->fecha)
                 . ",".$this->var2str($this->descripcion).");";
         
         if( $this->db->exec($sql) )
         {
            $this->idremesa = $this->db->lastval();
            return TRUE;
         }
         else
         {
            return FALSE;
         }
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM remesas_sepa WHERE idremesa = ".$this->var2str($this->idremesa));
   }
   
   /**
    * Devuelve un array con todos los recibos de esta remesa.
    * @param type $offset
    * @return \remesa
    */
   public function all($offset = 0)
   {
      $lista = array();
      
      $data = $this->db->select_limit("SELECT * FROM remesas_sepa ORDER BY fecha DESC", FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new remesa($d);
         }
      }
      
      return $lista;
   }
}
