<?php
class admin_jrsa extends fs_list_controller {

  public function __construct() {
    /// se crea una entrada 'Mi controlador' dentro del menú 'Mio'
    parent::__construct(__CLASS__, 'Configuración', 'EComprobantes');
  }
  protected function create_tabs()
  {	  
	  $this->add_tab('configuracion','Configuración','configura_jrsa');
	  //$this->add_search_columns('configuracion',['firma','ambiente']);
	  //$this->add_sort_option('configuracion',['id']);
	  //$this->add_sort_option('configuracion',['ambiente'],1);
	  ///Columnas a mostrar
	  $this->decoration->add_column('configuracion','id','smallint','ID');
	  $this->decoration->add_column('configuracion','ambiente','smallint','Ambiente');
	  $this->decoration->add_column('configuracion','contabilidad','bool','Contabilidad');
	  $this->decoration->add_column('configuracion','firma','string','Firma');
	  $this->decoration->add_column('configuracion','rimpe','bool','RIMPE');
	  $this->decoration->add_column('configuracion','logo','string','Logo');	
	  $this->decoration->add_column('configuracion','estado','bool','Activo');
	  $this->decoration->add_row_url('configuracion','index.php?page=edit_configura_jrsa&code=','id');
	  
	  //colores
	  $this->decoration->add_row_option('configuracion','firma','','warning');
	  //botones
	  
	  $values = $this->sql_distinct('configura_jrsa', 'id');	  
	  if (count($values)===0)
	  {
		$this->add_button('configuracion','nuevo','index.php?page=edit_configura_jrsa','fa-plus','btn-success');
	  }	  
	//$this->new_message(directoriofjrsa());
	
  }
 

 }
?>