<?php


// require_once ("include/variables.php");
 $rucem = '1790263061001';





$xml = new DomDocument('1.0', 'UTF-8');
//		$xml->standalone         = false;
		$xml->preserveWhiteSpace = false;

		$Factura = $xml->createElement('factura');
		$cabecerav=$xml->createAttribute('version');
		$cabecerav->value='1.0.0';
		$cabecera = $xml->createAttribute('id');
		$cabecera->value='comprobante';
		$Factura->appendChild($cabecerav);
		$Factura->appendChild($cabecera);
		$Factura = $xml->appendChild($Factura);
		
		

// INFORMACION TRIBUTARIA.
	$infoTributaria = $xml->createElement('infoTributaria');
	$infoTributaria = $Factura->appendChild($infoTributaria);
	$cbc = $xml->createElement('ambiente','1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('tipoEmision', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('razonSocial', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('nombreComercial', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('ruc', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('claveAcceso', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('codDoc', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('estab', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('ptoEmi', '001');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('secuencial', '001');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('dirMatriz', 'direccion matris');
	$cbc = $infoTributaria->appendChild($cbc);

// INFORMACIOO DE FACTURA.
	$infoFactura = $xml->createElement('infoFactura');
	$infoFactura = $Factura->appendChild($infoFactura);
	$cbc = $xml->createElement('fechaEmision','07/10/2022');
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('dirEstablecimiento', '1');
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('contribuyenteEspecial', '000');
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('obligadoContabilidad', '1');
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('tipoIdentificacionComprador', '1');
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('razonSocialComprador', '1');
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('identificacionComprador', '1');
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('direccionComprador', '1');
	$cbc = $infoFactura->appendChild($cbc);	
	$cbc = $xml->createElement('totalSinImpuestos', '1');
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('totalSubsidio', 0);
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('totalDescuento', '001');
	$cbc = $infoFactura->appendChild($cbc);


	$totalConImpuestos = $xml->createElement('totalConImpuestos');
	$totalConImpuestos = $infoFactura->appendChild($totalConImpuestos);
	$totalImpuesto = $xml->createElement('totalImpuesto');
	$totalImpuesto = $totalConImpuestos->appendChild($totalImpuesto);
	$cbc = $xml->createElement('codigo', '001');
	$cbc = $totalImpuesto->appendChild($cbc);
	$cbc = $xml->createElement('codigoPorcentaje', 0);
	$cbc = $totalImpuesto->appendChild($cbc);
	/*$cbc = $xml->createElement('descuentoAdicional', '001');
	$cbc = $totalImpuesto->appendChild($cbc);*/
	$cbc = $xml->createElement('baseImponible', '001');
	$cbc = $totalImpuesto->appendChild($cbc);
	$cbc = $xml->createElement('valor', '001');
	$cbc = $totalImpuesto->appendChild($cbc);

	$cbc = $xml->createElement('propina', 0.0);
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('importeTotal', '1');
	$cbc = $infoFactura->appendChild($cbc);
	$cbc = $xml->createElement('moneda', 'DOLAR');
	$cbc = $infoFactura->appendChild($cbc);
	
		
	$pagos = $xml->createElement('pagos');
	$pagos = $infoFactura->appendChild($pagos);	
	$pago = $xml->createElement('pago');
	$pago = $pagos->appendChild($pago);
	$cbc = $xml->createElement('formaPago', '01');
	$cbc = $pago->appendChild($cbc);
	$cbc = $xml->createElement('total', 'misma base imponible');
	$cbc = $pago->appendChild($cbc);
	$cbc = $xml->createElement('plazo', 0);
	$cbc = $pago->appendChild($cbc);
	$cbc = $xml->createElement('unidadTiempo', 'dias');
	$cbc = $pago->appendChild($cbc);
	

//DETALLES DE LA FACTURA.
	$detalles = $xml->createElement('detalles');
	$detalles = $Factura->appendChild($detalles);

$descripcion = '';
$i = 0;

// EMULANDO LA CONSULTA A LA BASE DE DATOS DE UN SELECT
$lineas = array( 

"1" =>array
   (
   "descripcion"=>"descripcion del producto 1",
   "precioUnitario"=>"200",
   "cantidad"=>"21"
   ),
"2" =>array
   (
   "descripcion"=>"descricon del producto 2",
   "precioUnitario"=>"100",
   "cantidad"=>"12"
   )

);


foreach ($lineas as $d) {
$i++;
$numerolinea = $i;

	$detalle = $xml->createElement('detalle');
	$detalle = $detalles->appendChild($detalle);
	$cbc = $xml->createElement('codigoPrincipal', '1');
	$cbc = $detalle->appendChild($cbc);
	$cbc = $xml->createElement('codigoAuxiliar', '1');
	$cbc = $detalle->appendChild($cbc);
	$cbc = $xml->createElement('descripcion', $d["descripcion"].' / '.$numerolinea );
	$cbc = $detalle->appendChild($cbc);
	$cbc = $xml->createElement('cantidad', $d["cantidad"]);
	$cbc = $detalle->appendChild($cbc);
	$cbc = $xml->createElement('precioUnitario', $d["precioUnitario"]);
	$cbc = $detalle->appendChild($cbc);
	$cbc = $xml->createElement('descuento', '0.00');
	$cbc = $detalle->appendChild($cbc);
	$cbc = $xml->createElement('precioTotalSinImpuesto', '1');
	$cbc = $detalle->appendChild($cbc);

	$impuestos = $xml->createElement('impuestos');
	$impuestos = $detalle->appendChild($impuestos);
	$impuesto = $xml->createElement('impuesto');
	$impuesto = $impuestos->appendChild($impuesto);
	$cbc = $xml->createElement('codigo', 2);
	$cbc = $impuesto->appendChild($cbc);
	$cbc = $xml->createElement('codigoPorcentaje', 0);
	$cbc = $impuesto->appendChild($cbc);
	$cbc = $xml->createElement('tarifa', 0);
	$cbc = $impuesto->appendChild($cbc);
	$cbc = $xml->createElement('baseImponible', 'igual al preciotatal sin impuesto');
	$cbc = $impuesto->appendChild($cbc);
	$cbc = $xml->createElement('valor', '001');
	$cbc = $impuesto->appendChild($cbc);
}

$infoAdicional = $xml->createElement('infoAdicional');


$xml_cp1 = $xml->createElement('campoAdicional','tumail@ns.com');

$atributo = $xml->createAttribute('nombre');
$atributo->value='email';
$xml_cp1->appendChild($atributo);

$xml_cp2 = $xml->createElement('campoAdicional','Paco Pepe');
$atributo2 = $xml->createAttribute('nombre');
$atributo2->value='Recaudador';
$xml_cp2->appendChild($atributo2);

$xml_cp3 = $xml->createElement('campoAdicional','Cdla. La Primavera');
$atributo3 = $xml->createAttribute('nombre');
$atributo3->value='Domicilio';
$xml_cp3->appendChild($atributo3);

$xml_cp4 = $xml->createElement('campoAdicional','Agua Variable - ENERO-20; FEBRERO-20; MARZO-20; ');
$atributo3 = $xml->createAttribute('nombre');
$atributo3->value='Concepto';
$xml_cp4->appendChild($atributo3);

$xml_cp5 = $xml->createElement('campoAdicional','Cuenta:436 Lec Act: 0 - Lec Ant: 0 = Consumo: 0 m3');
$atributo3 = $xml->createAttribute('nombre');
$atributo3->value='Cuenta';
$xml_cp5->appendChild($atributo3);


$infoAdicional->appendChild($xml_cp3);
$infoAdicional->appendChild($xml_cp1);
$infoAdicional->appendChild($xml_cp4);
$infoAdicional->appendChild($xml_cp5);
$infoAdicional->appendChild($xml_cp2);


$Factura->appendChild($infoAdicional);




/*

$Factura = $xml->createElement('factura');
		$cabecerav=$xml->createAttribute('version');
		$cabecerav->value='1.0.0';
		$cabecera = $xml->createAttribute('id');
		$cabecera->value='comprobante';
		$Factura->appendChild($cabecerav);
		$Factura->appendChild($cabecera);
		$Factura = $xml->appendChild($Factura);



$infoTributaria = $xml->createElement('infoTributaria');
	$infoTributaria = $Factura->appendChild($infoTributaria);
	
	$cbc = $xml->createElement('ambiente','1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('tipoEmision', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('razonSocial', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('nombreComercial', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('ruc', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('claveAcceso', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('codDoc', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('estab', '1');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('ptoEmi', '001');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('secuencial', '001');
	$cbc = $infoTributaria->appendChild($cbc);
	$cbc = $xml->createElement('dirMatriz', 'direccion matris');
	$cbc = $infoTributaria->appendChild($cbc);
	*/


$xml->formatOutput = true;
$strings_xml       = $xml->saveXML();

//****mi código */
$ruta='factura-sin-firmar/';
if (!file_exists($ruta)) {
	mkdir($ruta, 0777, true);
}
//****mi código */
$xml->save('comprobantes/factura-sin-firmar/'.$rucem.'74902020320953jrsa.xml');
chmod('comprobantes/factura-sin-firmar/'.$rucem.'74902020320953jrsa.xml', 0777);
echo '<span style="color: #015B01; font-size: 15pt;">XML de Factura creada:</span>&nbsp;';
echo '<span style="color: #B21919; font-size: 15pt;">'.$rucem.'74902020320953.xml</span><br>';
echo '<hr width="100%"></div>';
    echo '<a href="firmarxml.php" target="blank">Firmar XML</a>';