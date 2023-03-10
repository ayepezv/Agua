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

class contabilidad_series extends fbase_controller
{

    public $ejercicios;
    public $num_personalizada;
    public $serie;

    public function __construct()
    {
        parent::__construct(__CLASS__, ucfirst(FS_SERIES), 'contabilidad');
    }

    protected function private_core()
    {
        parent::private_core();

        $eje = new ejercicio();
        $this->ejercicios = $eje->all();
        $this->serie = new serie();

        $this->numeracion_personalizada();

        if (isset($_POST['codserie'])) {
            $this->editar_serie();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_serie();
        }
    }

    private function numeracion_personalizada()
    {
        $fsvar = new fs_var();
        if (isset($_GET['num_personalizada'])) {
            if ($_GET['num_personalizada'] == 'TRUE') {
                $this->num_personalizada = TRUE;
                $fsvar->simple_save('numeracion_personalizada', $this->num_personalizada);
            } else {
                $this->num_personalizada = FALSE;
                $fsvar->simple_delete('numeracion_personalizada');
            }
        } else {
            $this->num_personalizada = $fsvar->simple_get('numeracion_personalizada');
        }
    }

    private function editar_serie()
    {
        $serie = $this->serie->get($_POST['codserie']);
        if (!$serie) {
            $serie = new serie();
            $serie->codserie = $_POST['codserie'];
        }

        $serie->descripcion = $_POST['descripcion'];
        $serie->siniva = isset($_POST['siniva']);
        $serie->irpf = floatval($_POST['irpf']);

        if ($this->num_personalizada) {
            if ($_POST['codejercicio'] != $serie->codejercicio || $_POST['numfactura'] != $serie->numfactura) {
                if ($this->user->admin) {
                    if ($this->hay_facturas_venta($serie->codserie)) {
                        $this->new_error_msg('Ya hay facturas con esta serie, no puedes cambiar la numeraci??n inicial.');
                    } else {
                        $serie->codejercicio = NULL;
                        $serie->numfactura = 1;

                        if ($_POST['codejercicio'] != '') {
                            $serie->codejercicio = $_POST['codejercicio'];
                            $serie->numfactura = intval($_POST['numfactura']);

                            /// anotamos el cambio en el log
                            $fslog = new fs_log();
                            $fslog->alerta = TRUE;
                            $fslog->detalle = 'Se ha cambiado la numeraci??n inicial de la serie '
                                . $serie->codserie . ' para el ejercicio ' . $serie->codejercicio
                                . '. Nuevo n??mero inicial: ' . $serie->numfactura;
                            $fslog->ip = $this->user->last_ip;
                            $fslog->usuario = $this->user->nick;
                            $fslog->tipo = 'serie';
                            $fslog->save();
                        }
                    }
                } else {
                    $this->new_error_msg("La numeraci??n de facturas es una cosa delicada,"
                        . " solamente un administrador puede hacer cambios.", 'serie', TRUE);
                }
            }
        }

        if ($serie->save()) {
            $this->new_message('Datos guardados correctamente.');
        } else
            $this->new_error_msg("??Imposible guardar " . FS_SERIE . "!");
    }

    private function eliminar_serie()
    {
        if (!$this->user->admin) {
            $this->new_error_msg('S??lo un administrador puede eliminar ' . FS_SERIES . '.');
        } else {
            $serie = $this->serie->get($_GET['delete']);
            if ($serie) {
                if ($this->hay_facturas_venta($serie->codserie)) {
                    $this->new_error_msg("Imposible eliminar. Ya ha documentos vinculados a esta " . FS_SERIE . ".");
                } else if ($serie->delete()) {
                    $this->new_message('Datos eliminados correctamente: ' . FS_SERIE . ' ' . $serie->codserie, TRUE);
                } else
                    $this->new_error_msg("??Imposible eliminar " . FS_SERIE . ' ' . $serie->codserie . "!");
            } else
                $this->new_error_msg('Datos no encontrados: ' . FS_SERIE . ' ' . $serie->codserie);
        }
    }

    /**
     * Devuelve TRUE si ya existen facturas o albaranes en la serie $codserie
     * @param type $codserie
     * @return boolean
     */
    private function hay_facturas_venta($codserie)
    {
        $hay = FALSE;

        $tablas = array('facturascli', 'albaranescli', 'pedidoscli', 'presupuescli', 'facturasprov', 'albaranesprov', 'pedidosprov');
        foreach ($tablas as $tabla) {
            if ($this->db->table_exists($tabla)) {
                $sql = "SELECT * FROM " . $tabla . " WHERE codserie = " . $this->empresa->var2str($codserie);
                $data = $this->db->select_limit($sql, 5, 0);
                if ($data) {
                    $hay = TRUE;
                    break;
                }
            }
        }

        return $hay;
    }
}
