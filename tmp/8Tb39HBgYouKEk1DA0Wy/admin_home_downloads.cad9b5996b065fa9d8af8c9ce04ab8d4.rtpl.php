<?php if(!class_exists('raintpl')){exit;}?><div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th></th>
                <th class="text-left">Plugin</th>
                <th class="text-left">Descripción</th>
            </tr>
        </thead>
        <?php $loop_var1=$fsc->plugin_manager->downloads(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

        <!--<?php $tr_class=$this->var['tr_class']='';?>-->
        <?php if( $value1["instalado"] ){ ?>

        <!--<?php echo $tr_class=' class="warning"';?>-->
        <?php }elseif( !$value1["estable"] ){ ?>

        <!--<?php echo $tr_class=' class="danger"';?>-->
        <?php }elseif( $value1["tipo"]=='gratis' ){ ?>

        <!--<?php echo $tr_class=' class="success"';?>-->
        <?php } ?>

        <tr<?php echo $tr_class;?>>
            <td class="text-center">
                <?php if( $value1["imagen"] ){ ?>

                <a href="https://www.facturascripts.com/plugin/<?php echo $value1["nombre"];?>" target="_blank" class="thumbnail">
                    <img src="<?php echo $value1["imagen"];?>" alt="<?php echo $value1["nombre"];?>" width="80"/>
                </a>
                <?php }else{ ?>

                <a href="https://www.facturascripts.com/plugin/<?php echo $value1["nombre"];?>" target="_blank" class="btn btn-block btn-default">
                    <i class="fa fa-plug fa-3x" aria-hidden="true"></i>
                </a>
                <?php } ?>

            </td>
            <td>
                <?php echo $value1["nombre"];?> &nbsp; v<?php echo $value1["version"];?>

                <br/><br/>
                <?php if( $value1["instalado"] ){ ?>

                <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>#plugins" class="btn btn-xs btn-default">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>&nbsp; Instalado
                </a>
                <?php }elseif( !$value1["zip_link"] ){ ?>

                <a href="https://www.facturascripts.com/plugin/<?php echo $value1["nombre"];?>" target="_blank" class="btn btn-xs btn-info">
                    <span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span>&nbsp; Comprar
                </a>
                <?php }elseif( $value1["estable"] ){ ?>

                <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&download=<?php echo $value1["id"];?>#plugins" class="btn btn-xs btn-primary">
                    <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>&nbsp; Descargar
                </a>
                <?php }else{ ?>

                <a href="#" class="btn btn-xs btn-primary" onclick="descargar_plugin_inestable('<?php echo $value1["id"];?>')">
                    <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>&nbsp; Descargar
                </a>
                <?php } ?>

            </td>
            <td>
                <?php echo nl2br($value1["descripcion"]); ?><br/>
                Última modificación: <?php echo $value1["ultima_modificacion"];?>

                <?php if( !$value1["estable"] ){ ?>

                <span class="label label-danger" title="inestable: no lo instales a menos que sepas lo que haces">inestable</span>
                <?php } ?>

            </td>
        </tr>
        <?php } ?>

        <tr>
            <td colspan="3">
                <a href="https://www.facturascripts.com/plugins" target="_blank" class="btn btn-block btn-info">
                    <i class="fa fa-plug"></i>&nbsp; Más plugins...
                </a>
            </td>
        </tr>
    </table>
</div>