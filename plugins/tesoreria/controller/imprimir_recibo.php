<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2014-2016  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'plugins/facturacion_base/extras/fs_pdf.php';
require_model('cliente.php');
require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('recibo_cliente.php');
require_model('recibo_proveedor.php');

/**
 * Esta clase agrupa los procedimientos de imprimir/enviar albaranes y recibos.
 */
class imprimir_recibo extends fs_controller
{
   private $cliente;
   private $factura;
   private $logo;
   private $recibo;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'imprimir', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->cliente = FALSE;
      
      $this->logo = FALSE;
      if( file_exists('tmp/'.FS_TMP_NAME.'logo.png') )
      {
         $this->logo = 'tmp/'.FS_TMP_NAME.'logo.png';
      }
      else if( file_exists('tmp/'.FS_TMP_NAME.'logo.jpg') )
      {
         $this->logo = 'tmp/'.FS_TMP_NAME.'logo.jpg';
      }
      
      $this->template = FALSE;
      if( isset($_REQUEST['id']) AND isset($_REQUEST['prov']) )
      {
         $recibo0 = new recibo_proveedor();
         $this->recibo = $recibo0->get($_REQUEST['id']);
         if($this->recibo)
         {
            $fact0 = new factura_proveedor();
            $this->factura = $fact0->get($this->recibo->idfactura);
            
            $this->generar_pdf_recibo_prov();
         }
         else
         {
            echo 'ERROR - Recibo no encontrado.';
         }
      }
      else if( isset($_REQUEST['id']) )
      {
         $recibo0 = new recibo_cliente();
         $this->recibo = $recibo0->get($_REQUEST['id']);
         if($this->recibo)
         {
            $cliente = new cliente();
            $this->cliente = $cliente->get($this->recibo->codcliente);
            
            $fact0 = new factura_cliente();
            $this->factura = $fact0->get($this->recibo->idfactura);
            
            $this->generar_pdf_recibo();
         }
         else
         {
            echo 'ERROR - Recibo no encontrado.';
         }
      }
      else
      {
         $this->share_extensions();
      }
   }
   
   private function share_extensions()
   {
      $extensiones = array(
          array(
              'name' => 'imprimir_recibo',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_recibo',
              'type' => 'pdf',
              'text' => 'Recibo simple',
              'params' => ''
          ),
          array(
              'name' => 'imprimir_recibo_prov',
              'page_from' => __CLASS__,
              'page_to' => 'compras_recibo',
              'type' => 'pdf',
              'text' => 'Recibo simple',
              'params' => '&prov=TRUE'
          ),
      );
      foreach($extensiones as $ext)
      {
         $fsext = new fs_extension($ext);
         if( !$fsext->save() )
         {
            $this->new_error_msg('Error al guardar la extensión '.$ext['name']);
         }
      }
   }
   
   private function generar_pdf_cabecera(&$pdf_doc)
   {
      /// ¿Añadimos el logo?
      if($this->logo)
      {
         if( function_exists('imagecreatefromstring') )
         {
            $pdf_doc->pdf->ezImage($this->logo, 0, 150, 'none');
         }
         else
         {
            die('ERROR: no se encuentra la función imagecreatefromstring(). '
                    . 'Y por tanto no se puede usar el logotipo en los documentos.');
         }
      }
      else
      {
         $pdf_doc->pdf->ezText("<b>".$this->empresa->nombre."</b>", 16, array('justification' => 'center'));
         $pdf_doc->pdf->ezText(FS_CIFNIF.": ".$this->empresa->cifnif, 8, array('justification' => 'center'));
         
         $direccion = $this->empresa->direccion;
         if($this->empresa->codpostal)
         {
            $direccion .= ' - ' . $this->empresa->codpostal;
         }
         
         if($this->empresa->ciudad)
         {
            $direccion .= ' - ' . $this->empresa->ciudad;
         }
         
         if($this->empresa->provincia)
         {
            $direccion .= ' (' . $this->empresa->provincia . ')';
         }
         
         if($this->empresa->telefono)
         {
            $direccion .= ' - Teléfono: ' . $this->empresa->telefono;
         }
         
         $pdf_doc->pdf->ezText($this->fix_html($direccion), 9, array('justification' => 'center'));
      }
   }
   
   private function generar_pdf_recibo()
   {
      /// Creamos el PDF y escribimos sus metadatos
      $pdf_doc = new fs_pdf();
      $pdf_doc->pdf->addInfo('Title', 'Recibo '.$this->recibo->codigo);
      $pdf_doc->pdf->addInfo('Subject', 'Recibo '.$this->recibo->codigo);
      $pdf_doc->pdf->addInfo('Author', $this->empresa->nombre);
      
      $this->generar_pdf_cabecera($pdf_doc);
      
      /*
       * Esta es la tabla con los datos del recibo, factura y cliente:
       */
      $pdf_doc->new_table();
      $pdf_doc->add_table_row(
              array(
                  'campo1' => "<b>Recibo:</b>",
                  'dato1' => $this->recibo->codigo.",\n".$this->recibo->fecha,
                  'campo2' => "<b>Factura:</b>",
                  'dato2' => $this->factura->codigo.",\n".$this->factura->fecha
              )
      );
      $pdf_doc->add_table_row(
              array(
                  'campo1' => "<b>Cliente:</b>",
                  'dato1' => $this->fix_html($this->recibo->nombrecliente),
                  'campo2' => "<b>".FS_CIFNIF.":</b>",
                  'dato2' => $this->recibo->cifnif
              )
      );
      $pdf_doc->add_table_row(
              array(
                  'campo1' => "<b>Dirección:</b>",
                  'dato1' => $this->fix_html(
                          $this->recibo->direccion.' CP: '.$this->recibo->codpostal.' - '.$this->recibo->ciudad.
                          ' ('.$this->recibo->provincia.')'),
                  'campo2' => "<b>Teléfonos:</b>",
                  'dato2' => $this->cliente->telefono1.'  '.$this->cliente->telefono2
              )
      );
      $pdf_doc->add_table_row(
              array(
                  'campo1' => '',
                  'dato1' => '',
                  'campo2' => '',
                  'dato2' => ''
              )
      );
      $pdf_doc->add_table_row(
              array(
                  'campo1' => "<b>Importe recibo:</b>",
                  'dato1' => $this->show_precio($this->recibo->importe, $this->recibo->coddivisa),
                  'campo2' => "<b>Estado:</b>",
                  'dato2' => $this->recibo->estado
              )
      );
      if($this->recibo->estado == 'Pagado')
      {
         $pdf_doc->add_table_row(
                 array(
                     'campo1' => '<b>Forma de pago:</b>',
                     'dato1' => $this->recibo->codpago,
                     'campo2' => "<b>Pago:</b>",
                     'dato2' => $this->recibo->fechap,
                 )
         );
      }
      else
      {
         $pdf_doc->add_table_row(
                 array(
                     'campo1' => '<b>Forma de pago:</b>',
                     'dato1' => $this->recibo->codpago,
                     'campo2' => "<b>Vencimiento:</b>",
                     'dato2' => $this->recibo->fechav,
                 )
         );
      }
      $pdf_doc->save_table(
              array(
                  'cols' => array(
                      'campo1' => array('justification' => 'right'),
                      'dato1' => array('justification' => 'left'),
                      'campo2' => array('justification' => 'right'),
                      'dato2' => array('justification' => 'left')
                  ),
                  'showLines' => 0,
                  'width' => 520,
                  'shaded' => 0
              )
      );
      $pdf_doc->pdf->ezText("\n", 10);
      
      /**
       * Añadimos datos de iban y swift
       */
      if($this->recibo->iban OR $this->recibo->swift)
      {
         $iban = '';
         if($this->recibo->iban)
         {
            $iban = '<b>IBAN:</b> '.$this->recibo->iban(TRUE);
         }
         
         $swift = '';
         if($this->recibo->swift)
         {
            $swift = '<b>SWIFT/BIC:</b> '.$this->recibo->swift;
         }
         
         $pdf_doc->new_table();
         if($this->recibo->iban AND $this->recibo->swift)
         {
            $pdf_doc->add_table_row(
                    array(
                        'campo1' => $iban,
                        'campo2' => $swift
                    )
            );
         }
         else if($this->recibo->iban)
         {
            $pdf_doc->add_table_row(
                    array(
                        'campo1' => $iban
                    )
            );
         }
         else
         {
            $pdf_doc->add_table_row(
                    array(
                        'campo1' => $swift
                    )
            );
         }
         $pdf_doc->save_table(
              array(
                  'cols' => array(
                      'campo1' => array('justification' => 'center'),
                      'campo2' => array('justification' => 'center'),
                  ),
                  'showLines' => 0,
                  'width' => 520,
                  'shaded' => 0
              )
         );
      }
      
      
      /**
       * Añadimos importe, vencimiento...
       */
      $pdf_doc->new_table();
      
      
      /*
       * Añadimos la parte de la firma y las observaciones,
       * para el tipo 'firma'
       */
      $pdf_doc->pdf->ezText("\n", 16);
      $pdf_doc->new_table();
      $pdf_doc->add_table_header(
              array(
                  'campo1' => "<b>Observaciones</b>",
                  'campo2' => "<b>Firma</b>"
              )
      );
      $pdf_doc->add_table_row(
              array(
                  'campo1' => $this->fix_html($this->recibo->observaciones),
                  'campo2' => ''
              )
      );
      $pdf_doc->save_table(
              array(
                  'cols' => array(
                      'campo1' => array('justification' => 'left'),
                      'campo2' => array('justification' => 'right', 'width' => 100)
                  ),
                  'showLines' => 4,
                  'width' => 530,
                  'shaded' => 0
              )
      );
      
      $pdf_doc->show('recibo_'.$this->recibo->codigo.'.pdf');
   }
   
   private function generar_pdf_recibo_prov()
   {
      /// Creamos el PDF y escribimos sus metadatos
      $pdf_doc = new fs_pdf();
      $pdf_doc->pdf->addInfo('Title', 'Recibo compra '.$this->recibo->codigo);
      $pdf_doc->pdf->addInfo('Subject', 'Recibo compra '.$this->recibo->codigo);
      $pdf_doc->pdf->addInfo('Author', $this->empresa->nombre);
      
      $this->generar_pdf_cabecera($pdf_doc);
      
      /*
       * Esta es la tabla con los datos del recibo y factura:
       */
      $pdf_doc->new_table();
      $pdf_doc->add_table_row(
              array(
                  'campo1' => "<b>Recibo:</b>",
                  'dato1' => $this->recibo->codigo.",\n".$this->recibo->fecha,
                  'campo2' => "<b>Factura:</b>",
                  'dato2' => $this->factura->codigo.",\n".$this->factura->fecha
              )
      );
      $pdf_doc->add_table_row(
              array(
                  'campo1' => "<b>Proveedor:</b>",
                  'dato1' => $this->fix_html($this->recibo->nombreproveedor),
                  'campo2' => "<b>".FS_CIFNIF.":</b>",
                  'dato2' => $this->recibo->cifnif
              )
      );
      $pdf_doc->add_table_row(
              array(
                  'campo1' => '',
                  'dato1' => '',
                  'campo2' => '',
                  'dato2' => ''
              )
      );
      $pdf_doc->add_table_row(
              array(
                  'campo1' => "<b>Importe recibo:</b>",
                  'dato1' => $this->show_precio($this->recibo->importe, $this->recibo->coddivisa),
                  'campo2' => "<b>Estado:</b>",
                  'dato2' => $this->recibo->estado
              )
      );
      if($this->recibo->estado == 'Pagado')
      {
         $pdf_doc->add_table_row(
                 array(
                     'campo1' => '<b>Forma de pago</b>',
                     'dato1' => $this->recibo->codpago,
                     'campo2' => "<b>Pago:</b>",
                     'dato2' => $this->recibo->fechap,
                 )
         );
      }
      else
      {
         $pdf_doc->add_table_row(
                 array(
                     'campo1' => '<b>Forma de pago</b>',
                     'dato1' => $this->recibo->codpago,
                     'campo2' => "<b>Vencimiento:</b>",
                     'dato2' => $this->recibo->fechav,
                 )
         );
      }
      $pdf_doc->save_table(
              array(
                  'cols' => array(
                      'campo1' => array('justification' => 'right'),
                      'dato1' => array('justification' => 'left'),
                      'campo2' => array('justification' => 'right'),
                      'dato2' => array('justification' => 'left')
                  ),
                  'showLines' => 0,
                  'width' => 520,
                  'shaded' => 0
              )
      );
      $pdf_doc->pdf->ezText("\n", 10);
      
      /**
       * Añadimos datos de iban y swift
       */
      if($this->recibo->iban OR $this->recibo->swift)
      {
         $iban = '';
         if($this->recibo->iban)
         {
            $iban = '<b>IBAN:</b> '.$this->recibo->iban(TRUE);
         }
         
         $swift = '';
         if($this->recibo->swift)
         {
            $swift = '<b>SWIFT/BIC:</b> '.$this->recibo->swift;
         }
         
         $pdf_doc->new_table();
         if($this->recibo->iban AND $this->recibo->swift)
         {
            $pdf_doc->add_table_row(
                    array(
                        'campo1' => $iban,
                        'campo2' => $swift
                    )
            );
         }
         else if($this->recibo->iban)
         {
            $pdf_doc->add_table_row(
                    array(
                        'campo1' => $iban
                    )
            );
         }
         else
         {
            $pdf_doc->add_table_row(
                    array(
                        'campo1' => $swift
                    )
            );
         }
         $pdf_doc->save_table(
              array(
                  'cols' => array(
                      'campo1' => array('justification' => 'center'),
                      'campo2' => array('justification' => 'center'),
                  ),
                  'showLines' => 0,
                  'width' => 520,
                  'shaded' => 0
              )
         );
      }
      
      /*
       * Añadimos la parte de la firma y las observaciones,
       * para el tipo 'firma'
       */
      $pdf_doc->pdf->ezText("\n", 16);
      $pdf_doc->new_table();
      $pdf_doc->add_table_header(
              array(
                  'campo1' => "<b>Observaciones</b>",
                  'campo2' => "<b></b>"
              )
      );
      $pdf_doc->add_table_row(
              array(
                  'campo1' => $this->fix_html($this->recibo->observaciones),
                  'campo2' => ''
              )
      );
      $pdf_doc->save_table(
              array(
                  'cols' => array(
                      'campo1' => array('justification' => 'left'),
                      'campo2' => array('justification' => 'right', 'width' => 100)
                  ),
                  'showLines' => 4,
                  'width' => 530,
                  'shaded' => 0
              )
      );
      
      $pdf_doc->show('recibo_compra_'.$this->recibo->codigo.'.pdf');
   }
   
   private function fix_html($txt)
   {
      $newt = str_replace('&lt;', '<', $txt);
      $newt = str_replace('&gt;', '>', $newt);
      $newt = str_replace('&quot;', '"', $newt);
      $newt = str_replace('&#39;', "'", $newt);
      return $newt;
   }
}
