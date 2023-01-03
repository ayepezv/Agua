<?php if(!class_exists('raintpl')){exit;}?><form class="form" action="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>#avanzado" method="post">
    <div class="container-fluid" style="margin-top: 10px;">
        <div class="row">
            <div class="col-md-3 col-sm-4">
                <div class="form-group">
                    Zona horaria:
                    <select class="form-control" name="zona_horaria">
                        <?php $loop_var1=$fsc->settings->get_timezone_list(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( $value1['zone']==$GLOBALS['config2']['zona_horaria'] ){ ?>

                        <option value="<?php echo $value1['zone'];?>" selected=""><?php echo $value1['diff_from_GMT'];?> - <?php echo $value1['zone'];?></option>
                        <?php }else{ ?>

                        <option value="<?php echo $value1['zone'];?>"><?php echo $value1['diff_from_GMT'];?> - <?php echo $value1['zone'];?></option>
                        <?php } ?>

                        <?php } ?>

                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Portada:
                    <select name="homepage" class="form-control">
                        <?php $loop_var1=$fsc->paginas; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( $value1->name==$GLOBALS['config2']['homepage'] ){ ?>

                        <option value="<?php echo $value1->name;?>" selected=""><?php echo $value1->name;?></option>
                        <?php }else{ ?>

                        <option value="<?php echo $value1->name;?>"><?php echo $value1->name;?></option>
                        <?php } ?>

                        <?php } ?>

                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Decimales de los totales:
                    <select name="nf0" class="form-control">
                        <?php $loop_var1=$fsc->settings->nf0(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( $value1==$GLOBALS['config2']['nf0'] ){ ?>

                        <option value="<?php echo $value1;?>" selected=""><?php echo $value1;?></option>
                        <?php }else{ ?>

                        <option value="<?php echo $value1;?>"><?php echo $value1;?></option>
                        <?php } ?>

                        <?php } ?>

                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Decimales de los precios:
                    <select name="nf0_art" class="form-control">
                        <?php $loop_var1=$fsc->settings->nf0(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( $value1==$GLOBALS['config2']['nf0_art'] ){ ?>

                        <option value="<?php echo $value1;?>" selected=""><?php echo $value1;?></option>
                        <?php }else{ ?>

                        <option value="<?php echo $value1;?>"><?php echo $value1;?></option>
                        <?php } ?>

                        <?php } ?>

                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Separador para los Decimales:
                    <select name="nf1" class="form-control">
                        <?php $loop_var1=$fsc->settings->nf1(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( $key1==$GLOBALS['config2']['nf1'] ){ ?>

                        <option value="<?php echo $key1;?>" selected=""><?php echo $value1;?></option>
                        <?php }else{ ?>

                        <option value="<?php echo $key1;?>"><?php echo $value1;?></option>
                        <?php } ?>

                        <?php } ?>

                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Separador para los Millares:
                    <select name="nf2" class="form-control">
                        <option value="">(Ninguno)</option>
                        <?php $loop_var1=$fsc->settings->nf1(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( $key1==$GLOBALS['config2']['nf2'] ){ ?>

                        <option value="<?php echo $key1;?>" selected=""><?php echo $value1;?></option>
                        <?php }else{ ?>

                        <option value="<?php echo $key1;?>"><?php echo $value1;?></option>
                        <?php } ?>

                        <?php } ?>

                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Símbolo Divisa:
                    <select name="pos_divisa" class="form-control">
                        <?php if( $GLOBALS['config2']['pos_divisa']=='right' ){ ?>

                        <option value="right" selected="">123 <?php echo $fsc->simbolo_divisa();?></option>
                        <option value="left"><?php echo $fsc->simbolo_divisa();?>123</option>
                        <?php }else{ ?>

                        <option value="right">123 <?php echo $fsc->simbolo_divisa();?></option>
                        <option value="left" selected=""><?php echo $fsc->simbolo_divisa();?>123</option>
                        <?php } ?>

                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <p class="help-block">
                    <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                    La configuración de decimales y separadores de decimales y millares
                    se aplica únicamente a los listados y formatos de impresión. Para los
                    campos editables se utiliza la configuración del sistema operativo.
                </p>
            </div>
        </div>
        <div class="row bg-success">
            <div class="col-md-12 col-sm-12">
                <h2>
                    <i class="fa fa-language"></i>&nbsp; Traducciones:
                </h2>
                <p class="help-block">
                    FACTURA y FACTURAS se traducen únicamente en los documentos de ventas.
                    FACTURA_SIMPLIFICADA se utiliza en los tickets.
                </p>
            </div>
        </div>
        <div class="row bg-success">
            <?php $loop_var1=$fsc->settings->traducciones(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <div class="col-md-2 col-sm-3">
                <div class="form-group">
                    <span class="text-uppercase"><?php echo $value1['nombre'];?>:</span>
                    <input class="form-control" type="text" name="<?php echo $value1['nombre'];?>" value="<?php echo $value1['valor'];?>" autocomplete="off"/>
                </div>
            </div>
            <?php } ?>

        </div>
        <div class="row bg-warning">
            <div class="col-md-12 col-sm-12">
                <h2>
                    <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
                    &nbsp; Desarrollo:
                </h2>
                <p class='help-block'>
                    Estos son parametros de configuración sensibles de FacturaScripts. No
                    los modifiques si no sabes lo que haces.
                </p>
            </div>
        </div>
        <div class="row bg-warning">
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Comprobaciones en la base de datos:
                    <select name="check_db_types" class="form-control">
                        <?php if( $GLOBALS['config2']['check_db_types']==1 ){ ?>

                        <option value="1" selected=''>Comprobar los tipos de las columnas de las tablas</option>
                        <option value="0">No comprobar los tipos</option>
                        <?php }else{ ?>

                        <option value="1">Comprobar los tipos de las columnas de las tablas</option>
                        <option value="0" selected=''>No comprobar los tipos</option>
                        <?php } ?>

                    </select>
                    <p class="help-block">
                        Tendrás que <a href="index.php?page=admin_info">limpiar la caché</a>
                        para que comiencen las comprobaciones.
                    </p>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Tipo entero:
                    <input class="form-control" type="text" name="db_integer" value="<?php echo $GLOBALS['config2']['db_integer'];?>"/>
                    <p class="help-block">Tipo a usar en la base de datos (MySQL).</p>
                </div>
            </div>
            <div class="col-md-2 col-sm-2">
                <!--<?php $form_class=$this->var['form_class']='';?>-->
                <?php if( $GLOBALS['config2']['foreign_keys']==0 ){ ?><!--<?php $form_class=$this->var['form_class']=' has-warning';?>--><?php } ?>

                <div class="form-group<?php echo $form_class;?>">
                    Comprobar claves ajenas:
                    <select name="foreign_keys" class="form-control">
                        <?php if( $GLOBALS['config2']['foreign_keys']==1 ){ ?>

                        <option value="1" selected=''>Si</option>
                        <option value="0">No</option>
                        <?php }else{ ?>

                        <option value="1">Si</option>
                        <option value="0" selected=''>No</option>
                        <?php } ?>

                    </select>
                    <p class="help-block">
                        <?php if( strtolower(FS_DB_TYPE)=='mysql' ){ ?>

                        Peligro: no tocar si no sabes lo que haces.
                        <?php }else{ ?>

                        Sólo se puede desactivar en MySQL.
                        <?php } ?>

                    </p>
                </div>
            </div>
            <div class="col-md-4 col-sm-4">
                <div class="form-group">
                    Permitir acceso desde estas IPs:
                    <input class="form-control" type="text" name="ip_whitelist" value="<?php echo $GLOBALS['config2']['ip_whitelist'];?>"/>
                    <p class="help-block">Los admninistradores pueden acceder desde cualquier IP.</p>
                </div>
            </div>
        </div>
        <div class="row bg-warning">
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Generar los libros contables:
                    <select name="libros_contables" class="form-control">
                        <?php if( $GLOBALS['config2']['libros_contables']==1 ){ ?>

                        <option value="1" selected=''>Si</option>
                        <option value="0">No</option>
                        <?php }else{ ?>

                        <option value="1">Si</option>
                        <option value="0" selected=''>No</option>
                        <?php } ?>

                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="form-group">
                    Algoritmo de nuevo código:
                    <select name="new_codigo" class="form-control">
                        <?php $loop_var1=$fsc->settings->new_codigo_options(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( $GLOBALS['config2']['new_codigo']==$key1 ){ ?>

                        <option value="<?php echo $key1;?>" selected=''><?php echo $value1;?></option>
                        <?php }else{ ?>

                        <option value="<?php echo $key1;?>"><?php echo $value1;?></option>
                        <?php } ?>

                        <?php } ?>

                    </select>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 20px;">
            <div class="col-md-6 col-sm-6">
                <button class="btn btn-sm btn-danger" type="button" onclick="window.location.href = '<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&reset=TRUE#avanzado'">
                    <span class="glyphicon glyphicon-edit"></span>&nbsp; Configuración por defecto
                </button>
            </div>
            <div class="col-md-6 col-sm-6 text-right">
                <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled = true;this.form.submit();">
                    <span class="glyphicon glyphicon-floppy-disk"></span>&nbsp; Guardar
                </button>
            </div>
        </div>
    </div>
</form>