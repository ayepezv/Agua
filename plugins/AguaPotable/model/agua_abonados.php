<?php


class agua_abonados extends fs_extended_model{
    
    public function __construct($data = FALSE) {
        parent::__construct('agua_abonados', $data);
    }
    
    public function model_class_name() {
        return 'agua_abonados';
    }
    
    public function primary_column() {
        return 'cuenta';
    }
    public function url($type = 'auto') {
        if ($type === 'list') {
        
            return 'index.php?page=agua_abonado';
        }
        return parent::url($type);
    }
}
