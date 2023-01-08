<?php
$razonsocial='Empresa Pública Empresa Municipal agua potable y Alcantarillado de Riobamba';
$nomComercial='EP-EMAPAR';
$ruc='0660836910001';
$xml = new DomDocument('1.0','utf-8');
$xml->formatOutPut=true;

$xml_fac = $xml->createElement('factura');
$cabecerav=$xml->createAttribute('version');
$cabecerav->value='1.0.0';
$cabecera = $xml->createAttribute('id');
$cabecera->value='comprobante';

$xml_inf = $xml->createElement('infoTributaria');
$xml_amb = $xml->createElement('ambiente','1');
$xml_tip = $xml->createElement('tipoEmision','1');
$xml_raz = $xml->createElement('razonSocial',$razonsocial);
$xml_nom = $xml->createElement('nombreComercial',$nomComercial);
$xml_ruc = $xml->createElement('ruc',$ruc);

$xml_cla = $xml->createElement('claveAcceso','1212121212121212121212121212121212121212121212121');
$xml_doc = $xml->createElement('codDoc','01');
$xml_est = $xml->createElement('estab','001');
$xml_emi = $xml->createElement('ptoEmi','001');
$xml_sec = $xml->createElement('secuencial','000001234');
$xml_dir = $xml->createElement('dirMatriz','Av. Juan Félix Proaño y Londres');

$xml_def = $xml->createElement('infoFactura');
$xml_fec = $xml->createElement('fechaEmision','21/09/2020');
$xml_des = $xml->createElement('dirEstablecimiento','Av. Juan Félix Proaño y Londres');
$xml_obl = $xml->createElement('obligadoContabilidad','SI');
$xml_ide = $xml->createElement('tipoIdentificacionComprador','05');
$xml_rco = $xml->createElement('razonSocialComprador','José Sánchez');
$xml_idc = $xml->createElement('identificacionComprador','0201503224');
$xml_drc = $xml->createElement('direccionComprador','0201503224');
$xml_tsi = $xml->createElement('totalSinImpuestos','1.00');
$xml_tds = $xml->createElement('totalDescuento','0.00');

$xml_imp = $xml->createElement('totalConImpuestos');
$xml_tim = $xml->createElement('totalImpuesto');
$xml_tco = $xml->createElement('codigo','2');
$xml_cpr = $xml->createElement('codigoPorcentaje','0');
$xml_bas = $xml->createElement('baseImponible','1.00');
$xml_val = $xml->createElement('valor','0');

$xml_pro = $xml->createElement('propina','0.00');
$xml_imt = $xml->createElement('importeTotal','1.00');
$xml_mon = $xml->createElement('moneda','DOLAR');

$xml_pgs = $xml->createElement('pagos');
$xml_pag = $xml->createElement('pago');
$xml_fpa = $xml->createElement('formaPago','01');
$xml_tot = $xml->createElement('total','1.00');
$xml_pla = $xml->createElement('plazo','30');
$xml_uti = $xml->createElement('unidadTiempo','dias');

$xml_dts = $xml->createElement('detalles');
$xml_det = $xml->createElement('detalle');
$xml_cop = $xml->createElement('codigoPrincipal','PROD008');
$xml_coa = $xml->createElement('codigoAuxiliar','PROD008-jrsa');
$xml_dcr = $xml->createElement('descripcion','Venta de agua');
$xml_can = $xml->createElement('cantidad','1');
$xml_pru = $xml->createElement('precioUnitario','1.00');
$xml_dsc = $xml->createElement('descuento','0.00');
$xml_tsm = $xml->createElement('precioTotalSinImpuesto','1.00');

$xml_dts = $xml->createElement('detalles');
$xml_det = $xml->createElement('detalle');
$xml_cop = $xml->createElement('codigoPrincipal','PROD009');
$xml_coa = $xml->createElement('codigoAuxiliar','PROD009-jrsa');
$xml_dcr = $xml->createElement('descripcion','Venta de agua');
$xml_can = $xml->createElement('cantidad','2');
$xml_pru = $xml->createElement('precioUnitario','2.00');
$xml_dsc = $xml->createElement('descuento','0.00');
$xml_tsm = $xml->createElement('precioTotalSinImpuesto','2.00');
/*$xml_tsm2 = $xml->createElement('detallesAdicionales');
$xml_tsm3 = $xml->createElement('detAdicional');
$atributodt1 = $xml->createAttribute('nombre');
$atributodt1->value='Marca Chevrolet';
$atributodt11 = $xml->createAttribute('value');
$atributodt11->value='Chevrolet';
$xml_tsm4 = $xml->createElement('detAdicional');
$atributodt2 = $xml->createAttribute('nombre');
$atributodt2->value='Modelo';
$atributodt22 = $xml->createAttribute('value');
$atributodt22->value='2012';
$xml_tsm5 = $xml->createElement('detAdicional');
$atributodt3 = $xml->createAttribute('nombre');
$atributodt3->value='Chasis';
$atributodt33 = $xml->createAttribute('value');
$atributodt33->value='oLDETA03V20003289';*/

