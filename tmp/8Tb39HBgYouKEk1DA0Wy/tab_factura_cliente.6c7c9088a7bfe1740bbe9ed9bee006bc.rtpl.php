<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header2") . ( substr("header2",-1,1) != "/" ? "/" : "" ) . basename("header2") );?>


<!--<?php $total=$this->var['total']=0;?>-->

<?php if( $fsc->pagada_previamente ){ ?>

<br/>
<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <div class="alert alert-info">
            Esta factura fué marcada como pagada previamente, por eso no se generan
            los recibos.
         </div>
      </div>
   </div>
   <div>
      <div class="col-sm-12">
         <a href="<?php echo $fsc->url();?>&id=<?php echo $fsc->factura->idfactura;?>&regenerar=TRUE" class="btn btn-sm btn-warning">
            <span class="glyphicon glyphicon-duplicate"></span>&nbsp; Generar recibos igualmente
         </a>
      </div>
   </div>
</div>
<br/>
<?php }else{ ?>

<div class="table-responsive">
   <table class="table table-hover">
      <thead>
         <tr>
            <th width="120">Código</th>
            <th class="text-right" width="120">Importe</th>
            <th>Forma de pago</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Observaciones</th>
         </tr>
      </thead>
      <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <tr class="clickableRow<?php if( $value1->estado=='Pagado' ){ ?> success<?php }elseif( $value1->vencido() ){ ?> danger<?php }else{ ?> warning<?php } ?>" href="<?php echo $value1->url();?>" target="_parent">
         <td><a href="<?php echo $value1->url();?>" target="_parent" class="cancel_clickable"><?php echo $value1->codigo;?></a></td>
         <td class="text-right">
            <?php echo $fsc->show_precio($value1->importe, $value1->coddivisa);?>

            <!--<?php $total=$this->var['total']=$total+$value1->importe;?>-->
         </td>
         <td>
            <?php echo $value1->codpago;?>

            <?php if( $value1->iban ){ ?><span class="label label-default">IBAN</span><?php } ?>

            <?php if( $value1->swift ){ ?><span class="label label-default">SWIFT/BIC</span><?php } ?>

            <?php if( $value1->idremesa ){ ?>

            <span title="Remesa <?php echo $value1->idremesa;?>">
               <i class="fa fa-university" aria-hidden="true"></i>
            </span>
            <?php } ?>

         </td>
         <td><?php echo $value1->fecha;?></td>
         <td>
            <?php if( $value1->estado=='Pagado' ){ ?>

            <span title="Pagado el <?php echo $value1->fechap;?>">
               <span class="glyphicon glyphicon-ok"></span> &nbsp; <?php echo $value1->fechap;?>

            </span>
            <?php }elseif( $value1->estado=='Emitido' ){ ?>

            <span title="Fecha de vencimiento: <?php echo $value1->fechav;?>">
               <span class="glyphicon glyphicon-hourglass"></span>&nbsp; <?php echo $value1->fechav;?>

            </span>
            <?php }else{ ?>

            <?php echo $value1->estado;?> el <?php echo $value1->fechav;?>

            <?php } ?>

         </td>
         <td><?php echo $value1->observaciones_resume();?></td>
      </tr>
      <?php } ?>

      <?php if( abs($fsc->factura->total-$total)>.01 ){ ?>

      <tr>
         <td></td>
         <td class="danger text-right">
            Pendiente:<br/>
            <b><?php echo $fsc->show_precio($fsc->factura->total-$total, $fsc->factura->coddivisa);?></b>
         </td>
         <td colspan="4"></td>
      </tr>
      <?php } ?>

   </table>
</div>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-4">
         <h3>
            <span class="glyphicon glyphicon-question-sign"></span>
            Los recibos se generan automáticamente
         </h3>
         <p class="help-block">
            Los recibos se generan de forma automática a partir de la
            <a href="index.php?page=contabilidad_formas_pago" target="_parent">forma de pago</a>.
            Para cada plazo de pago se genera un recibo.
         </p>
         <a href="<?php echo $fsc->url();?>&id=<?php echo $fsc->factura->idfactura;?>&regenerar=TRUE" class="btn btn-sm btn-warning">
            <span class="glyphicon glyphicon-duplicate"></span>&nbsp; Regenerar
         </a>
      </div>
      <div class="col-sm-4">
         <h3>
            <span class="glyphicon glyphicon-edit"></span>
            Clic para editar
         </h3>
         <p class="help-block">
            También puedes editar manualmente los recibos,
            cambiar la fecha de vencimiento, modificar el estado o el importe.
         </p>
      </div>
      <div class="col-sm-4">
         <?php if( !$fsc->factura->floatcmp($fsc->factura->total,$total) ){ ?>

         <form action="<?php echo $fsc->url();?>" method="post" class="form" target="_parent">
            <input type="hidden" name="idfactura" value="<?php echo $fsc->factura->idfactura;?>"/>
            <div class="panel panel-info">
               <div class="panel-heading">
                  <h3 class="panel-title">
                     <span class="glyphicon glyphicon-plus-sign"></span>
                     Añade recibos
                  </h3>
               </div>
               <div class="panel-body">
                  <div class="form-group">
                     <div class="input-group">
                        <span class="input-group-addon">Importe</span>
                        <input type="number" step="any" name="importe" value="<?php echo round($fsc->factura->total-$total,FS_NF0); ?>" class="form-control" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="input-group">
                        <span class="input-group-addon">Fecha</span>
                        <input type="text" name="fecha" value="<?php echo $fsc->today();?>" class="form-control datepicker" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="input-group">
                        <span class="input-group-addon">Vencimiento</span>
                        <input type="text" name="fechav" value="<?php echo $fsc->vencimiento;?>" class="form-control datepicker" autocomplete="off"/>
                     </div>
                  </div>
               </div>
               <div class="panel-footer">
                  <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();" title="Guardar">
                     <span class="glyphicon glyphicon-floppy-disk"></span>&nbsp; Guardar
                  </button>
               </div>
            </div>
         </form>
         <?php } ?>

      </div>
   </div>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer2") . ( substr("footer2",-1,1) != "/" ? "/" : "" ) . basename("footer2") );?>