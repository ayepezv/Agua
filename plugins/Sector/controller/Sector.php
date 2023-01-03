<?php
/*
 * @author XXXX      xxx@xxx.xx
 * @copyright 2018, XXXX. All Rights Reserved.
 */

/**
 * Description of Sector
 *
 * @author xxx
 */
class Sector extends fs_controller
{

    public $texto;
    public $texto2;
    public $lista;
    public $resultados_sql;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Sector', 'admin');
    }

    protected function private_core()
    {
        $this->texto = 'hola mundo';
        $this->texto2 = 'Bla, bla, bla, bla, bla, bla, bla, bla, bla, bla, bla, bla.';
        $this->lista = array('peras', 'manzanas', 'puerros', 'naranjas');

        $this->resultados_sql = $this->db->select("select * from paises;");
    }
}
