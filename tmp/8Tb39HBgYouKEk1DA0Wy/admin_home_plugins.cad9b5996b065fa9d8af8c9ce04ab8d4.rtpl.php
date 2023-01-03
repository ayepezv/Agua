<?php if(!class_exists('raintpl')){exit;}?><div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="text-left">Plugin</th>
                <th class="text-left">Descripción</th>
                <th class="text-right">Versión</th>
                <th class="text-right">
                    <span class="glyphicon glyphicon-flash" aria-hidden="true" title="Prioridad"></span>
                </th>
            </tr>
        </thead>
        <?php $loop_var1=$fsc->plugin_manager->installed(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

        <!--<?php $tr_class=$this->var['tr_class']='';?>-->
        <?php if( !$value1["compatible"] ){ ?><!--<?php echo $tr_class=' class="danger"';?>--><?php }elseif( $value1["enabled"] ){ ?><!--<?php echo $tr_class=' class="success"';?>--><?php } ?>

        <tr<?php echo $tr_class;?>>
            <td><?php echo $value1["name"];?></td>
            <td>
                <p><?php echo $value1["description"];?></p>
                <?php if( $value1["wizard"] !='' && $value1["enabled"] ){ ?>

                <a href="index.php?page=<?php echo $value1["wizard"];?>" class="btn btn-xs btn-default">
                    <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>&nbsp; Configurar
                </a>
                <?php } ?>

                <a href="https://www.facturascripts.com/plugin/<?php echo $value1["name"];?>" target="_blank" class="btn btn-xs btn-default">
                    <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp; más información
                </a>
                <?php if( $value1["enabled"] ){ ?>

                <a class="btn btn-xs btn-warning" href="<?php echo $fsc->url();?>&disable=<?php echo $value1["name"];?>#plugins">
                    <span class="glyphicon glyphicon-remove"></span> Desactivar
                </a>
                <?php }else{ ?>

                <div class="btn-group">
                    <?php if( !$fsc->plugin_manager->disable_rm_plugins ){ ?>

                    <a class="btn btn-xs btn-danger" onclick="eliminar('<?php echo $value1["name"];?>')" title="eliminar plugin">
                        <span class="glyphicon glyphicon-trash"></span>
                    </a>
                    <?php } ?>

                    <?php if( $value1["compatible"] ){ ?>

                    <a class="btn btn-xs btn-primary" href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&enable=<?php echo $value1["name"];?>#plugins">
                        <span class="glyphicon glyphicon-ok"></span>&nbsp; Activar
                    </a>
                    <?php }else{ ?>

                    <a class="btn btn-xs btn-warning" href="#" onclick="bootbox.alert({message: '<?php echo $value1["error_msg"];?>', title: '<b>Atención</b>'});">
                        <span class="glyphicon glyphicon-remove"></span>&nbsp; Incompatible
                    </a>
                    <?php } ?>

                </div>
                <?php } ?>

            </td>
            <td class="text-right">
                <a href="<?php  echo FS_COMMUNITY_URL;?>/index.php?page=community_changelog&plugin=<?php echo $value1["name"];?>&version=<?php echo $value1["version"];?>" target="_blank">
                    <?php echo $value1["version"];?>

                </a>
            </td>
            <td class="text-right"><?php echo $value1["prioridad"];?></td>
        </tr>
        <?php }else{ ?>

        <tr class="warning">
            <td colspan="5">
                No tienes plugins instalados.
                <?php if( !$fsc->plugin_manager->disable_mod_plugins ){ ?>

                Mira en la pestaña <b>Descargas</b>.
                <?php } ?>

            </td>
        </tr>
        <?php } ?>

    </table>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <?php if( !$fsc->plugin_manager->disable_add_plugins ){ ?>

            <a href="#" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal_add_plugin">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                <span class="hidden-xs">&nbsp;Añadir</span>
            </a>
            <?php } ?>

        </div>
    </div>
</div>