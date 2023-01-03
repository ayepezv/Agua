<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript">
   function clean_cliente()
   {
      document.f_custom_search.ac_cliente.value='';
      document.f_custom_search.codcliente.value='';
      document.f_custom_search.ac_cliente.focus();
   }
   $(document).ready(function() {
      
      $.ajax({
         type: 'GET',
         url: '<?php echo $fsc->url();?>&cronjob=TRUE',
         dataType: 'html',
         success: function() {}
      });
      
      <?php if( $fsc->mostrar=='buscar' ){ ?>

      document.f_custom_search.query.focus();
      <?php } ?>

      
      $("#ac_cliente").autocomplete({
         serviceUrl: '<?php echo $fsc->url();?>',
         paramName: 'buscar_cliente',
         onSelect: function (suggestion) {
            if(suggestion)
            {
               if(document.f_custom_search.codcliente.value != suggestion.data && suggestion.data != '')
               {
                  document.f_custom_search.codcliente.value = suggestion.data;
                  document.f_custom_search.submit();
               }
            }
         }
      });
   });
</script>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-6">
         <div class="btn-group hidden-xs">
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
         <div class="btn-group">
            <a class="btn btn-sm btn-default" href="index.php?page=contabilidad_formas_pago">
               <span class="glyphicon glyphicon-cog"></span>
               <span class="hidden-xs">&nbsp;Formas de pago</span>
            </a>
            <a class="btn btn-sm btn-default" href="index.php?page=remesas">
               <i class="fa fa-university" aria-hidden="true"></i>
               <span class="hidden-xs">&nbsp;Remesas</span>
            </a>
            <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <?php if( $value1->type=='button' ){ ?>

               <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
               <?php } ?>

            <?php } ?>

         </div>
      </div>
      <div class="col-sm-6 text-right">
         <div class="btn-group"><h2 style="margin-top: 0px;">Recibos de venta</h2></div> 
         &nbsp;
         <div class="btn-group">
            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">
               <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
               <li>
                  <a href="<?php echo $fsc->b_url;?>&order=fecha_desc">
                     <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>
                     &nbsp; Fecha &nbsp;
                     <?php if( $fsc->order=='fecha DESC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->b_url;?>&order=fecha_asc">
                     <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span>
                     &nbsp; Fecha &nbsp;
                     <?php if( $fsc->order=='fecha ASC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->b_url;?>&order=fechav_desc">
                     <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>
                     &nbsp; Vencimiento &nbsp;
                     <?php if( $fsc->order=='fechav DESC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->b_url;?>&order=fechav_asc">
                     <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span>
                     &nbsp; Vencimiento &nbsp;
                     <?php if( $fsc->order=='fechav ASC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->b_url;?>&order=codigo_desc">
                     <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>
                     &nbsp; Código &nbsp;
                     <?php if( $fsc->order=='codigo DESC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->b_url;?>&order=codigo_asc">
                     <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span>
                     &nbsp; Código &nbsp;
                     <?php if( $fsc->order=='codigo ASC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
               <li>
                  <a href="<?php echo $fsc->b_url;?>&order=importe_desc">
                     <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span>
                     &nbsp; Importe &nbsp;
                     <?php if( $fsc->order=='importe DESC' ){ ?><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php } ?>

                  </a>
               </li>
            </ul>
         </div>
      </div>
   </div>
</div>

<ul class="nav nav-tabs">
   <li role="presentation"<?php if( $fsc->mostrar=='todo' ){ ?> class="active"<?php } ?>>
      <a href="<?php echo $fsc->url();?>&mostrar=todo">Recibos (Todo)</a>
   </li>
   <?php if( $fsc->num_pendientes > 0 ){ ?>

   <li role="presentation"<?php if( $fsc->mostrar=='pendientes' ){ ?> class="active"<?php } ?>>
      <a href="<?php echo $fsc->url();?>&mostrar=pendientes">
         <span class="glyphicon glyphicon-time"></span>
         <span class="hidden-xs">&nbsp;Pendientes</span>
         <span class="hidden-xs badge"><?php echo $fsc->num_pendientes;?></span>
      </a>
   </li>
   <?php } ?>

   <?php if( $fsc->num_vencidos > 0 ){ ?>

   <li role="presentation"<?php if( $fsc->mostrar=='vencidos' ){ ?> class="active"<?php } ?>>
      <a href="<?php echo $fsc->url();?>&mostrar=vencidos" title="Vencidos">
         <span class="glyphicon glyphicon-exclamation-sign"></span>
         <span class="hidden-xs hidden-sm">&nbsp;Vencidos</span>
         <span class="badge"><?php echo $fsc->num_vencidos;?></span>
      </a>
   </li>
   <?php } ?>

   <li role="presentation"<?php if( $fsc->mostrar=='buscar' ){ ?> class="active"<?php } ?>>
      <a href="<?php echo $fsc->url();?>&mostrar=buscar" title="Buscar">
         <span class="glyphicon glyphicon-search"></span>
         <?php if( $fsc->mostrar=='buscar' ){ ?>

         <span class="badge"><?php echo $fsc->num_resultados;?></span>
         <?php } ?>

      </a>
   </li>
</ul>

<?php if( $fsc->mostrar=='buscar' ){ ?>

<br/>
<form name="f_custom_search" action="<?php echo $fsc->url();?>&mostrar=buscar" method="post" class="form">
   <?php if( $fsc->cliente ){ ?>

   <input type="hidden" name="codcliente" value="<?php echo $fsc->cliente->codcliente;?>"/>
   <?php }else{ ?>

   <input type="hidden" name="codcliente"/>
   <?php } ?>

   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <input class="form-control" type="text" name="query" value="<?php echo $fsc->query;?>" autocomplete="off" placeholder="Buscar">
                  <span class="input-group-btn">
                     <button class="btn btn-primary hidden-sm" type="submit">
                        <span class="glyphicon glyphicon-search"></span>
                     </button>
                  </span>
               </div>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <?php if( $fsc->cliente ){ ?>

                  <input class="form-control" type="text" name="ac_cliente" value="<?php echo $fsc->cliente->nombre;?>" id="ac_cliente" placeholder="Cualquier cliente" autocomplete="off"/>
                  <?php }else{ ?>

                  <input class="form-control" type="text" name="ac_cliente" id="ac_cliente" placeholder="Cualquier cliente" autocomplete="off"/>
                  <?php } ?>

                  <span class="input-group-btn">
                     <button class="btn btn-default" type="button" onclick="clean_cliente()">
                        <span class="glyphicon glyphicon-remove"></span>
                     </button>
                  </span>
               </div>
            </div>
         </div>
         <div class="col-sm-2">
            <select name="estado" class="form-control" onchange="this.form.submit()">
               <option value="">Cualquier estado</option>
               <option value="">---</option>
               <option value="Emitido"<?php if( $fsc->estado=='Emitido' ){ ?> selected=""<?php } ?>>Emitido</option>
               <option value="Pagado"<?php if( $fsc->estado=='Pagado' ){ ?> selected=""<?php } ?>>Pagado</option>
               <option value="Vencido"<?php if( $fsc->estado=='Vencido' ){ ?> selected=""<?php } ?>>Vencido</option>
            </select>
         </div>
         <div class="col-sm-2">
            <select name="codpago" class="form-control" onchange="this.form.submit()">
               <option value="">Cualquier forma de pago</option>
               <option value="">---</option>
               <?php $loop_var1=$fsc->forma_pago->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->codpago==$fsc->codpago ){ ?>

                  <option value="<?php echo $value1->codpago;?>" selected=""><?php echo $value1->descripcion;?></option>
                  <?php }else{ ?>

                  <option value="<?php echo $value1->codpago;?>"><?php echo $value1->descripcion;?></option>
                  <?php } ?>

               <?php } ?>

            </select>
         </div>
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
         <div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                  <input type="text" name="hasta" value="<?php echo $fsc->hasta;?>" class="form-control datepicker" placeholder="Hasta" autocomplete="off" onchange="this.form.submit()"/>
               </div>
               <label>
                  <?php if( $fsc->check_vencimiento ){ ?>

                  <input type="checkbox" name="vencimiento" value="TRUE" checked="" onchange="this.form.submit()"/>
                  <?php }else{ ?>

                  <input type="checkbox" name="vencimiento" value="TRUE" onchange="this.form.submit()"/>
                  <?php } ?>

                  comprobar vencimiento
               </label>
            </div>
         </div>
      </div>
   </div>
</form>
<?php } ?>


<div class="table-responsive">
   <table class="table table-hover">
      <thead>
         <tr>
            <th>Código + Cliente</th>
            <th>Observaciones</th>
            <th class="text-right">Importe</th>
            <th>Forma pago</th>
            <th class="text-right">Fecha</th>
            <th class="text-right">Estado</th>
         </tr>
      </thead>
      <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <tr class="clickableRow<?php if( $value1->estado=='Pagado' ){ ?> success<?php }elseif( $value1->vencido() ){ ?> danger<?php } ?>" href="<?php echo $value1->url();?>">
         <td>
            <a href="<?php echo $value1->url();?>"><?php echo $value1->codigo;?></a> <?php echo $value1->nombrecliente;?>

            <?php if( $value1->codcliente ){ ?>

            <a href="<?php echo $fsc->url();?>&codcliente=<?php echo $value1->codcliente;?>" class="cancel_clickable" title="Ver más recibos de <?php echo $value1->nombrecliente;?>">[+]</a>
            <?php }else{ ?>

            <span class="label label-danger" title="Cliente desconocido">???</span>
            <?php } ?>

         </td>
         <td><?php echo $value1->observaciones_resume();?></td>
         <td class="text-right" title="<?php echo $fsc->show_precio($fsc->euro_convert($value1->importeeuros, $value1->coddivisa, $value1->tasaconv));?>">
            <?php echo $fsc->show_precio($value1->importe, $value1->coddivisa);?>

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
         <td class="text-right"><?php echo $value1->fecha;?></td>
         <td class="text-right">
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
      </tr>
      <?php }else{ ?>

      <tr class="warning">
         <td colspan="6">Sin resultados.</td>
      </tr>
      <?php } ?>

      <?php if( $fsc->total_resultados!=='' ){ ?>

      <tr>
         <td colspan="3" class="text-right">
            <?php echo $fsc->total_resultados_txt;?> &nbsp; <b><?php echo $fsc->show_precio($fsc->total_resultados);?></b>
         </td>
         <td colspan="3"></td>
      </tr>
      <?php } ?>

   </table>
</div>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12 text-center">
         <ul class="pagination">
            <?php $loop_var1=$fsc->paginas(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <li<?php if( $value1['actual'] ){ ?> class="active"<?php } ?>>
               <a href="<?php echo $value1['url'];?>"><?php echo $value1['num'];?></a>
            </li>
            <?php } ?>

         </ul>
      </div>
   </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>