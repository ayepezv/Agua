<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript">
    function fs_marcar_todo() {
        $('#f_enable_pages input:checkbox').prop('checked', true);
    }
    function eliminar(name) {
        bootbox.confirm({
            message: '¿Realmente desea eliminar este plugin?',
            title: '<b>Atención</b>',
            callback: function (result) {
                if (result) {
                    window.location.href = '<?php echo $fsc->url();?>&delete_plugin=' + name + '#plugins';
                }
            }
        });
    }
    function descargar_plugin_inestable(id) {
        bootbox.confirm({
            message: 'Este plugin está marcado como inestable. Significa que <b>no se recomienda su uso</b>,\n\
 que todavía está en desarrollo y puede contener errores. ¿Estas seguro de que quieres descargarlo?',
            title: '<b>Atención</b>',
            callback: function (result) {
                if (result) {
                    window.location.href = '<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&download=' + id + '#plugins';
                }
            }
        });
    }
    $(document).ready(function () {
        <?php if( $fsc->step=='1' ){ ?>

        $('#tab_panel_control a[href="#t_descargas"]').tab('show');
        <?php } ?>


        if (window.location.hash.substring(1) == 'paginas') {
            $('#tab_panel_control a[href="#t_paginas"]').tab('show');
        } else if (window.location.hash.substring(1) == 'plugins') {
            $('#tab_panel_control a[href="#t_plugins"]').tab('show');
        } else if (window.location.hash.substring(1) == 'descargas') {
            $('#tab_panel_control a[href="#t_descargas"]').tab('show');
        } else if (window.location.hash.substring(1) == 'avanzado') {
            $('#tab_panel_control a[href="#t_avanzado"]').tab('show');
        }

        $('#marcar_todo_enabled').click(function () {
            var checked = $(this).prop('checked');
            $("#f_enable_pages input[name='enabled[]']").prop('checked', checked);
        });
    });
</script>

<?php if( !$fsc->step ){ ?>

<div class="well">
    <div class="page-header" style="margin-top: 0px;">
        <h1>
            Bienvenido a FacturaScripts
            <small><?php echo $fsc->version();?></small>
        </h1>
    </div>
    <p class="help-block">
        Ya tienes instalado el núcleo. Como ves, el núcleo permite gestionar páginas,
        plugins y usuarios. Pulsa continuar para seguir con la instalación.
    </p>
    <a href="#" class="btn btn-sm btn-primary" onclick="fs_marcar_todo();f_enable_pages.submit();">
        <span class="glyphicon glyphicon-ok"></span>&nbsp; Continuar
    </a>
</div>
<?php }elseif( $fsc->step=='1' ){ ?>

<div class="well">
    <div class="page-header" style="margin-top: 0px;">
        <h1>
            <i class="fa fa-plug"></i> Plugins
        </h1>
    </div>
    <p class="help-block">
        El núcleo solamente se encarga de la gestión de usuarios, plugins y páginas.
        Para todo lo demás tienes los plugins.
        En la pestaña <b>descargas</b> tienes disponibles los principales plugins.
        Elige los que necesites. Hay de todo y la lista se actualiza periódicamente.
        <br/>
        Los plugins instalados los tienes en la pestaña <b>plugins</b>. Puedes añadir
        plugins manualmente, si lo deseas, y también puedes activar o desactivar,
        incluso eliminarlos.
        <br/>
        Además, toda la facturación y contabilidad básica ha sido movida al plugin
        <b>facturacion_base</b>. Puedes descargarlo automáticamente e instalarlo pulsando
        este botón.
    </p>
    <?php if( file_exists('plugins/facturacion_base') ){ ?>

    <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&enable=facturacion_base#plugins" class="btn btn-sm btn-primary">
        <span class="glyphicon glyphicon-ok"></span>&nbsp; Activar facturacion_base
    </a>
    <?php }else{ ?>

    <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&download=87#plugins" class="btn btn-sm btn-primary">
        <span class="glyphicon glyphicon-download-alt"></span>&nbsp; Descargar facturacion_base
    </a>
    <?php } ?>

    <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&skip=TRUE#descargas" class="btn btn-sm btn-warning">
        <span class="glyphicon glyphicon-remove"></span>&nbsp; No, gracias
    </a>
</div>
<?php }else{ ?>

<div class="container-fluid" style="margin-top: 10px;">
    <div class="row">
        <div class="col-xs-6">
            <div class="btn-group">
                <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>" title="Recargar la página">
                    <span class="glyphicon glyphicon-refresh"></span>
                </a>
                <?php if( $fsc->page->is_default() ){ ?>

                <a class="btn btn-sm btn-default active" href="<?php echo $fsc->url();?>&amp;default_page=FALSE" title="Marcada como página de inicio (pulsa de nuevo para desmarcar)">
                    <i class="fa fa-bookmark" aria-hidden="true"></i>
                </a>
                <?php }else{ ?>

                <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>&amp;default_page=TRUE" title="Marcar como página de inicio">
                    <i class="fa fa-bookmark-o" aria-hidden="true"></i>
                </a>
                <?php } ?>

            </div>
            <?php if( !$fsc->plugin_manager->disable_mod_plugins ){ ?>

            <div class="btn-group">
                <a href="#" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal_add_plugin">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                    <span class="hidden-xs">&nbsp;Añadir</span>
                </a>
                <a class='btn btn-sm <?php if( $fsc->check_for_updates() ){ ?>btn-info<?php }else{ ?>btn-default<?php } ?>' href='updater.php' title='Actualizador'>
                    <span class="glyphicon glyphicon-upload"></span>
                    <span class="hidden-xs">&nbsp;Actualizador</span>
                </a>
            </div>
            <?php } ?>

            <div class="btn-group">
                <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                <?php if( $value1->type=='button' ){ ?>

                <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
                <?php } ?>

                <?php } ?>

            </div>
        </div>
        <div class="col-xs-6 text-right">
            <h2 style="margin-top: 0px;">Panel de control</h2>
        </div>
    </div>
