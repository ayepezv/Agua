<?php if(!class_exists('raintpl')){exit;}?><form id="f_enable_pages" action="<?php echo $fsc->url();?>" method="post" class="form">
    <input type="hidden" name="modpages" value="TRUE"/>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="text-center" width="40">
                        <input id="marcar_todo_enabled" type="checkbox" name="c_enabled" value=""/>
                    </th>
                    <th class="text-left">Página</th>
                    <th class="text-left">Menú</th>
                    <th class="text-center">Existe</th>
                </tr>
            </thead>
            <?php $loop_var1=$fsc->paginas; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <tr<?php if( !$value1->exists ){ ?> class="danger"<?php } ?>>
                <td class="text-center">
                    <?php if( $value1->enabled ){ ?>

                    <input class="checkbox-inline" type="checkbox" name="enabled[]" value="<?php echo $value1->name;?>" checked=""/>
                    <?php }else{ ?>

                    <input class="checkbox-inline" type="checkbox" name="enabled[]" value="<?php echo $value1->name;?>"/>
                    <?php } ?>

                </td>
                <td>
                    <a target="_blank" href="<?php echo $value1->url();?>"><?php echo $value1->name;?></a>
                </td>
                <td>
                    <?php if( $value1->important ){ ?>

                    <span class="glyphicon glyphicon-star"></span> » <?php echo $value1->title;?>

                    <?php }elseif( $value1->show_on_menu ){ ?>

                    <span class="text-capitalize"><?php echo $value1->folder;?></span> » <?php echo $value1->title;?>

                    <?php }else{ ?>

                    -
                    <?php } ?>

                </td>
                <td class="text-center">
                    <?php if( $value1->exists ){ ?>

                    <span class="glyphicon glyphicon-ok"></span>
                    <?php }else{ ?>

                    <span class="glyphicon glyphicon-exclamation-sign" title="No se encuentra el controlador o pertenece a un plugin inactivo"></span>
                    <?php } ?>

                </td>
            </tr>
            <?php } ?>

        </table>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 text-right">
                <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled = true;this.form.submit();">
                    <span class="glyphicon glyphicon-floppy-disk"></span>
                    <span class="hidden-xs">&nbsp;Guardar</span>
                </button>
            </div>
        </div>
    </div>
</form>