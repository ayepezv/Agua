<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of agua_ciclo
 *
 * @author Andres
 */
class agua_co_sector extends fs_extended_model{
    public $id;
    public $columna1;

    public function __construct($data = FALSE)
    {
        parent::__construct('agua_co_sector', $data); /// aquí indicamos el NOMBRE DE LA TABLA
    }

    public function model_class_name()
    {
        return 'agua_co_sector';
    }

    public function primary_column()
    {
        return 'sec_id';
    }
    public function url($type = 'auto') {
        if($type === 'list'){
            return 'index.php?page=agua_parametro';
        }
            
        return parent::url($type);
    }
}
