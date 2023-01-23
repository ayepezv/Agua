<?php


class agua_planilla extends fs_list_controller {

   public function __construct() {
     
      parent::__construct(__CLASS__, 'Consulta Valores', 'Agua Potable');   // PUT HERE MENU OPTION WHERE INSTALL CONTROLLER 
   }

   protected function create_tabs() {
       /// pestaña de planillas 
       $this->add_tab('Planilla', 'Planillas Agua', 'agua_planilla');
       $cuenta = $this->sql_distinct('agua_planilla', 'cuenta');
       $this->add_filter_select('Planilla', 'cuenta', 'Cuenta',$cuenta);
       $pagado = $this->sql_distinct('agua_planilla', 'estado_pago');
       $this->add_filter_select('Planilla', 'estado_pago', 'Estado Planilla',$pagado);
       ///$this->add_search_columns('Planilla', ['usuario_cobro']); NO ME FUNCIONA BUSCAR NUMEROS DE LA CUENTA
       $this->add_sort_option('Planilla', ['cuenta','anio','mes']);
       
       ///columnas a mostrar planillas
       $this->decoration->add_column('Planilla', 'cuenta','integer','Cuenta No.');
       $this->decoration->add_column('Planilla', 'mes','integer', 'Mes');
       $this->decoration->add_column('Planilla', 'anio','integer', 'Año');
       $this->decoration->add_column('Planilla', 'lec_anterior','integer', 'Lec. Ant.');
       $this->decoration->add_column('Planilla', 'lect_actual','integer', 'Lec. Actual');
       $this->decoration->add_column('Planilla', 'consumo','integer', 'Consumo m3');
       $this->decoration->add_column('Planilla', 'total','money', 'Consumo m3');
       ///$this->decoration->add_column('Planilla', 'usuario_cobro','string', 'Usuario Cobro');
       $this->decoration->add_row_url('Planilla', 'index.php?page=ver_agua_ciclo&code=', 'cic_id');
       
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