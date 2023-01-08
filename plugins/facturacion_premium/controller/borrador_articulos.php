<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('articulo.php');
require_model('fabricante.php');
require_model('familia.php');

/**
 * Description of borrador_articulos
 *
 * @author Carlos García Gómez
 */
class borrador_articulos extends fs_controller
{
   public $codfabricante;
   public $codfamilia;
   public $fabricante;
   public $familia;
   public $resultados;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Borrador de artículos', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->share_extensions();
      $this->fabricante = new fabricante();
      $this->familia = new familia();
      
      $this->codfabricante = '';
      if( isset($_POST['codfabricante']) )
      {
         $this->codfabricante = $_POST['codfabricante'];
      }
      
      $this->codfamilia = '';
      if( isset($_POST['codfamilia']) )
      {
         $this->codfamilia = $_POST['codfamilia'];
      }
      
      $articulo = new articulo();
      if( isset($_POST['referencias']) )
      {
         $num = 0;
         foreach($_POST['referencias'] as $ref)
         {
            if( $this->borrar_articulo($ref) )
            {
               $num++;
            }
         }
         
         $this->new_message($num.' artículos borrados.');
      }
      
      $this->resultados = $articulo->search($this->query, 0, $this->codfamilia, FALSE, $this->codfabricante);
   }
   
   private function borrar_articulo($ref)
   {
      $art = new articulo();
      $articulo = $art->get($ref);
      if($articulo)
      {
         if( $articulo->delete() )
         {
            return TRUE;
         }
         else
         {
            $this->new_error_msg('Error al borrar el artículo '.$ref);
            return FALSE;
         }
      }
      else
         return FALSE;
   }
   
   private function share_extensions()
   {
      $extension = array(
          'name' => 'borrador_articulos',
          'page_from' => __CLASS__,
          'page_to' => 'ventas_articulos',
          'type' => 'button',
          'text' => '<span class="glyphicon glyphicon-erase" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Borrar...</span>',
          'params' => ''
      );
      $fsext = new fs_extension($extension);
      $fsext->save();
   }
}
