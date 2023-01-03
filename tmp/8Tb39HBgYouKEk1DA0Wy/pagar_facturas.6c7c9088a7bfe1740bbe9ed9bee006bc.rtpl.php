<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript">
   function fs_marcar_todo()
   {
      $("#f_agrupar_cli input[name='idfactura[]']").prop('checked', true);
   }
   function fs_marcar_nada()
   {
      $("#f_agrupar_cli input[name='idfactura[]']").prop('checked', false);
   }
   $(document).ready(function() {
      $("#ac_cliente").autocomplete({
         serviceUrl: '<?php echo $fsc->url();?>',
         paramName: 'buscar_cliente',
         onSelect: function (suggestion) {
            if(suggestion)
            {
               if(document.f_pagar_facturas.codcliente.value != suggestion.data)
               {
                  document.f_pagar_facturas.codcliente.value = suggestion.data;
                  $("#todos_cli").prop('checked', false);
               }
            }
         }
      });
   });
</script>

<form name="f_pagar_facturas" class="form" action="<?php echo $fsc->url();?>" method="post">
   <input type="hidden" name="codcliente" value="<?php echo $fsc->codcliente;?>"/>
   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-12">
            <div class="btn-group">
               <a href="index.php?page=ventas_facturas" class="btn btn-sm btn-default">
                  <span class="glyphicon glyphicon-arrow-left"></span>
                  <span class="hidden-xs">&nbsp; Todo</span>
               </a>
               <a href="<?php echo $fsc->url();?>" class="btn btn-sm btn-default" title="recargar la página">
                  <span class="glyphicon glyphicon-refresh"></span>
               </a>
            </div>
            <div class="btn-group">
            <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <?php if( $value1->type=='button' ){ ?>

               <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
               <?php } ?>

            <?php } ?>

            </div>
            <div class="page-header">
               <h1>
                  <span class="glyphicon glyphicon-check"></span> Pagar facturas de ventas
                  <span class="badge"><?php echo count($fsc->resultados); ?></span>
               </h1>
               <p class="help-block">
                  Aplica los filtros que necesites, marca/desmarca las facturas que
                  quieras y pulsa el botón <b>marcar como pagadas</b> para marcarlas
                  como pagadas. Si tienes activada la contabilidad integrada se generarán
                  los asientos contables.
               </p>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-2">
            <div class="form-group">
               Desde: <!--<?php echo $fsc->desde;?> -->
               <input class="form-control datepicker" type="text" name="desde" value="01-01-2010" autocomplete="off" onchange="this.form.submit()"/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Hasta:
               <input class="form-control datepicker" type="text" name="hasta" value="<?php echo $fsc->hasta;?>" autocomplete="off" onchange="this.form.submit()"/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Serie:
               <select name="codserie" class="form-control" onchange="this.form.submit()">
               <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->codserie==$fsc->codserie ){ ?>

                  <option value="<?php echo $value1->codserie;?>" selected=""><?php echo $value1->descripcion;?></option>
                  <?php }else{ ?>

                  <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
                  <?php } ?>

               <?php } ?>

               </select>
            </div>
         </div>
         <!--<div class="col-sm-1">
            <div class="form-group">
               Cuenta:
               <input id="cuenta" class="form-control" type="text" name="cuenta" placeholder="# cuenta..." autocomplete="off"/>
            </div>
         </div> -->
         <div class="col-sm-4">
            <div class="form-group">
               Clientes:
               <?php if( $fsc->cliente ){ ?>

               <input id="ac_cliente" class="form-control" type="text" name="ac_cliente" placeholder="<?php echo $fsc->cliente->nombre;?>" autocomplete="off"/>
               <?php }else{ ?>

               <input id="ac_cliente" class="form-control" type="text" name="ac_cliente" placeholder="buscar..." autocomplete="off"/>
               <?php } ?>

               <label class="checkbox-inline">
                  <?php if( $fsc->codcliente ){ ?>

                  <input id="todos_cli" type="checkbox" name="todos" value="TRUE" onchange="this.form.submit()"/>
                  <?php }else{ ?>

                  <input id="todos_cli" type="checkbox" name="todos" value="TRUE" checked=""/>
                  <?php } ?>

                  Todos los clientes
               </label>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="hidden-xs">
               <br/>
            </div>
            <?php if( $fsc->resultados ){ ?>

            <button class="btn btn-sm btn-default" type="submit" onclick="this.disabled=true;this.form.submit();">
               <span class="glyphicon glyphicon-search"></span>&nbsp; Buscar
            </button>
            <?php }else{ ?>

            <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
               <span class="glyphicon glyphicon-search"></span>&nbsp; Buscar
            </button>
            <?php } ?>

         </div>
      </div>
   </div>
