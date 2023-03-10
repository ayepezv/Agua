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

class ventas_albaranes extends fbase_controller
{

    public $agente;
    public $almacenes;
    public $articulo;
    public $buscar_lineas;
    public $cliente;
    public $codagente;
    public $codalmacen;
    public $codgrupo;
    public $codpago;
    public $codserie;
    public $desde;
    public $forma_pago;
    public $grupo;
    public $hasta;
    public $lineas;
    public $mostrar;
    public $num_resultados;
    public $offset;
    public $order;
    public $resultados;
    public $serie;
    public $total_resultados;
    public $total_resultados_txt;

    public function __construct()
    {
        parent::__construct(__CLASS__, ucfirst(FS_ALBARANES), 'ventas');
    }

    protected function private_core()
    {
        parent::private_core();

        $albaran = new albaran_cliente();
        $this->agente = new agente();
        $this->almacenes = new almacen();
        $this->forma_pago = new forma_pago();
        $this->grupo = new grupo_clientes();
        $this->serie = new serie();

        $this->mostrar = 'todo';
        if (isset($_GET['mostrar'])) {
            $this->mostrar = $_GET['mostrar'];
            setcookie('ventas_alb_mostrar', $this->mostrar, time() + FS_COOKIES_EXPIRE);
        } else if (isset($_COOKIE['ventas_alb_mostrar'])) {
            $this->mostrar = $_COOKIE['ventas_alb_mostrar'];
        }

        $this->offset = 0;
        if (isset($_REQUEST['offset'])) {
            $this->offset = intval($_REQUEST['offset']);
        }

        $this->order = 'fecha DESC';
        if (isset($_GET['order'])) {
            $orden_l = $this->orden();
            if (isset($orden_l[$_GET['order']])) {
                $this->order = $orden_l[$_GET['order']]['orden'];
            }

            setcookie('ventas_alb_order', $this->order, time() + FS_COOKIES_EXPIRE);
        } else if (isset($_COOKIE['ventas_alb_order'])) {
            $this->order = $_COOKIE['ventas_alb_order'];
        }

        if (isset($_POST['buscar_lineas'])) {
            $this->buscar_lineas();
        } else if (isset($_REQUEST['buscar_cliente'])) {
            $this->fbase_buscar_cliente($_REQUEST['buscar_cliente']);
        } else if (isset($_GET['ref'])) {
            $this->template = 'extension/ventas_albaranes_articulo';

            $articulo = new articulo();
            $this->articulo = $articulo->get($_GET['ref']);

            $linea = new linea_albaran_cliente();
            $this->resultados = $linea->all_from_articulo($_GET['ref'], $this->offset);
        } else {
            $this->share_extension();
            $this->cliente = FALSE;
            $this->codagente = '';
            $this->codalmacen = '';
            $this->codgrupo = '';
            $this->codpago = '';
            $this->codserie = '';
            $this->desde = '';
            $this->hasta = '';
            $this->num_resultados = '';
            $this->total_resultados = array();
            $this->total_resultados_txt = '';

            if (isset($_POST['delete'])) {
                $this->delete_albaran();
            } else {
                if (!isset($_GET['mostrar']) && ( $this->query != '' || isset($_REQUEST['codagente']) || isset($_REQUEST['codcliente']) || isset($_REQUEST['codserie']))) {
                    /**
                     * si obtenermos un codagente, un codcliente o un codserie pasamos direcatemente
                     * a la pesta??a de b??squeda, a menos que tengamos un mostrar, que
                     * entonces nos indica donde tenemos que estar.
                     */
                    $this->mostrar = 'buscar';
                }

                if (isset($_REQUEST['codcliente']) && $_REQUEST['codcliente'] != '') {
                    $cli0 = new cliente();
                    $this->cliente = $cli0->get($_REQUEST['codcliente']);
                }

                if (isset($_REQUEST['codagente'])) {
                    $this->codagente = $_REQUEST['codagente'];
                }

                if (isset($_REQUEST['codalmacen'])) {
                    $this->codalmacen = $_REQUEST['codalmacen'];
                }

                if (isset($_REQUEST['codgrupo'])) {
                    $this->codgrupo = $_REQUEST['codgrupo'];
                }

                if (isset($_REQUEST['codpago'])) {
                    $this->codpago = $_REQUEST['codpago'];
                }

                if (isset($_REQUEST['codserie'])) {
                    $this->codserie = $_REQUEST['codserie'];
                }

                if (isset($_REQUEST['desde'])) {
                    $this->desde = $_REQUEST['desde'];
                    $this->hasta = $_REQUEST['hasta'];
                }
            }

            /// a??adimos segundo nivel de ordenaci??n
            $order2 = '';
            if ($this->order == 'fecha DESC') {
                $order2 = ', hora DESC';
            } else if ($this->order == 'fecha ASC') {
                $order2 = ', hora ASC';
            }

            if ($this->mostrar == 'pendientes') {
                $this->resultados = $albaran->all_ptefactura($this->offset, $this->order . $order2);

                if ($this->offset == 0) {
                    /// calculamos el total, pero desglosando por divisa
                    $this->total_resultados = array();
                    $this->total_resultados_txt = 'Suma total de esta p??gina:';
                    foreach ($this->resultados as $alb) {
                        if (!isset($this->total_resultados[$alb->coddivisa])) {
                            $this->total_resultados[$alb->coddivisa] = array(
                                'coddivisa' => $alb->coddivisa,
                                'total' => 0
                            );
                        }

                        $this->total_resultados[$alb->coddivisa]['total'] += $alb->total;
                    }
                }
            } else if ($this->mostrar == 'buscar') {
                $this->buscar($order2);
            } else {
                $this->resultados = $albaran->all($this->offset, $this->order . $order2);
            }
        }
    }

