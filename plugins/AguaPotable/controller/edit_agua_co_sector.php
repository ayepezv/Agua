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
class edit_agua_co_sector extends fs_edit_controller{
    
    public function __construct() {
     
      parent::__construct(__CLASS__, 'Parámetros', 'Agua Potable');   // PUT HERE MENU OPTION WHERE INSTALL CONTROLLER 
   }
   
   public function get_model_class_name()
    {
        return 'agua_co_sector';
    }
    protected function set_edit_columns()
    {
        $ciclos= $this->sql_distinct('agua_ciclo', 'cic_nombre');
        $this->form->add_column_select('cic_id', $ciclos, 'Ciclos', 2, true);
        
        $this->form->add_column('sec_num', 'integer', 'Número de Sector', 2, true);
        $this->form->add_column('sec_nombre', 'string', 'Nombre de Sector', 2, true);
    }
    
}