</div>
<?php } ?>


<div id="tab_panel_control" role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="#t_paginas" aria-controls="t_paginas" role="tab" data-toggle="tab">
                <i class="fa fa-check-square"></i>
                <span class="hidden-xs">&nbsp;Menú</span>
            </a>
        </li>
        <li role="presentation">
            <a href="#t_plugins" aria-controls="t_plugins" role="tab" data-toggle="tab">
                <i class="fa fa-plug"></i>
                <span class="hidden-xs">&nbsp;Plugins</span>
            </a>
        </li>
        <?php if( !$fsc->plugin_manager->disable_mod_plugins ){ ?>

        <li role="presentation">
            <a href="#t_descargas" aria-controls="t_descargas" role="tab" data-toggle="tab">
                <span class="glyphicon glyphicon-download-alt"></span>
                <span class="hidden-xs">&nbsp;Descargas</span>
            </a>
        </li>
        <?php } ?>

        <li role="presentation">
            <a href="#t_avanzado" aria-controls="t_avanzado" role="tab" data-toggle="tab">
                <span class="glyphicon glyphicon-wrench"></span>
                <span class="hidden-xs">&nbsp;Avanzado</span>
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="t_paginas">
            <?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("tab/admin_home_pages") . ( substr("tab/admin_home_pages",-1,1) != "/" ? "/" : "" ) . basename("tab/admin_home_pages") );?>

        </div>
        <div role="tabpanel" class="tab-pane" id="t_plugins">
            <?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("tab/admin_home_plugins") . ( substr("tab/admin_home_plugins",-1,1) != "/" ? "/" : "" ) . basename("tab/admin_home_plugins") );?>

        </div>
        <div role="tabpanel" class="tab-pane" id="t_descargas">
            <?php if( !$fsc->plugin_manager->disable_mod_plugins ){ ?>

                <?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("tab/admin_home_downloads") . ( substr("tab/admin_home_downloads",-1,1) != "/" ? "/" : "" ) . basename("tab/admin_home_downloads") );?>

            <?php } ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="t_avanzado">
            <?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("tab/admin_home_advanced") . ( substr("tab/admin_home_advanced",-1,1) != "/" ? "/" : "" ) . basename("tab/admin_home_advanced") );?>

        </div>
    </div>
</div>

<div class="modal fade" id="modal_add_plugin" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-plug"></i>&nbsp; Añadir un plugin
                </h4>
                <p class="help-block">
                    Si tienes un plugin en un archivo .zip, puedes subirlo e instalarlo desde aquí.
                </p>
            </div>
            <div class="modal-body">
                <form class="form" action="<?php echo $fsc->url();?>#plugins" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="install" value="TRUE"/>
                    <div class="form-group">
                        <input type="file" name="fplugin" accept="application/zip"/>
                    </div>
                    <p class="help-block">
                        Este servidor admite un tamaño máximo de <?php echo $fsc->get_max_file_upload();?> MB.
                        Si el plugin ocupa más, dará error al subirlo, incluso puede que no salga
                        nada.
                    </p>
                    <button type="submit" class="btn btn-primary" onclick="this.disabled=true;this.form.submit();">
                        <span class="glyphicon glyphicon-import" aria-hidden="true"></span>&nbsp; Añadir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>