    public function url($busqueda = FALSE)
    {
        if ($busqueda) {
            $codcliente = '';
            if ($this->cliente) {
                $codcliente = $this->cliente->codcliente;
            }

            $url = parent::url() . "&mostrar=" . $this->mostrar
                . "&query=" . $this->query
                . "&codagente=" . $this->codagente
                . "&codalmacen=" . $this->codalmacen
                . "&codcliente=" . $codcliente
                . "&codgrupo=" . $this->codgrupo
                . "&codpago=" . $this->codpago
                . "&codserie=" . $this->codserie
                . "&desde=" . $this->desde
                . "&hasta=" . $this->hasta;

            return $url;
        }

        return parent::url();
    }

    public function paginas()
    {
        if ($this->mostrar == 'pendientes') {
            $total = $this->total_pendientes();
        } else if ($this->mostrar == 'buscar') {
            $total = $this->num_resultados;
        } else {
            $total = $this->total_registros();
        }

        return $this->fbase_paginas($this->url(TRUE), $total, $this->offset);
    }

    public function buscar_lineas()
    {
        /// cambiamos la plantilla HTML
        $this->template = 'ajax/ventas_lineas_albaranes';

        $this->buscar_lineas = $_POST['buscar_lineas'];
        $linea = new linea_albaran_cliente();

        if (isset($_POST['codcliente'])) {
            $this->lineas = $linea->search_from_cliente2($_POST['codcliente'], $this->buscar_lineas, $_POST['buscar_lineas_o'], $this->offset);
        } else {
            $this->lineas = $linea->search($this->buscar_lineas, $this->offset);
        }
    }

    private function delete_albaran()
    {
        $alb = new albaran_cliente();
        $alb1 = $alb->get($_POST['delete']);
        if ($alb1) {
            /// ??Actualizamos el stock de los art??culos?
            if (isset($_POST['stock'])) {
                $articulo = new articulo();

                if ($alb1->idfactura) {
                    /// descontamos los art??culos de la factura del albar??n
                    $fac = new factura_cliente();
                    $fac1 = $fac->get($alb1->idfactura);
                    if ($fac1) {
                        foreach ($fac1->get_lineas() as $linea) {
                            /**
                             * Solamente descontamos de la factura las lineas de este albar??n
                             * y las que no pertenezcan a ninguno. Las que pertenecen a otro
                             * no tocamos, porque sigue estando ese otro albar??n.
                             * (las facturas pueden agrupar albaranes).
                             */
                            if ($linea->referencia && ( is_null($linea->idalbaran) || $linea->idalbaran == $alb1->idalbaran)) {
                                $art0 = $articulo->get($linea->referencia);
                                if ($art0) {
                                    $art0->sum_stock($alb1->codalmacen, $linea->cantidad, FALSE, $linea->codcombinacion);
                                }
                            }
                        }
                    }
                } else {
                    /// descontamos todos los art??culos del albar??n
                    foreach ($alb1->get_lineas() as $linea) {
                        if ($linea->referencia) {
                            $art0 = $articulo->get($linea->referencia);
                            if ($art0) {
                                $art0->sum_stock($alb1->codalmacen, $linea->cantidad, FALSE, $linea->codcombinacion);
                            }
                        }
                    }
                }
            }

            if ($alb1->delete()) {
                $this->clean_last_changes();
            } else {
                $this->new_error_msg("??Imposible eliminar el " . FS_ALBARAN . "!");
            }
        } else {
            $this->new_error_msg("??" . FS_ALBARAN . " no encontrado!");
        }
    }

