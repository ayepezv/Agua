<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of agua_ciclo_edit
 *
 * @author Andres
 */
class edit_agua_ciclo extends fs_edit_controller{
    
    public function __construct() {
     
      parent::__construct(__CLASS__, 'Parámetros', 'Agua Potable');   // PUT HERE MENU OPTION WHERE INSTALL CONTROLLER 
   }
   
   public function get_model_class_name()
    {
        return 'agua_ciclo';
    }
    protected function set_edit_columns()
    {
        $this->form->add_column('cic_num', 'number', 'Número de Ciclo', 2, true);
        $this->form->add_column('cic_nombre', 'string', 'Nombre de Ciclo', 2, true);
        $this->form->add_column('cic_fecha', 'date','Fecha de Facturación', 3);
        $this->form->add_column('cic_proceso_mes', 'number', 'Mes Facturado', 2,['2018','2019','2020','2021']);
        $this->form->add_column('cic_proceso_anio', 'number', 'Año Facturado', 2,['1','2','3','4','5','6','7','8','9','10','11','12']);
    }
    
}
