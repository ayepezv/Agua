<?php

class cobros_planilla2 extends fs_controller {

    public $cobros;
    public $desde;
    public $hasta;
    public $sql;
    public $offset;
    public $suma;
    public function __construct() {
        parent::__construct(__CLASS__, 'Parte de Cobros 2', 'informes');
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
        $this->sql = "SELECT 'factura_batan.php?cliente='||COALESCE(co_asientos.nombre,'-')||'&cedula='||COALESCE(co_asientos.cifnif,'-')||'&valor='||co_asientos.importe||'&fecha1='||co_asientos.fecha||'&Tex='||COALESCE(meses,'-')||'&Tex1='||co_asientos.observacion||'&Me='||COALESCE(co_asientos.num_meses,0)||'&Det='||COALESCE(co_asientos.rubro,'Otros') AS url, co_asientos.codejercicio, co_asientos.numero, co_asientos.concepto, co_asientos.fecha,co_asientos.idasiento, co_asientos.importe, Sum(co_asientos.importe) AS total, facturascli.codserie FROM co_asientos INNER JOIN facturascli ON facturascli.idasientop = co_asientos.idasiento WHERE co_asientos.concepto LIKE 'Cobro%' and co_asientos.fecha >= '".$this->desde ."' and co_asientos.fecha <= '".$this->hasta ."' and facturascli.codserie='PA' GROUP BY co_asientos.concepto,co_asientos.fecha,co_asientos.idasiento, co_asientos.importe,facturascli.codserie ORDER BY co_asientos.idasiento ASC";
        $this->cobros = $this->db->select_limit($this->sql, 300, $this->offset);
        $this->sqlPuntosAgua = "SELECT 'factura_batan.php?cliente='||COALESCE(co_asientos.nombre,'-')||'&cedula='||COALESCE(co_asientos.cifnif,'-')||'&valor='||co_asientos.importe||'&fecha1='||co_asientos.fecha||'&Tex='||COALESCE(meses,'-')||'&Tex1='||co_asientos.observacion||'&Me='||COALESCE(co_asientos.num_meses,0)||'&Det='||COALESCE(co_asientos.rubro,'Otros') AS url, co_asientos.codejercicio, co_asientos.numero, co_asientos.concepto, co_asientos.fecha,co_asientos.idasiento, co_asientos.importe, Sum(co_asientos.importe) AS total, facturascli.codserie FROM co_asientos INNER JOIN facturascli ON facturascli.idasientop = co_asientos.idasiento WHERE co_asientos.concepto LIKE 'Cobro%' and co_asientos.fecha >= '".$this->desde ."' and co_asientos.fecha <= '".$this->hasta ."' and facturascli.codserie='PU' GROUP BY co_asientos.concepto,co_asientos.fecha,co_asientos.idasiento, co_asientos.importe,facturascli.codserie ORDER BY co_asientos.idasiento ASC";
        $this->cobrosPuntosAgua = $this->db->select_limit($this->sqlPuntosAgua, 300, $this->offset);
        $this->sqlPuntosAlcantarillado = "SELECT 'factura_batan.php?cliente='||COALESCE(co_asientos.nombre,'-')||'&cedula='||COALESCE(co_asientos.cifnif,'-')||'&valor='||co_asientos.importe||'&fecha1='||co_asientos.fecha||'&Tex='||COALESCE(meses,'-')||'&Tex1='||co_asientos.observacion||'&Me='||COALESCE(co_asientos.num_meses,0)||'&Det='||COALESCE(co_asientos.rubro,'Otros') AS url, co_asientos.codejercicio, co_asientos.numero, co_asientos.concepto, co_asientos.fecha,co_asientos.idasiento, co_asientos.importe, Sum(co_asientos.importe) AS total, facturascli.codserie FROM co_asientos INNER JOIN facturascli ON facturascli.idasientop = co_asientos.idasiento WHERE co_asientos.concepto LIKE 'Cobro%' and co_asientos.fecha >= '".$this->desde ."' and co_asientos.fecha <= '".$this->hasta ."' and facturascli.codserie='AL' GROUP BY co_asientos.concepto,co_asientos.fecha,co_asientos.idasiento, co_asientos.importe,facturascli.codserie ORDER BY co_asientos.idasiento ASC";
        $this->cobrosPuntosAlcantarillado = $this->db->select_limit($this->sqlPuntosAlcantarillado, 300, $this->offset);
        
    }
    
    public function sumar() {
        $this->sql2 = "SELECT SUM(facturascli.total) AS total FROM co_asientos INNER JOIN facturascli ON facturascli.idasientop = co_asientos.idasiento WHERE co_asientos.concepto LIKE 'Cobro%' and co_asientos.fecha >= '".$this->desde ."' and co_asientos.fecha <= '".$this->hasta ."' and facturascli.codserie='PA'";
        $this->suma = $this->db->select_limit($this->sql2, 100, $this->offset);
        return current($this->suma[0]);
    }

    public function sumarPuntosAgua() {
        $this->sql2 = "SELECT SUM(facturascli.total) AS total FROM co_asientos INNER JOIN facturascli ON facturascli.idasientop = co_asientos.idasiento WHERE co_asientos.concepto LIKE 'Cobro%' and co_asientos.fecha >= '".$this->desde ."' and co_asientos.fecha <= '".$this->hasta ."' and facturascli.codserie='PU'";
        $this->suma = $this->db->select_limit($this->sql2, 100, $this->offset);
        return current($this->suma[0]);
    }

    public function sumarPuntosAlcantarillado() {
        $this->sql2 = "SELECT SUM(facturascli.total) AS total FROM co_asientos INNER JOIN facturascli ON facturascli.idasientop = co_asientos.idasiento WHERE co_asientos.concepto LIKE 'Cobro%' and co_asientos.fecha >= '".$this->desde ."' and co_asientos.fecha <= '".$this->hasta ."' and facturascli.codserie='AL'";
        $this->suma = $this->db->select_limit($this->sql2, 100, $this->offset);
        return current($this->suma[0]);
    }

    public function sumarTodo() {
        $this->sql2 = "SELECT SUM(co_asientos.importe) AS total FROM co_asientos  WHERE co_asientos.concepto LIKE 'Cobro%' and co_asientos.fecha >= '".$this->desde ."' and co_asientos.fecha <= '".$this->hasta ."'";
        $this->suma = $this->db->select_limit($this->sql2, 100, $this->offset);
        return current($this->suma[0]);
    }
    
}