    private function share_extension()
    {
        /// a??adimos las extensiones para clientes, agentes y art??culos
        $extensiones = array(
            array(
                'name' => 'albaranes_cliente',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_cliente',
                'type' => 'button',
                'text' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> &nbsp; ' . ucfirst(FS_ALBARANES),
                'params' => ''
            ),
            array(
                'name' => 'albaranes_agente',
                'page_from' => __CLASS__,
                'page_to' => 'admin_agente',
                'type' => 'button',
                'text' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> &nbsp; ' . ucfirst(FS_ALBARANES) . ' de cliente',
                'params' => ''
            ),
            array(
                'name' => 'albaranes_articulo',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_articulo',
                'type' => 'tab_button',
                'text' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> &nbsp; ' . ucfirst(FS_ALBARANES) . ' de cliente',
                'params' => ''
            ),
        );
        foreach ($extensiones as $ext) {
            $fsext0 = new fs_extension($ext);
            if (!$fsext0->save()) {
                $this->new_error_msg('Imposible guardar los datos de la extensi??n ' . $ext['name'] . '.');
            }
        }
    }

    public function total_pendientes()
    {
        return $this->fbase_sql_total('albaranescli', 'idalbaran', 'WHERE ptefactura = true');
    }

    private function total_registros()
    {
        return $this->fbase_sql_total('albaranescli', 'idalbaran');
    }

    private function buscar($order2)
    {
        $this->resultados = array();
        $this->num_resultados = 0;
        $sql = " FROM albaranescli ";
        $where = 'WHERE ';

        if ($this->query) {
            $query = $this->agente->no_html(mb_strtolower($this->query, 'UTF8'));
            $sql .= $where;
            if (is_numeric($query)) {
                $sql .= "(codigo LIKE '%" . $query . "%' OR numero2 LIKE '%" . $query . "%' OR observaciones LIKE '%" . $query . "%')";
            } else {
                $sql .= "(lower(codigo) LIKE '%" . $query . "%' OR lower(numero2) LIKE '%" . $query . "%' "
                    . "OR lower(observaciones) LIKE '%" . str_replace(' ', '%', $query) . "%')";
            }
            $where = ' AND ';
        }

        if ($this->cliente) {
            $sql .= $where . "codcliente = " . $this->agente->var2str($this->cliente->codcliente);
            $where = ' AND ';
        }

        if ($this->codagente != '') {
            $sql .= $where . "codagente = " . $this->agente->var2str($this->codagente);
            $where = ' AND ';
        }

        if ($this->codalmacen != '') {
            $sql .= $where . "codalmacen = " . $this->agente->var2str($this->codalmacen);
            $where = ' AND ';
        }

        if ($this->codgrupo != '') {
            $sql .= $where . "codcliente IN (SELECT codcliente FROM clientes WHERE codgrupo = " . $this->agente->var2str($this->codgrupo) . ")";
            $where = ' AND ';
        }

        if ($this->codpago != '') {
            $sql .= $where . "codpago = " . $this->agente->var2str($this->codpago);
            $where = ' AND ';
        }

        if ($this->codserie != '') {
            $sql .= $where . "codserie = " . $this->agente->var2str($this->codserie);
            $where = ' AND ';
        }

        if ($this->desde) {
            $sql .= $where . "fecha >= " . $this->agente->var2str($this->desde);
            $where = ' AND ';
        }

        if ($this->hasta) {
            $sql .= $where . "fecha <= " . $this->agente->var2str($this->hasta);
            $where = ' AND ';
        }

        $data = $this->db->select("SELECT COUNT(idalbaran) as total" . $sql);
        if ($data) {
            $this->num_resultados = intval($data[0]['total']);

            $data2 = $this->db->select_limit("SELECT *" . $sql . " ORDER BY " . $this->order . $order2, FS_ITEM_LIMIT, $this->offset);
            if ($data2) {
                foreach ($data2 as $d) {
                    $this->resultados[] = new albaran_cliente($d);
                }
            }

            $data2 = $this->db->select("SELECT coddivisa,SUM(total) as total" . $sql . " GROUP BY coddivisa");
            if ($data2) {
                $this->total_resultados_txt = 'Suma total de los resultados:';

                foreach ($data2 as $d) {
                    $this->total_resultados[] = array(
                        'coddivisa' => $d['coddivisa'],
                        'total' => floatval($d['total'])
                    );
                }
            }
        }
    }

    public function orden()
    {
        return array(
            'fecha_desc' => array(
                'icono' => '<span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>',
                'texto' => 'Fecha',
                'orden' => 'fecha DESC'
            ),
            'fecha_asc' => array(
                'icono' => '<span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span>',
                'texto' => 'Fecha',
                'orden' => 'fecha ASC'
            ),
            'codigo_desc' => array(
                'icono' => '<span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>',
                'texto' => 'C??digo',
                'orden' => 'codigo DESC'
            ),
            'codigo_asc' => array(
                'icono' => '<span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span>',
                'texto' => 'C??digo',
                'orden' => 'codigo ASC'
            ),
            'total_desc' => array(
                'icono' => '<span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>',
                'texto' => 'Total',
                'orden' => 'total DESC'
            )
        );
    }
}
