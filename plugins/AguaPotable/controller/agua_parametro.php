<?php


class agua_parametro extends fs_list_controller {

   public function __construct() {
     
      parent::__construct(__CLASS__, 'Parámetros', 'Agua Potable');   // PUT HERE MENU OPTION WHERE INSTALL CONTROLLER 
   }

   protected function create_tabs() {
       /// pestaña de ciclos 
       $this->add_tab('Ciclo', 'Ciclos AP', 'agua_ciclo');
       $this->add_search_columns('Ciclo', ['cic_nombre','cic_proceso_estado']);
       $this->add_sort_option('Ciclo', ['cic_proceso_mes','cic_proceso_anio']);
       $this->add_sort_option('Ciclo', ['cic_nombre'],1);
       
       ///columnas a mostrar ciclos
       $this->decoration->add_column('Ciclo', 'cic_nombre',string,'Nombre del Ciclo');
       $this->decoration->add_column('Ciclo', 'cic_num','integer', 'Número de Ciclo');
       $this->decoration->add_column('Ciclo', 'cic_proceso_mes','integer', 'Mes del Ciclo');
       $this->decoration->add_column('Ciclo', 'cic_proceso_anio','integer', 'Año del Ciclo');
       $this->decoration->add_column('Ciclo', 'cic_fecha','date', 'Fecha del Ciclo');
       $this->decoration->add_row_url('Ciclo', 'index.php?page=edit_agua_ciclo&code=', 'cic_id');
       
       ///agregar botones ciclos
       $this->add_button('Ciclo', 'nuevo', 'index.php?page=edit_agua_ciclo','fa-plus','btn-success');
       
       /// pestaña de sector 
       $this->add_tab('Sector', 'Sectores AP', 'agua_co_sector');
       $this->add_search_columns('Sector', ['sec_nombre','sec_num']);
       $this->add_sort_option('Sector', ['sec_nombre'],1);
       
       ///columnas a mostrar sectores
       $this->decoration->add_column('Sector', 'sec_nombre','string','Nombre del Sector');
       $this->decoration->add_column('Sector', 'sec_num','integer', 'Número de Sector');
       $this->decoration->add_column('Sector', 'cic_id','integer', 'Ciclo');
       $this->decoration->add_row_url('Sector', 'index.php?page=edit_agua_co_sector&code=', 'sec_id');
       
       ///agregar botones sectores
       $this->add_button('Sector', 'nuevo', 'index.php?page=edit_agua_co_sector','fa-plus','btn-success');
       
   }

}