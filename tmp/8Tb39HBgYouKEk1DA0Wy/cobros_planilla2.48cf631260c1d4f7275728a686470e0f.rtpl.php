<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>

<h2>Seleccionar las fechas del reporte</h2>
<a class="btn btn-sm btn-default" onclick="window.print();">
                    <span class="glyphicon glyphicon-print" aria-hidden="true"></span>
                </a>
<a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>" title="Recargar la pÃ¡gina">
                        <span class="glyphicon glyphicon-refresh"></span>
                    </a>
<form name="f_custom_search" action="<?php echo $fsc->url();?>" method="post" class="form">
<div class="col-sm-1">Desde: </div>
<div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                  <input type="text" name="desde" value="<?php echo $fsc->desde;?>" class="form-control datepicker" placeholder="Desde" autocomplete="off" onchange="this.form.submit()"/>
               </div>
            </div>
         </div>
<div class="col-sm-1">Hasta: </div>
         <div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                  <input type="text" name="hasta" value="<?php echo $fsc->hasta;?>" class="form-control datepicker" placeholder="Hasta" autocomplete="off" onchange="this.form.submit()"/>
               </div>
            </div>
         </div>
</form>
<br><br><br><h3>PAGOS SERVICIOS DE AGUA</h3>
<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center">Ejercicio</th>
            <th class="text-center">ID</th>
            <th class="text-center">Asiento</th>
            <th class="text-left">Fecha</th>
            <th class="text-left">Propietario Factura</th>
            <th class="text-center">Valor Cobrado</th>
            <!--<th class="text-center">Factura</th> -->
        </tr>
    </thead>
    
    <?php $loop_var1=$fsc->cobros; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>
    <tr>
        <td class="text-center"><?php echo $value1['codejercicio'];?></td>
        <td class="text-center"><?php echo $value1['idasiento'];?></td>
        <td class="text-center"><?php echo $value1['numero'];?></td>
        <td><?php echo $value1['fecha'];?></td>
        <td><?php echo $value1['concepto'];?></td>
        <td type="float" class="money" >$ <?php echo $value1['importe'];?></td>
        <!--<td type="url" class="text-center"><a target="_new" href="<?php echo $value1['url'];?>">IMPRIMIR FACTURA</a></td> -->
        
    </tr>
    <?php } ?>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><h4><b>Total Planilla de Agua</b></h4></td>
        <td><h4><b>$ <?php echo $fsc->sumar();?></b></h4></td>
    </tr>
</table>

<br><br><br><h3>PAGOS PUNTOS DE AGUA</h3>
<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center">Ejercicio</th>
            <th class="text-center">ID</th>
            <th class="text-center">Asiento</th>
            <th class="text-left">Fecha</th>
            <th class="text-left">Propietario Factura</th>
            <th class="text-center">Valor Cobrado</th>
            <!--<th class="text-center">Factura</th> -->
        </tr>
    </thead>
    
    <?php $loop_var1=$fsc->cobrosPuntosAgua; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>
    <tr>
        <td class="text-center"><?php echo $value1['codejercicio'];?></td>
        <td class="text-center"><?php echo $value1['idasiento'];?></td>
        <td class="text-center"><?php echo $value1['numero'];?></td>
        <td><?php echo $value1['fecha'];?></td>
        <td><?php echo $value1['concepto'];?></td>
        <td type="float" class="money" >$ <?php echo $value1['importe'];?></td>
        <!--<td type="url" class="text-center"><a target="_new" href="<?php echo $value1['url'];?>">IMPRIMIR FACTURA</a></td> -->
        
    </tr>
    <?php } ?>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><h4><b>Total Puntos de Agua</b></h4></td>
        <td><h4><b>$ <?php echo $fsc->sumarPuntosAgua();?></b></h4></td>
    </tr>
</table>

<br><br><br><h3>PAGOS PUNTOS DE ALCANTARILLADO</h3>
<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center">Ejercicio</th>
            <th class="text-center">ID</th>
            <th class="text-center">Asiento</th>
            <th class="text-left">Fecha</th>
            <th class="text-left">Propietario Factura</th>
            <th class="text-center">Valor Cobrado</th>
            <!--<th class="text-center">Factura</th> -->
        </tr>
    </thead>
    
    <?php $loop_var1=$fsc->cobrosPuntosAlcantarillado; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>
    <tr>
        <td class="text-center"><?php echo $value1['codejercicio'];?></td>
        <td class="text-center"><?php echo $value1['idasiento'];?></td>
        <td class="text-center"><?php echo $value1['numero'];?></td>
        <td><?php echo $value1['fecha'];?></td>
        <td><?php echo $value1['concepto'];?></td>
        <td type="float" class="money" >$ <?php echo $value1['importe'];?></td>
        <!--<td type="url" class="text-center"><a target="_new" href="<?php echo $value1['url'];?>">IMPRIMIR FACTURA</a></td> -->
        
    </tr>
    <?php } ?>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><h4><b>Total Puntos Alcantarillado</b></h4></td>
        <td><h4><b>$ <?php echo $fsc->sumarPuntosAlcantarillado();?></b></h4></td>
    </tr>
</table>

<table class="table table-hover">
   <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td><h3><b>Total Recaudacion General</b></h3></td>
      <td><h3><b>$ <?php echo $fsc->sumarTodo();?></b></h3></td>
  </tr>   
</table>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>