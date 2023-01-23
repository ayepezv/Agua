<?php


class agua_abonado extends fs_list_controller {

   public function __construct() {
     
      parent::__construct(__CLASS__, 'Abonados', 'Agua Potable');   // PUT HERE MENU OPTION WHERE INSTALL CONTROLLER 
   }

   protected function create_tabs() {
       /// pestaña de planillas 
       $this->add_tab('Abonados', 'Abonados de Agua', 'agua_abonados');
       $cuenta = $this->sql_distinct('agua_abonados', 'cuenta');
       $this->add_filter_select('Abonados', 'cuenta', 'Cuenta',$cuenta);
       $tarifa = $this->sql_distinct('agua_abonados', 'tarifa_completa');
       $this->add_filter_select('Abonados', 'tarifa_completa', 'Tarifa',$tarifa);
       $this->add_search_columns('Abonados', ['apellidos','nombres','cedula']);
       $this->add_sort_option('Abonados', ['cuenta']);
       
       ///columnas a mostrar planillas
       $this->decoration->add_column('Abonados', 'cuenta','integer','Cuenta No.');
       $this->decoration->add_column('Abonados', 'cedula','string', 'CI / RUC');
       $this->decoration->add_column('Abonados', 'apellidos','string', 'Apellidos');
       $this->decoration->add_column('Abonados', 'nombres','string', 'Nombres');
       $this->decoration->add_column('Abonados', 'medidor','string', 'Medidor');
       $this->decoration->add_column('Abonados', 'tarifa_completa','string', 'Tarifa');
       $this->decoration->add_column('Abonados', 'direccion','string', 'Dirección');
       ///$this->decoration->add_column('Planilla', 'usuario_cobro','string', 'Usuario Cobro');
       $this->decoration->add_row_url('Abonados', 'index.php?page=edit_agua_abonado&code=', 'cuenta');
       
       ///agregar botones ciclos
       ///$this->add_button('Ciclo', 'nuevo', 'index.php?page=edit_agua_ciclo','fa-plus','btn-success');
       /*
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
       */
   }

}