</form>

<div class="visible-xs">
   <br/><br/>
</div>

<?php if( $fsc->resultados ){ ?>

<form id="f_agrupar_cli" class="form" name="f_agrupar_cli" action="<?php echo $fsc->url();?>" method="post">
   <input type="hidden" name="codcliente" value="<?php echo $fsc->codcliente;?>"/>
   <?php if( !$fsc->codcliente ){ ?>

   <input type="hidden" name="todos" value="TRUE"/>
   <?php } ?>

   <input type="hidden" name="desde" value="<?php echo $fsc->desde;?>"/>
   <input type="hidden" name="hasta" value="<?php echo $fsc->hasta;?>"/>
   <input type="hidden" name="codserie" value="<?php echo $fsc->codserie;?>"/>
   <div class="container-fluid">
      <div class="row">
         <div class="col-xs-6">
            <div class="btn-group">
               <button class="btn btn-sm btn-default" type="button" onclick="fs_marcar_todo()" title="Marcar todo">
                  <span class="glyphicon glyphicon-check"></span>
               </button>
               <button class="btn btn-sm btn-default" type="button" onclick="fs_marcar_nada()" title="Desmarcar todo">
                  <span class="glyphicon glyphicon-unchecked"></span>
               </button>
            </div>
         </div>
         <div class="col-xs-6 text-right">
            <button class="btn btn-sm btn-primary" type="button" data-toggle="modal" data-target="#modal_opciones_pago">
               <span class="glyphicon glyphicon-ok"></span>
               <span class="hidden-xs">&nbsp; Pagar</span>
            </button>
         </div>
      </div>
      <div class="row">
         <div class="col-xs-12">
            <div class="table-responsive">
               <!--<?php $total=$this->var['total']=0;?>-->
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th></th>
                        <th class="text-left">Código + Número 2</th>
                        <th class="text-left">Cliente</th>
                        <th class="text-left">Observaciones</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Fecha</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr class="<?php if( $value1->anulada ){ ?>danger<?php }elseif( $value1->total<=0 ){ ?>warning<?php } ?>">
                     <td class="text-center">
                        <?php if( $value1->pagada ){ ?>

                        <span class="glyphicon glyphicon-ok" title="La factura está pagada"></span>
                        <?php }else{ ?>

                        <input type="checkbox" name="idfactura[]" value="<?php echo $value1->idfactura;?>" checked="checked"/>
                        <!--<?php echo $total+=$value1->totaleuros;?>-->
                        <?php } ?>

                     </td>
                     <td>
                        <a href="<?php echo $value1->url();?>"><?php echo $value1->codigo;?></a> <?php echo $value1->numero2;?>

                        <?php if( $value1->anulada ){ ?>

                        <span class="glyphicon glyphicon-remove" title="La <?php  echo FS_FACTURA;?> está anulada"></span>
                        <?php } ?>

                        <?php if( $value1->idfacturarect ){ ?>

                        <span class="glyphicon glyphicon-flag" title="<?php  echo FS_FACTURA_RECTIFICATIVA;?> de <?php echo $value1->codigorect;?>"></span>
                        <?php } ?>

                     </td>
                     <td>
                        <?php echo $value1->nombrecliente;?>

                        <?php if( $value1->codcliente ){ ?>

                        <a href="<?php echo $fsc->url();?>&codcliente=<?php echo $value1->codcliente;?>&desde=<?php echo $fsc->desde;?>&hasta=<?php echo $fsc->hasta;?>&codserie=<?php echo $fsc->codserie;?>" class="cancel_clickable" title="Ver más facturas de <?php echo $value1->nombrecliente;?>">[+]</a>
                        <?php }else{ ?>

                        <span class="label label-danger" title="Cliente desconocido">???</span>
                        <?php } ?>

                     </td>
                     <td><?php echo $value1->observaciones_resume();?></td>
                     <td class="text-right" title="<?php echo $fsc->show_precio($fsc->euro_convert($value1->totaleuros, $value1->coddivisa, $value1->tasaconv));?>">
                        <?php echo $fsc->show_precio($value1->total, $value1->coddivisa);?>

                     </td>
                     <td class="text-right" title="Hora <?php echo $value1->hora;?>">
                        <?php if( $value1->fecha==$fsc->today() ){ ?><b><?php echo $value1->fecha;?></b><?php }else{ ?><?php echo $value1->fecha;?><?php } ?>

                     </td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td></td>
                     <td colspan="5">Ninguna factura encontrada. Pulsa <b>Nueva</b> para crear una.</td>
                  </tr>
                  <?php } ?>

                  <tr>
                     <td colspan="4"></td>
                     <td class="text-right"><b><?php echo $fsc->show_precio( $fsc->euro_convert($total) );?></b></td>
                     <td></td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-xs-6">
            <div class="btn-group">
               <button class="btn btn-sm btn-default" type="button" onclick="fs_marcar_todo()" title="Marcar todo">
                  <span class="glyphicon glyphicon-check"></span>
               </button>
               <button class="btn btn-sm btn-default" type="button" onclick="fs_marcar_nada()" title="Desmarcar todo">
                  <span class="glyphicon glyphicon-unchecked"></span>
               </button>
            </div>
         </div>
         <div class="col-xs-6 text-right">
            <button class="btn btn-sm btn-primary" type="button" data-toggle="modal" data-target="#modal_opciones_pago">
               <span class="glyphicon glyphicon-ok"></span>
               <span class="hidden-xs">&nbsp; Pagar</span>
            </button>
         </div>
      </div>
   </div>
   <div class="modal" id="modal_opciones_pago" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-sm" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
               <h4 class="modal-title">Pagar en...</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  <select name="codsubcuenta" class="form-control">
                     <?php $loop_var1=$fsc->get_subcuentas_pago(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( $value1->codsubcuenta==$fsc->codsubcuenta_pago ){ ?>

                        <option value="<?php echo $value1->codsubcuenta;?>" selected=""><?php echo $value1->descripcion;?></option>
                        <?php }else{ ?>

                        <option value="<?php echo $value1->codsubcuenta;?>"><?php echo $value1->descripcion;?></option>
                        <?php } ?>

                     <?php } ?>

                     <option value="">------</option>
                     <?php $loop_var1=$fsc->cuenta_banco->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <?php if( !$value1->codsubcuenta ){ ?>

                        <?php }elseif( $value1->codsubcuenta==$fsc->codsubcuenta_pago ){ ?>

                        <option value="<?php echo $value1->codsubcuenta;?>" selected=""><?php echo $value1->descripcion;?></option>
                        <?php }else{ ?>

                        <option value="<?php echo $value1->codsubcuenta;?>"><?php echo $value1->descripcion;?></option>
                        <?php } ?>

                     <?php } ?>

                  </select>
               </div>
               <div class="form-group">
                  <div class="input-group">
                     <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                     <input type="text" name="fecha" value="<?php echo $fsc->fecha_pago;?>" class="form-control datepicker" autocomplete="off"/>
                  </div>
               </div>
               <?php if( $fsc->cliente ){ ?>

               <p class="help-block">
                  <span class="glyphicon glyphicon-info-sign"></span>
                  Se generará un único asiento de pago.
               </p>
               <?php } ?>

            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-sm btn-primary" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-ok"></span>&nbsp; Pagar
               </button>
            </div>
         </div>
      </div>
   </div>
</form>
<?php }else{ ?>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <div class="alert alert-info">
            Sin resultados. Prueba ajustando las fechas.
         </div>
      </div>
   </div>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>