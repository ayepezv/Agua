<?php
/*
 * This file is part of facturacion_base
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'plugins/facturacion_base/extras/fbase_controller.php';

class contabilidad_asientos extends fbase_controller
{

    public $asiento;
    public $desde;
    public $hasta;
    public $mostrar;
    public $num_resultados;
    public $offset;
    public $orden;
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Asientos', 'contabilidad');
    }

    protected function private_core()
    {
        parent::private_core();

        $this->asiento = new asiento();
        $this->ini_filters();

        if (isset($_GET['delete'])) {
            $this->eliminar_asiento();
        } else if (isset($_GET['renumerar'])) {
            if ($this->asiento->renumerar()) {
                $this->new_message("Asientos renumerados.");
            }
        }

        if ($this->mostrar == 'descuadrados') {
            $this->resultados = $this->asiento->descuadrados();
        } else {
            $this->buscar();
        }
    }

    private function ini_filters()
    {
        $this->mostrar = 'todo';
        if (isset($_GET['mostrar'])) {
            $this->mostrar = $_GET['mostrar'];
        }

        $this->offset = 0;
        if (isset($_GET['offset'])) {
            $this->offset = intval($_GET['offset']);
        }

        $this->desde = '';
        $this->hasta = '';
        $this->orden = 'fecha DESC, numero DESC';
        if (isset($_REQUEST['desde']) || isset($_REQUEST['hasta']) || isset($_REQUEST['orden'])) {
            $this->desde = $_REQUEST['desde'];
            $this->hasta = $_REQUEST['hasta'];
            $this->orden = $_REQUEST['orden'];
        }
    }

    private function buscar()
    {
        $this->resultados = array();
        $this->num_resultados = 0;
        $query = $this->empresa->no_html(mb_strtolower($this->query, 'UTF8'));
        $sql = " FROM co_asientos ";
        $where = 'WHERE ';

        if ($query == '') {
            /// nada
        } else if (is_numeric($query)) {
            $aux_sql = '';
            if (strtolower(FS_DB_TYPE) == 'postgresql') {
                $aux_sql = '::TEXT';
            }

            $sql .= $where . "(numero" . $aux_sql . " LIKE '%" . $query . "%' OR concepto LIKE '%" . $query
                . "%' OR importe BETWEEN " . ($query - .01) . " AND " . ($query + .01) . ')';
            $where = ' AND ';
        } else {
            $sql .= $where . "(lower(concepto) LIKE '%" . str_replace(' ', '%', $query) . "%')";
            $where = ' AND ';
        }

        if ($this->desde != '') {
            $sql .= $where . "fecha >= " . $this->empresa->var2str($this->desde);
            $where = ' AND ';
        }

        if ($this->hasta != '') {
            $sql .= $where . "fecha <= " . $this->empresa->var2str($this->hasta);
            $where = ' AND ';
        }

        $data = $this->db->select("SELECT COUNT(idasiento) as total" . $sql);
        if ($data) {
            $this->num_resultados = intval($data[0]['total']);

            $data2 = $this->db->select_limit("SELECT *" . $sql . ' ORDER BY ' . $this->orden, FS_ITEM_LIMIT, $this->offset);
            if ($data2) {
                foreach ($data2 as $d) {
                    $this->resultados[] = new asiento($d);
                }
            }
        }
    }

    public function paginas()
    {
        return $this->fbase_paginas($this->url(TRUE), $this->num_resultados, $this->offset);
    }

    public function url($busqueda = FALSE)
    {
        if ($busqueda) {
            return parent::url() . '&query=' . $this->query
                . "&desde=" . $this->desde
                . "&hasta=" . $this->hasta
                . "&orden=" . $this->orden;
        }

        return parent::url();
    }

    private function eliminar_asiento()
    {
        $asiento = $this->asiento->get($_GET['delete']);
        if ($asiento) {
            if (!$this->allow_delete) {
                $this->new_error_msg("No tienes permiso para eliminar en esta p??gina.");
            } else if ($asiento->delete()) {
                $this->new_message("Asiento eliminado correctamente. (ID: " . $asiento->idasiento . ", " . $asiento->fecha . ")", TRUE);
                $this->clean_last_changes();
            } else {
                $this->new_error_msg("??Imposible eliminar el asiento!");
            }
        } else {
            $this->new_error_msg("??Asiento no encontrado!");
        }
    }
}
