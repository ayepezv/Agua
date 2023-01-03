<?php


class edit_agua_abonado extends fs_edit_controller{
    
    public function __construct() {
     
      parent::__construct(__CLASS__, 'Abonados', 'Agua Potable');   // PUT HERE MENU OPTION WHERE INSTALL CONTROLLER 
   }
   
   public function get_model_class_name()
    {
        return 'agua_abonados';
    }
    protected function set_edit_columns()
    {
        /*$cuenta= $this->sql_distinct('agua_abonados', 'cuenta');
        $this->form->add_column_select('cuenta', $cuenta, 'Cuenta', 2, true);*/
        
        $this->form->add_column('cuenta', 'integer', 'Número de Cuenta', 2, true);
        $this->form->add_column('cedula', 'string', 'CI / RUC', 2, true);
        $this->form->add_column('nombres', 'string', 'Nombres', 2);
        $this->form->add_column('apellidos', 'string', 'Apellidos', 2);
        $this->form->add_column('fecha_nacimiento', 'date', 'Fecha Nacimiento', 2);
        $this->form->add_column('direccion', 'string', 'Dirección', 4);
        $this->form->add_column('medidor', 'string', 'Número Medidor', 2, true);
        
    }
    
}