$xml_ips = $xml->createElement('impuestos');
$xml_ipt = $xml->createElement('impuesto');
$xml_cdg = $xml->createElement('codigo','2');
$xml_cpt = $xml->createElement('codigoPorcentaje','2');
$xml_trf = $xml->createElement('tarifa','0.00');
$xml_bsi = $xml->createElement('baseImponible','1.00');
$xml_vlr = $xml->createElement('valor','0.00');

$xml_ifa = $xml->createElement('infoAdicional');
$xml_cp1 = $xml->createElement('campoAdicional','tumail@ns.com');
$atributo = $xml->createAttribute('nombre');
$atributo->value='email';
$xml_cp2 = $xml->createElement('campoAdicional','0987082881');
$atributo2 = $xml->createAttribute('nombre');
$atributo2->value='telefono';
$xml_cp3 = $xml->createElement('campoAdicional','Cdla. La Primavera');
$atributo3 = $xml->createAttribute('nombre');
$atributo3->value='dirección';

$xml_inf->appendChild($xml_amb);
$xml_inf->appendChild($xml_tip);
$xml_inf->appendChild($xml_raz);
$xml_inf->appendChild($xml_nom);
$xml_inf->appendChild($xml_ruc);
$xml_inf->appendChild($xml_cla);
$xml_inf->appendChild($xml_doc);
$xml_inf->appendChild($xml_est);
$xml_inf->appendChild($xml_emi);
$xml_inf->appendChild($xml_sec);
$xml_inf->appendChild($xml_dir);
$xml_fac->appendChild($xml_inf);

$xml_def->appendChild($xml_fec);
$xml_def->appendChild($xml_des);
$xml_def->appendChild($xml_obl);
$xml_def->appendChild($xml_ide);
$xml_def->appendChild($xml_rco);
$xml_def->appendChild($xml_idc);
$xml_def->appendChild($xml_drc);
$xml_def->appendChild($xml_tsi);
$xml_def->appendChild($xml_tds);

$xml_def->appendChild($xml_imp);
$xml_imp->appendChild($xml_tim);
$xml_tim->appendChild($xml_tco);
$xml_tim->appendChild($xml_cpr);
$xml_tim->appendChild($xml_bas);
$xml_tim->appendChild($xml_val);
$xml_fac->appendChild($xml_def);

$xml_def->appendChild($xml_pro);
$xml_def->appendChild($xml_imt);
$xml_def->appendChild($xml_mon);

$xml_def->appendChild($xml_pgs);
$xml_pgs->appendChild($xml_pag);
$xml_pag->appendChild($xml_fpa);
$xml_pag->appendChild($xml_tot);
$xml_pag->appendChild($xml_pla);
$xml_pag->appendChild($xml_uti);

$xml_fac->appendChild($xml_dts);
$xml_dts->appendChild($xml_det);

/*$xml_tsm2->appendChild($xml_tsm3);
$xml_tsm2->appendChild($xml_tsm4);
$xml_tsm2->appendChild($xml_tsm5);
$xml_dts->appendChild($xml_tsm2);

$xml_tsm3->appendChild($atributodt11);
$xml_tsm3->appendChild($atributodt1);
$xml_tsm4->appendChild($atributodt22);
$xml_tsm4->appendChild($atributodt2);
$xml_tsm5->appendChild($atributodt33);
$xml_tsm5->appendChild($atributodt3);*/

$xml_det->appendChild($xml_cop);
$xml_det->appendChild($xml_coa);
$xml_det->appendChild($xml_dcr);
$xml_det->appendChild($xml_can);
$xml_det->appendChild($xml_pru);
$xml_det->appendChild($xml_dsc);
$xml_det->appendChild($xml_tsm);

$xml_det->appendChild($xml_ips);
$xml_ips->appendChild($xml_ipt);
$xml_ipt->appendChild($xml_cdg);
$xml_ipt->appendChild($xml_cpt);
$xml_ipt->appendChild($xml_trf);
$xml_ipt->appendChild($xml_bsi);
$xml_ipt->appendChild($xml_vlr);

$xml_fac->appendChild($xml_ifa);
$xml_ifa->appendChild($xml_cp1);
$xml_ifa->appendChild($xml_cp2);
$xml_ifa->appendChild($xml_cp3);

$xml_cp1->appendChild($atributo);
$xml_cp2->appendChild($atributo2);
$xml_cp3->appendChild($atributo3);




$xml_fac->appendChild($cabecerav);
$xml_fac->appendChild($cabecera);

$xml->appendChild($xml_fac);
echo 'CREADO: '.$xml->save('./no_firmadojrsa/ejemploxml.xml'). ' bytes';
?>