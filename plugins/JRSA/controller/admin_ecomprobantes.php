<?php
class admin_ecomprobantes extends fs_list_controller {

  public function __construct() {
    /// se crea una entrada 'Mi controlador' dentro del menú 'Mio'
    parent::__construct(__CLASS__, 'Comprobantes Pendientes', 'EComprobantes');
  }
  protected function create_tabs()
  {	  
	  $this->add_tab('pendientes','Comprobantes Pendientes','ecomprobantes_jrsa');
	  //$this->add_search_columns('configuracion',['firma','ambiente']);
	  //$this->add_sort_option('configuracion',['id']);
	  //$this->add_sort_option('configuracion',['ambiente'],1);
	  ///Columnas a mostrar	  	  	  
	  $this->decoration->add_column('pendientes','id_asiento','smallint','Asiento');	  	  
	  $this->decoration->add_column('pendientes','id_cliente','string','Id_Cliente');
	  $this->decoration->add_column('pendientes','ci_ruc_cliente','string','CI/RUC');
	  $this->decoration->add_column('pendientes','nombre_cliente','string','Cliente');
	  $this->decoration->add_column('pendientes','base_impoble','number','Valor');
	  $this->decoration->add_column('pendientes','fecha_autoriza','date','Fecha Autorización');
	  $this->decoration->add_column('pendientes','mensaje','string','Mensaje');
	  $this->decoration->add_column('pendientes','estado','smallint','Estado');
	  $this->add_sort_option('pendientes', ['estado']);
	  $this->add_search_columns('pendientes', ['mensaje']);
	  //$this->decoration->add_row_url('pendientes','plugins/JRSA/factura_electronica.php?asiento=','id');	  
	  
	  
	  
	
  }
 

 }
?>