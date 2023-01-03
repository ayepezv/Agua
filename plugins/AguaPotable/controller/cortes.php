<?php
class cortes extends fs_controller {

    public $cobros;
    public $desde;
    public $hasta;
    public $sql;
    public $offset;
    public $suma;
    public function __construct() {
        parent::__construct(__CLASS__, 'Cortes', 'Agua Potable');
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
        $this->sql = "SELECT agua_abonados.secuencia, agua_abonados.cuenta, Sum(facturascli.total), Count(facturascli.idfactura), agua_abonados.cedula, agua_abonados.apellidos, agua_abonados.direccion, agua_abonados.medidor FROM agua_abonados INNER JOIN facturascli ON agua_abonados.cuenta = facturascli.cuenta WHERE facturascli.pagada=FALSE GROUP BY agua_abonados.cuenta, agua_abonados.cedula, agua_abonados.apellidos, agua_abonados.direccion, agua_abonados.secuencia, agua_abonados.medidor HAVING Count(facturascli.idfactura) > 2 ORDER BY secuencia, cuenta ASC";
        $this->cobros = $this->db->select_limit($this->sql, 1000, $this->offset);
        
    }
    
    public function sumar() {
        $this->sql2 = "SELECT Sum(facturascli.total) FROM agua_abonados INNER JOIN facturascli ON agua_abonados.cuenta = facturascli.cuenta WHERE facturascli.pagada=FALSE GROUP BY agua_abonados.cuenta, agua_abonados.cedula, agua_abonados.apellidos, agua_abonados.direccion, agua_abonados.secuencia, agua_abonados.medidor HAVING Count(facturascli.idfactura) > 2 ORDER BY secuencia ASC";
        $this->suma = $this->db->select_limit($this->sql2, 1000, $this->offset);
        return print_r("$ ".current($this->suma[0]));
    }
    
}
