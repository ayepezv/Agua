<?php

class impresion_planilla extends fs_controller {

    public $cobros;
    public $desde;
    public $hasta;
    public $sql;
    public $offset;
    public $suma;
    public function __construct() {
        parent::__construct(__CLASS__, 'Impresion de Facturas', 'informes');
    }
    
    protected function private_core() {
        
        $this->desde = '';
         if( isset($_REQUEST['desde']) )
         {
            $this->desde = $_REQUEST['desde'];
         } else {
            $this->desde = date("d-m-Y");   
         }
         
         $this->hasta = '';
         if( isset($_REQUEST['hasta']) )
         {
            $this->hasta = $_REQUEST['hasta'];
         } else {
            $this->hasta = date("d-m-Y"); 
         }
         $this->offset =0;
        $this->sql = "SELECT 'factura_batan.php?cliente='||COALESCE(nombre,'-')||'&cedula='||COALESCE(cifnif,'-')||'&valor='||importe||'&fecha1='||fecha||'&Tex='||COALESCE(meses,'-')||'&Tex1='||observacion||'&Me='||COALESCE(num_meses,0)||'&Det='||COALESCE(rubro,'Otros') AS url, co_asientos.codejercicio, co_asientos.numero, co_asientos.concepto, co_asientos.fecha,co_asientos.idasiento, co_asientos.importe, Sum(co_asientos.importe) AS total FROM co_asientos WHERE concepto LIKE 'Cobro%' and fecha >= '".$this->desde ."' and fecha <= '".$this->hasta ."' GROUP BY concepto,fecha,idasiento, importe ORDER BY idasiento ASC";
        $this->cobros = $this->db->select_limit($this->sql, 200, $this->offset);
        
    }
    
    public function sumar() {
        $this->sql2 = "SELECT SUM(importe) AS total FROM co_asientos WHERE concepto LIKE 'Cobro%' and fecha >= '".$this->desde ."' and fecha <= '".$this->hasta ."'";
        $this->suma = $this->db->select_limit($this->sql2, 100, $this->offset);
        return print_r("$ ".current($this->suma[0]));
    }
    
}
