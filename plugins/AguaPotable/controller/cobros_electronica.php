<?php

class cobros_electronica extends fs_controller {

    public $cobros;
    public $desde;
    public $hasta;
    public $sql;
    public $offset;
    public $suma;
    public function __construct() {
        parent::__construct(__CLASS__, 'Factura Electronica', 'informes');
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
        $this->sql = "SELECT v_factura_electronica_pendientes_y_autorizadas.identificador,v_factura_electronica_pendientes_y_autorizadas.factura, v_factura_electronica_pendientes_y_autorizadas.nombre_cliente, v_factura_electronica_pendientes_y_autorizadas.ci_ruc_cliente, v_factura_electronica_pendientes_y_autorizadas.base_impoble,v_factura_electronica_pendientes_y_autorizadas.fecha_crea,v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza, v_factura_electronica_pendientes_y_autorizadas.clave_acceso, v_factura_electronica_pendientes_y_autorizadas.observacion FROM v_factura_electronica_pendientes_y_autorizadas WHERE v_factura_electronica_pendientes_y_autorizadas.estado=1 and v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza >= '".$this->desde ."' and v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza <= '".$this->hasta."'";
        print("Autorizada".$this->sql);
		$this->autorizada = $this->db->select_limit($this->sql, 500, $this->offset);
		$this->sql1 = "SELECT v_factura_electronica_pendientes_y_autorizadas.identificador,v_factura_electronica_pendientes_y_autorizadas.factura, v_factura_electronica_pendientes_y_autorizadas.nombre_cliente, v_factura_electronica_pendientes_y_autorizadas.ci_ruc_cliente, v_factura_electronica_pendientes_y_autorizadas.base_impoble,v_factura_electronica_pendientes_y_autorizadas.fecha_crea,v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza, v_factura_electronica_pendientes_y_autorizadas.clave_acceso, v_factura_electronica_pendientes_y_autorizadas.observacion FROM v_factura_electronica_pendientes_y_autorizadas WHERE v_factura_electronica_pendientes_y_autorizadas.estado=0 and v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza >= '".$this->desde ."' and v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza <= '".$this->hasta."'";
        print("Pendiente".$this->sql1);
		$this->pendiente = $this->db->select_limit($this->sql1, 500, $this->offset);
        
        
    }
    
    public function sumaautorizada() {
	$this->sql2 = "SELECT SUM(v_factura_electronica_pendientes_y_autorizadas.base_impoble) AS total FROM v_factura_electronica_pendientes_y_autorizadas WHERE v_factura_electronica_pendientes_y_autorizadas.estado=1 and v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza >= '".$this->desde ."' and v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza <= '".$this->hasta."'";
        $this->sumaautorizada = $this->db->select_limit($this->sql2, 500, $this->offset);
        return current($this->sumaautorizada[0]);
    }
	
	public function sumapendiente() {
	$this->sql3 = "SELECT SUM(v_factura_electronica_pendientes_y_autorizadas.base_impoble) AS total FROM v_factura_electronica_pendientes_y_autorizadas WHERE v_factura_electronica_pendientes_y_autorizadas.estado=0 and v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza >= '".$this->desde ."' and v_factura_electronica_pendientes_y_autorizadas.fecha_autoriza <= '".$this->hasta."'";
        $this->sumaautorizada = $this->db->select_limit($this->sql3, 500, $this->offset);
        return current($this->sumaautorizada[0]);
    }
    
}
