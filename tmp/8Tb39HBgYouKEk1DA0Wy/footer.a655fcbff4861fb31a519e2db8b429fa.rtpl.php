<?php if(!class_exists('raintpl')){exit;}?>   <?php if( FS_DEMO ){ ?>

   <br/>
   <div class="container-fluid hidden-print bg-success">
      <div class="row">
         <div class="col-sm-12">
            <div class="page-header">
               <h3>
                  <i class="fa fa-question-circle" aria-hidden="true"></i>
                  ¿Te ha convencido?
               </h3>
               <p class="help-block">
                  Esta demo sirve para tener una idea rápida de cómo funciona FacturaScripts,
                  con un número reducido de plugins activos y muchas opciones desactivadas.
                  Ten en cuenta que hay docenas de personas usando esta demo a la vez.
               </p>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-3">
            <b>Plugins instalados:</b>
            <ul>
               <?php $loop_var1=$GLOBALS['plugins']; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <li>
                  <a href='https://www.facturascripts.com/plugin/<?php echo $value1;?>' target='_blank'><?php echo $value1;?></a>
               </li>
               <?php } ?>

            </ul>
         </div>
         <div class="col-sm-6">
            <b>Ventajas:</b>
            <ul>
               <li>Trabaja desde cualquier PC, tablet o smartphone.</li>
               <li>Formularios sencillos con las opciones imprescindibles.</li>
               <li>Software libre con actualizaciones constantes.</li>
               <li>Más de 50 plugins con un desarrollo muy activo.</li>
               <li>Soporte profesional a tu disposición a través de los Partners.</li>
            </ul>
         </div>
         <div class="col-sm-3">
            <a href='https://www.facturascripts.com/descargar?ref=<?php echo base64_encode($fsc->user->email); ?>' target="_blank" class="btn btn-lg btn-block btn-primary">
               <i class="fa fa-download" aria-hidden="true"></i>&nbsp; Descargar
            </a>
            <i class="fa fa-cloud" aria-hidden="true"></i>
            <i class="fa fa-windows" aria-hidden="true"></i>
            <i class="fa fa-linux" aria-hidden="true"></i>
            <i class="fa fa-apple" aria-hidden="true"></i>
            Disponible para todas las plataformas.
         </div>
      </div>
      <div class="row">
         <div class="col-sm-12">
            <br/><br/><br/>
         </div>
      </div>
   </div>
   <?php }else{ ?>

   <hr style="margin-top: 50px;" class="hidden-print"/>
   
   <div class="container-fluid hidden-print" style="margin-bottom: 10px;">
      <div class="row">
         <div class="col-sm-12">
            <?php if( FS_DB_HISTORY ){ ?>

               <div class="panel panel-default hidden-print">
                  <div class="panel-heading">
                     <h3 class="panel-title">Consultas SQL:</h3>
                  </div>
                  <div class="panel-body">
                     <ol style="font-size: 11px; margin: 0px; padding: 0px 0px 0px 20px;">
                        <?php $loop_var1=$fsc->get_db_history(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?><li><?php echo $value1;?></li><?php } ?>

                     </ol>
                  </div>
               </div>
               <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->type=='hidden_iframe' ){ ?>

                  <iframe src="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" width="100%"></iframe>
                  <?php } ?>

               <?php } ?>

            <?php }else{ ?>

               <div class="hidden">
               <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->type=='hidden_iframe' ){ ?>

                  <iframe src="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>"></iframe>
                  <?php } ?>

               <?php } ?>

               </div>
            <?php } ?>

         </div>
      </div>
      <div class="row">
         <div class="col-sm-4 col-xs-6">
            <small>
               Creado con <a target="_blank" href="https://www.facturascripts.com">FacturaScripts</a>.
            </small>
         </div>
         <div class="col-sm-4 hidden-xs text-center">
            <span class="label label-default">Consultas: <?php echo $fsc->selects();?></span>
            <span class="label label-default">Transacciones: <?php echo $fsc->transactions();?></span>
         </div>
         <div class="col-sm-4 col-xs-6 text-right">
            <span class="label label-default">
               <i class="fa fa-clock-o" aria-hidden="true" title="Página procesada en <?php echo $fsc->duration();?>"></i>
               &nbsp;<?php echo $fsc->duration();?>

            </span>
         </div>
      </div>
   </div>
   <?php } ?>

</body>
</html>