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

class contabilidad_asiento extends fbase_controller
{

    public $asiento;
    public $divisa;
    public $ejercicio;
    public $impuesto;
    public $lineas;
    public $resultados;
    public $subcuenta;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Asiento', 'contabilidad', FALSE, FALSE);
    }

    protected function private_core()
    {
        parent::private_core();

        $this->asiento = FALSE;
        $this->ppage = $this->page->get('contabilidad_asientos');
        $this->divisa = new divisa();
        $this->ejercicio = new ejercicio();
        $this->impuesto = new impuesto();
        $this->subcuenta = new subcuenta();

        if (isset($_GET['id'])) {
            $asiento = new asiento();
            $this->asiento = $asiento->get($_GET['id']);
        }

        if (isset($_POST['fecha']) && isset($_POST['query'])) {
            $this->new_search();
        } else if ($this->asiento) {
            $this->page->title = 'Asiento: ' . $this->asiento->numero;

            if (isset($_GET['bloquear'])) {
                $this->bloquear();
            } else if (isset($_GET['desbloquear'])) {
                $this->desbloquear();
            } else if (isset($_POST['fecha']) && $this->asiento->editable) {
                $this->modificar();
            }

            /// comprobamos el asiento
            $this->asiento->full_test();

            $this->lineas = $this->get_lineas_asiento();
        } else {
            $this->new_error_msg("Asiento no encontrado.", 'error', FALSE, FALSE);
        }
    }

    public function url()
    {
        if (!isset($this->asiento)) {
            return parent::url();
        } else if ($this->asiento) {
            return $this->asiento->url();
        }

        return $this->ppage->url();
    }

    private function new_search()
    {
        /// cambiamos la plantilla HTML
        $this->template = 'ajax/contabilidad_nuevo_asiento';

        $eje0 = $this->ejercicio->get_by_fecha($_POST['fecha']);
        if ($eje0) {
            $this->resultados = $this->subcuenta->search_by_ejercicio($eje0->codejercicio, $this->query);
        } else {
            $this->resultados = array();
        }
    }

    private function bloquear()
    {
        $this->asiento->editable = FALSE;
        if ($this->asiento->save()) {
            $this->new_message('Asiento bloqueado correctamente.');
        } else {
            $this->new_error_msg('Imposible bloquear el asiento.');
        }
    }

    private function desbloquear()
    {
        $ejercicio = $this->ejercicio->get($this->asiento->codejercicio);
        if ($ejercicio) {
            if ($ejercicio->abierto()) {
                $this->asiento->editable = TRUE;

                $regiva0 = new regularizacion_iva();
                $excluir = array($ejercicio->idasientoapertura, $ejercicio->idasientocierre, $ejercicio->idasientopyg);
                if ($regiva0->get_fecha_inside($this->asiento->fecha) && ! in_array($this->asiento->idasiento, $excluir)) {
                    $this->asiento->editable = FALSE;
                    $this->new_error_msg('El asiento est?? dentro de una regularizaci??n de '
                        . FS_IVA . '. No se puede modificar.');
                } else if ($this->asiento->save()) {
                    $this->new_message('Asiento desbloqueado correctamente.');
                } else {
                    $this->new_error_msg('Imposible desbloquear el asiento.');
                }
            } else {
                $this->new_error_msg('Imposible desbloquear el asiento: el ejercicio '
                    . $ejercicio->nombre . ' est?? cerrado.');
            }
        }
    }

    private function modificar()
    {
        $bloquear = TRUE;

        $ejercicio = $this->ejercicio->get($this->asiento->codejercicio);
        if ($ejercicio) {
            if ($ejercicio->abierto()) {
                $regiva0 = new regularizacion_iva();
                $excluir = array($ejercicio->idasientoapertura, $ejercicio->idasientocierre, $ejercicio->idasientopyg);
                if ($regiva0->get_fecha_inside($this->asiento->fecha) && ! in_array($this->asiento->idasiento, $excluir)) {
                    $this->new_error_msg('El asiento est?? dentro de una regularizaci??n de '
                        . FS_IVA . '. No se puede modificar.');
                } else {
                    $this->modificar2($ejercicio);
                    $bloquear = FALSE;
                }
            } else {
                $this->new_error_msg('Imposible modificar el asiento: el ejercicio '
                    . $ejercicio->nombre . ' est?? cerrado.');
            }
        }

        if ($bloquear && $this->asiento->editable) {
            $this->asiento->editable = FALSE;
            $this->asiento->save();
        }
    }

    private function modificar2(&$eje0)
    {
        if ($_POST['fecha'] != $this->asiento->fecha) {
            /// necesitamos el ejercicio para poder acotar la fecha
            $regiva0 = new regularizacion_iva();

            if (strtotime($eje0->fechainicio) > strtotime($_POST['fecha'])) {
                $this->new_error_msg('La fecha ' . $_POST['fecha'] . ' est?? fuera del'
                    . ' rango del ejercicio ' . $eje0->codejercicio . '.');
            } else if (strtotime($eje0->fechafin) < strtotime($_POST['fecha'])) {
                $this->new_error_msg('La fecha ' . $_POST['fecha'] . ' est?? fuera del'
                    . ' rango del ejercicio ' . $eje0->codejercicio . '.');
            } else if ($regiva0->get_fecha_inside($_POST['fecha'])) {
                $this->new_error_msg('No se puede asignar la fecha ' . $_POST['fecha'] . ' porque ya hay'
                    . ' una regularizaci??n de ' . FS_IVA . ' para ese periodo.');
            } else {
                $this->asiento->fecha = $_POST['fecha'];
            }
        }

        $this->asiento->concepto = $_POST['concepto'];
        $this->asiento->importe = floatval($_POST['importe']);

        /// obtenemos la divisa de las partidas
        $div0 = $this->divisa->get($_POST['divisa']);

        if (!$eje0 || ! $div0) {
            $this->new_error_msg('Imposible modificar el asiento.');
        } else if ($this->asiento->save()) {
            $continuar = TRUE;
            $numlineas = intval($_POST['numlineas']);

            /// eliminamos las partidas que faltan
            foreach ($this->asiento->get_partidas() as $pa) {
                $encontrada = FALSE;
                for ($i = 1; $i <= $numlineas; $i++) {
                    if (isset($_POST['idpartida_' . $i]) && intval($_POST['idpartida_' . $i]) == $pa->idpartida) {
                        $encontrada = TRUE;
                        break;
                    }
                }
                if (!$encontrada && !$pa->delete()) {
                    $this->new_error_msg('Imposible eliminar la l??nea debe=' . $pa->debe . ' haber=' . $pa->haber);
                    $continuar = FALSE;
                    break;
                }
            }

            /// a??adimos y modificamos
            $subcuentas_recalcular = array();
            $npartida = new partida();
            for ($i = 1; $i <= $numlineas; $i++) {
                if (isset($_POST['idpartida_' . $i])) {
                    if ($_POST['idpartida_' . $i] == '-1') {
                        /// las nuevas l??neas llevan idpartida = -1
                        $partida = new partida();
                    } else {
                        $partida = $npartida->get($_POST['idpartida_' . $i]);
                        if ($partida) {
                            if ($partida->codsubcuenta != $_POST['codsubcuenta_' . $i]) {
                                /// si hemos cambiado de subcuenta, hay que recalcular el saldo de la anterior
                                $subcuentas_recalcular[] = $partida->get_subcuenta();
                            }
                        } else {
                            $this->new_error_msg('Partida de ' . $_POST['codsubcuenta_' . $i] . ' no encontrada.');
                            $continuar = FALSE;
                        }
                    }

                    if ($continuar) {
                        /// a??adimos
                        $sub0 = $this->subcuenta->get_by_codigo($_POST['codsubcuenta_' . $i], $eje0->codejercicio);
                        if ($sub0) {
                            $partida->idasiento = $this->asiento->idasiento;
                            $partida->coddivisa = $div0->coddivisa;
                            $partida->tasaconv = $div0->tasaconv;
                            $partida->idsubcuenta = $sub0->idsubcuenta;
                            $partida->codsubcuenta = $sub0->codsubcuenta;
                            $partida->debe = floatval($_POST['debe_' . $i]);
                            $partida->haber = floatval($_POST['haber_' . $i]);
                            $partida->idconcepto = $this->asiento->idconcepto;
                            $partida->concepto = $this->asiento->concepto;
                            $partida->documento = $this->asiento->documento;
                            $partida->tipodocumento = $this->asiento->tipodocumento;

                            $partida->idcontrapartida = NULL;
                            $partida->codcontrapartida = NULL;
                            $partida->cifnif = NULL;
                            $partida->iva = 0;
                            $partida->baseimponible = 0;
                            if (isset($_POST['codcontrapartida_' . $i]) && $_POST['codcontrapartida_' . $i] != '') {
                                $subc1 = $this->subcuenta->get_by_codigo($_POST['codcontrapartida_' . $i], $eje0->codejercicio);
                                if ($subc1) {
                                    $partida->idcontrapartida = $subc1->idsubcuenta;
                                    $partida->codcontrapartida = $subc1->codsubcuenta;
                                    $partida->cifnif = $_POST['cifnif_' . $i];
                                    $partida->iva = floatval($_POST['iva_' . $i]);
                                    $partida->baseimponible = floatval($_POST['baseimp_' . $i]);
                                } else {
                                    $this->new_error_msg('Subcuenta ' . $_POST['codcontrapartida_' . $i] . ' no encontrada.');
                                    $continuar = FALSE;
                                }
                            }

                            if (!$partida->save()) {
                                $this->new_error_msg('Imposible guardar la partida de la subcuenta ' . $_POST['codsubcuenta_' . $i] . '.');
                                $continuar = FALSE;
                            }
                        } else {
                            $this->new_error_msg('Subcuenta ' . $_POST['codsubcuenta_' . $i] . ' de la l??nea ' . $i . ' no encontrada.');
                            $continuar = FALSE;
                        }
                    } else {
                        break;
                    }
                }
            }

            /// recalculamos el saldo de las subcuentas cambiadas
            foreach ($subcuentas_recalcular as $scr) {
                $scr->save();
            }

            if ($continuar) {
                $this->new_message('Asiento modificado correctamente.');
                $this->new_change('Asiento ' . $this->asiento->numero, $this->asiento->url());
            }
        } else {
            $this->new_error_msg('Imposible modificar el asiento.');
        }
    }

    private function get_lineas_asiento()
    {
        $lineas = $this->asiento->get_partidas();
        $subc = new subcuenta();

        foreach ($lineas as $i => $lin) {
            $subcuenta = $subc->get($lin->idsubcuenta);
            if ($subcuenta) {
                $lineas[$i]->desc_subcuenta = $subcuenta->descripcion;
                $lineas[$i]->saldo = $subcuenta->saldo;
            } else {
                $lineas[$i]->desc_subcuenta = '';
                $lineas[$i]->saldo = 0;
            }
        }

        return $lineas;
    }
}
