<?php
require_once '../../config.php';
require_once 'functions.php';
function Conectar0() 
	{	
		$host =     FS_DB_HOST;
		//$host =     '190.152.215.29';
		//$host =     '172.16.0.1';
		$database = FS_DB_NAME;
		$user =     FS_DB_USER;
		$pass =     FS_DB_PASS;

	   if (!($link=pg_connect("host=$host port=5432 dbname=$database user=$user password=$pass")) )
	   { 
		  echo "Error conectando a la base de datos."; 
		  exit(); 
	   }    
	   return $link;
	}
class modulo
	{
		function getMod11Dv( $num ){	
		  $digits = str_replace( array( '.', ',' ), array( ''.'' ), strrev($num ) );
		  if ( ! ctype_digit( $digits ) )
		  {
			return false;
		  }
		  $sum = 0;
		  $factor = 2;
		  for( $i=0;$i<strlen( $digits ); $i++ )
		  {
			$sum += substr( $digits,$i,1 ) * $factor;
			if ( $factor == 7 )
			{
			  $factor = 2;
			}else{
			 $factor++;
		   }
		  }	 
		  $dv = 11 - ($sum % 11);
		  if ( $dv == 10 )
		  {
			return 1;
		  }
		  if ( $dv == 11 )
		  {
			return 0;
		  }
		  return $dv;
		}
	}




//error_reporting(0);
$idasiento=0;
$idcliente=0;
$rucepmresa="";
$ciudadempresa="";
$direccionempresa="";
$emailempresa="";
$nombreempresa="";
$nombrecorto="";
$telefonoempresa="";
$ruccliente="";
$emailcliente="";
$GRx0i="";
$xhLS0="";
$jIa0r="";
$LIGW0="";
$nombrecliente="";
$razoncliente="";
$telefonocliente="";
$cuenta=0;
$medidor="";
$direccion="";
$tarifa="";
$ambiente=0;
$contabilidad="";
$rimpe="";
$vD6kA="";
$establecimiento="";
$punto="";
$contribuidorespecial="";
$firma="";
$clave_firma="";
$logo="";
$tipofirma="";
$claveacceso="";
$datos_clientes=[];
$datos_factuacion=[];
$rutafinal_xml="";

function carga_datos_empresa()
	{
		$link=Conectar0();
		$data=false;	
		$sql = "select * from datos_empresa_jrsa;";  	
		$result = pg_query($link,$sql);
		//global $rucepmresa; así para declarar variables globales;
		if (pg_num_rows($result)>0)
		{		
			while($row = pg_fetch_array($result))
			{
				$_SESSION["rucempresa"]=$row[0];			
				$_SESSION["ciudadempresa"]=$row[1];
				$_SESSION["direccionempresa"]=$row[2];
				$_SESSION["emailempresa"]=$row[3];
				$_SESSION["nombreempresa"]=$row[4];
				$_SESSION["nombrecorto"]=$row[5];
				$_SESSION["telefonoempresa"]=$row[6];					
			}
		}
	}
function carga_datos_clientes($idcliente)
	{
		$link=Conectar0();
		$data=false;	
		global $idcliente;	
		global $datos_clientes;
		$sql = "select * from datos_cliente_jrsa where codcliente='".$idcliente."';"; 
		$result = pg_query($link,$sql);		
		if (pg_num_rows($result)>0)
		{		
			return pg_fetch_array($result);
			while($row = pg_fetch_array($result))
			{
				$ruccliente=$row[0];
				$emailcliente=$row[1];
				$nombrecliente=$row[2];
				$razoncliente=$row[3];
				$telefonocliente=$row[4];
				$cuenta=$row[6];
				$medidor=$row[7];
				$direccion=$row[8];
				$tarifa=$row[9];
			}
		}
		else
		{
			return 0;
		}
		
	}
function carga_datos_facturacion()
	{
		$link=Conectar0();
		$data=false;	
		global	$ambiente;
		global	$contabilidad;
		global	$rimpe;
		global	$establecimiento;
		global	$punto;
		global	$contribuidorespecial;
		global	$firma;
		global	$clave;
		global	$logo;
		global	$tipofirma;
		$sql ="select * from configura_v_jrsa;";
		$result = pg_query($link,$sql);		
		if (pg_num_rows($result)>0)
		{		
			return pg_fetch_array($result);
			while($row = pg_fetch_array($result))
			{
				$ambiente=$row[0];
				$contabilidad=$row[1];
				$rimpe=$row[2];
				$establecimiento=$row[3];
				$punto=$row[4];
				$contribuidorespecial=$row[5];
				$firma=$row[6];
				$clave=$row[7];
				$logo=$row[8];
				$tipofirma=$row[10];
			}
		}
		else
		{
			return 0;
		}		
	}
function rellena_ceros ($valor, $long = 0)
	{
		return str_pad($valor[0], $long, '0', STR_PAD_LEFT);
	};
function obtiene_secuencia($idasiento)
	{
		$link=Conectar0();
		$sql="select id from ecomprobantes_jrsa where id_asiento=".$idasiento.";";
		$result = pg_query($link,$sql);		
		//echo $sql;
		if (pg_num_rows($result)>0)
		{			
			return pg_fetch_array($result);
		}
		else
		{
			return 0;
		}				
	}
function carga_detalle_factura($idasiento)
	{
		$link=Conectar0();
		$sql="select count(idarti) as facturas,idarti, referencia, round(sum(pvpsindto)::numeric, 2) AS pvpsindto, round(sum(pvptotal)::numeric, 2) as pvptotal, round(sum(iva)::numeric, 2) as iva,round(pvpunitario::numeric, 2) as pvpunitario from factura_detalles_jrsa where pvpsindto>0 and pvptotal>0 and pvpunitario>0 and idfactura in (select idfactura from facturas_jrsa where idasientop=".$idasiento.") group by idarti, referencia,pvpunitario;";
		$result = pg_query($link,$sql);		
		global	$cantidad_detalle;
		$cantidad_detalle=0;
		if (pg_num_rows($result)>0)
		{			
			$cantidad_detalle=pg_num_rows($result);
			return pg_fetch_array($result);
		}
		else
		{
			return 0;
		}				
	}
function generaclave($ambiente,$establecimiento,$punto,$secuencial)
	{	
		//global $datos_factuacion;
		//global $secuencial;
		$tipo_comprobante="01";
		setlocale(LC_TIME, "spanish");
		date_default_timezone_set("America/Guayaquil");
		
		$characters = '123456789';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < 8; $i++)
		{
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		//$characters = '12345678';
		$characters =$randomString;
		$clave=date('dmY').$tipo_comprobante.$_SESSION["rucempresa"].$ambiente.$establecimiento.$punto.$secuencial.$characters."1";
		return $clave;
			
	}
function carga_datos_generales_factura($idasiento)
	{
		$link=Conectar0();
		$sql="select * from public.datos_factura_jrsa where idasiento=".$idasiento.";";
		$result = pg_query($link,$sql);		
		//echo $sql;
		if (pg_num_rows($result)>0)
		{			
			return pg_fetch_array($result);
		}
		else
		{
			return 0;
		}				
	}


if (!isset($_SESSION["rucempresa"]))
	{
		carga_datos_empresa();
	}	
function creaxml()
	{
			global $rutafinal_xml;
			global $firma;
			global $LIGW0;
			global $jIa0r;
			global $clave_firma;
			global $tipofirma;
			global $xhLS0;
			global $GRx0i;
			global $claveacceso;
			$estadoarchivo=1;
			$idasiento=$_GET['asiento'];
			if (isset($_GET['codcliente']))
			{
				$idcliente=$_GET['codcliente'];
			}
			else
			{
				$link=Conectar0();
				$sql="SELECT id_cliente,id_asiento FROM ecomprobantes_jrsa where id=".$idasiento.";";	
				$result = pg_query($link,$sql);		
				if (pg_num_rows($result)>0)
					{			
						$valores=pg_fetch_array($result);
						$idcliente=$valores[0];
						$idasiento=$valores[1];
					}	
			}			
			$cantidad_detalle=0;
			$datos_generales_factura=carga_datos_generales_factura($idasiento);
		if ($datos_generales_factura!==0)
		{
			if (!isset($_SESSION["rucempresa"]))
			{
				carga_datos_empresa();
			}			
			$datos_facturacion=carga_datos_facturacion();
			$firma=$datos_facturacion[6];$jIa0r=$firma;
			$clave_firma=$datos_facturacion[7];$xhLS0=$clave_firma;
			$tipofirma=$datos_facturacion[10];$GRx0i=$tipofirma;
			$datos_cliente=carga_datos_clientes($idcliente);
			$secuencial=rellena_ceros(obtiene_secuencia($idasiento),9);		
			$clave=generaclave($datos_facturacion[0],$datos_facturacion[3],$datos_facturacion[4],$secuencial);			
			$dig = new modulo();
			$clave=$clave.$dig->getMod11Dv($clave);$claveacceso=$clave;$LIGW0=$claveacceso;
			$rucem = $_SESSION["rucempresa"];
			$xml = new DomDocument('1.0', 'UTF-8');
			//$xml->standalone         = false;
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
			$cbc = $xml->createElement('ambiente',$datos_facturacion[0]);
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('tipoEmision',1);
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('razonSocial', $_SESSION["nombreempresa"]);
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('nombreComercial',$_SESSION["nombrecorto"]);
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('ruc',$rucem);
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('claveAcceso', $clave);
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('codDoc', '01');
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('estab',$datos_facturacion[3]);
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('ptoEmi',$datos_facturacion[4]);
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('secuencial',$secuencial);
			$cbc = $infoTributaria->appendChild($cbc);
			$cbc = $xml->createElement('dirMatriz',$_SESSION["direccionempresa"]);
			$cbc = $infoTributaria->appendChild($cbc);

			// INFORMACIOO DE FACTURA.
			$infoFactura = $xml->createElement('infoFactura');
			$infoFactura = $Factura->appendChild($infoFactura);
			$cbc = $xml->createElement('fechaEmision',date('d/m/Y'));
			$cbc = $infoFactura->appendChild($cbc);
			$cbc = $xml->createElement('dirEstablecimiento',$_SESSION["direccionempresa"]);
			$cbc = $infoFactura->appendChild($cbc);
			if ($datos_facturacion[5]!=="")
			{	
				$cbc = $xml->createElement('contribuyenteEspecial',($datos_facturacion[5]=="") ? 's/n': $datos_facturacion[5]);
				$cbc = $infoFactura->appendChild($cbc);
			}
			$cbc = $xml->createElement('obligadoContabilidad',($datos_facturacion[2] ? 'SI' : 'NO'));
			$cbc = $infoFactura->appendChild($cbc);
			$cbc = $xml->createElement('tipoIdentificacionComprador',$datos_cliente[10]);
			$cbc = $infoFactura->appendChild($cbc);
			$cbc = $xml->createElement('razonSocialComprador',$datos_cliente[3]);
			$cbc = $infoFactura->appendChild($cbc);
			$cbc = $xml->createElement('identificacionComprador',$datos_cliente[0]);
			$cbc = $infoFactura->appendChild($cbc);
			$cbc = $xml->createElement('direccionComprador',$datos_cliente[8]);
			$cbc = $infoFactura->appendChild($cbc);	
			$cbc = $xml->createElement('totalSinImpuestos',$datos_generales_factura[8]);
			$cbc = $infoFactura->appendChild($cbc);
			$cbc = $xml->createElement('totalSubsidio', 0);
			$cbc = $infoFactura->appendChild($cbc);
			$cbc = $xml->createElement('totalDescuento',0);
			$cbc = $infoFactura->appendChild($cbc);


			$totalConImpuestos = $xml->createElement('totalConImpuestos');
			$totalConImpuestos = $infoFactura->appendChild($totalConImpuestos);
			$totalImpuesto = $xml->createElement('totalImpuesto');
			$totalImpuesto = $totalConImpuestos->appendChild($totalImpuesto);
			$cbc = $xml->createElement('codigo',2);
			$cbc = $totalImpuesto->appendChild($cbc);
			$cbc = $xml->createElement('codigoPorcentaje',0);
			$cbc = $totalImpuesto->appendChild($cbc);
			/*$cbc = $xml->createElement('descuentoAdicional', '001');
			$cbc = $totalImpuesto->appendChild($cbc);*/
			$cbc = $xml->createElement('baseImponible',$datos_generales_factura[8]);
			$cbc = $totalImpuesto->appendChild($cbc);
			$cbc = $xml->createElement('valor',number_format((0*$datos_generales_factura[8]), 2, '.', ''));
			$cbc = $totalImpuesto->appendChild($cbc);

			$cbc = $xml->createElement('propina', 0.0);
			$cbc = $infoFactura->appendChild($cbc);
			$cbc = $xml->createElement('importeTotal',$datos_generales_factura[8]);
			$cbc = $infoFactura->appendChild($cbc);
			$cbc = $xml->createElement('moneda', 'DOLAR');
			$cbc = $infoFactura->appendChild($cbc);
			
				
			$pagos = $xml->createElement('pagos');
			$pagos = $infoFactura->appendChild($pagos);	
			$pago = $xml->createElement('pago');
			$pago = $pagos->appendChild($pago);
			$cbc = $xml->createElement('formaPago','01');
			$cbc = $pago->appendChild($cbc);
			$cbc = $xml->createElement('total',$datos_generales_factura[8]);
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
			$d = carga_detalle_factura($idasiento);

			/*array( 

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

			);*/


			//foreach ($lineas as $d)
			for ($i=0;$i<=$cantidad_detalle;$i++)
			{
				$i++;
				$numerolinea = $i;

				$detalle = $xml->createElement('detalle');
				$detalle = $detalles->appendChild($detalle);
				$cbc = $xml->createElement('codigoPrincipal', $d[1]);
				$cbc = $detalle->appendChild($cbc);
				$cbc = $xml->createElement('codigoAuxiliar', $d[1]);
				$cbc = $detalle->appendChild($cbc);
				$cbc = $xml->createElement('descripcion',$d[2]);
				$cbc = $detalle->appendChild($cbc);
				$cbc = $xml->createElement('cantidad', $d[0]);
				$cbc = $detalle->appendChild($cbc);
				$cbc = $xml->createElement('precioUnitario', $d[6]);
				$cbc = $detalle->appendChild($cbc);
				$cbc = $xml->createElement('descuento', '0.00');
				$cbc = $detalle->appendChild($cbc);
				$cbc = $xml->createElement('precioTotalSinImpuesto',$d[3]);
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
				$cbc = $xml->createElement('baseImponible',$d[3]);
				$cbc = $impuesto->appendChild($cbc);
				$cbc = $xml->createElement('valor', '0.00');
				$cbc = $impuesto->appendChild($cbc);
			}

			$infoAdicional = $xml->createElement('infoAdicional');


			$xml_cp1 = $xml->createElement('campoAdicional',$datos_cliente[1]);

			$atributo = $xml->createAttribute('nombre');
			$atributo->value='email';
			$xml_cp1->appendChild($atributo);

			$xml_cp2 = $xml->createElement('campoAdicional',$datos_generales_factura[17]);
			$atributo2 = $xml->createAttribute('nombre');
			$atributo2->value='Recaudador';
			$xml_cp2->appendChild($atributo2);

			$xml_cp3 = $xml->createElement('campoAdicional',$datos_cliente[8]);
			$atributo3 = $xml->createAttribute('nombre');
			$atributo3->value='Domicilio';
			$xml_cp3->appendChild($atributo3);

			$xml_cp4 = $xml->createElement('campoAdicional',$datos_generales_factura[12].' Correspondiente a: '.$datos_generales_factura[13]);
			$atributo3 = $xml->createAttribute('nombre');
			$atributo3->value='Concepto';
			$xml_cp4->appendChild($atributo3);

			$xml_cp5 = $xml->createElement('campoAdicional',$datos_generales_factura[11]);
			$atributo3 = $xml->createAttribute('nombre');
			$atributo3->value='Cuenta';
			$xml_cp5->appendChild($atributo3);


			$infoAdicional->appendChild($xml_cp3);
			$infoAdicional->appendChild($xml_cp1);
			$infoAdicional->appendChild($xml_cp4);
			$infoAdicional->appendChild($xml_cp5);
			$infoAdicional->appendChild($xml_cp2);


			$Factura->appendChild($infoAdicional);
			$xml->formatOutput = true;
			$strings_xml       = $xml->saveXML();

			//****mi código */
			$ruta='comprobantes/factura-sin-firmar/';
			if (!file_exists($ruta))
			{
				mkdir($ruta, 0777, true);
			}
			//****mi código */						
			if($xml->save($ruta.$clave.'.xml'))
			{
				if(chmod($ruta.$clave.'.xml', 0777))
				{
					$estadoarchivo=1;
					$rutafinal_xml=$ruta.$clave.'.xml';
				}
				else
				{
					$estadoarchivo=0;
				}
				/*echo '<span style="color: #015B01; font-size: 15pt;">XML de Factura creada:</span>&nbsp;';
				echo '<span style="color: #B21919; font-size: 15pt;">'.$rucem.'74902020320953.xml</span><br>';
				echo '<hr width="100%"></div>';
				echo '<a href="firmarxml.php" target="blank">Firmar XML</a>';*/
			}
			else
			{
				$estadoarchivo=0;
			}			
		}
		else
		{
			$estadoarchivo=3;
		}
		return $estadoarchivo;
	}
//select * from facturas_jrsa where idasientop=35192 -> para cargar los id de facturas_jrsa
//select array_to_string(array_agg(idfactura),',',',') from facturas_jrsa where idasientop=35192; -> esta es una variante de la anterior
//select count(idarti) as facturas,idarti, referencia, sum(pvpsindto)as pvpsindto, sum(pvptotal) as pvptotal, sum(iva) as iva, pvpunitario from factura_detalles_jrsa where idfactura in (4,5) group by idarti, referencia,pvpunitario; para sacar el detalle de cada factura, con estos datos llamar al proceso para crear el xml

//select count(idarti) as facturas,idarti, referencia, sum(pvpsindto)as pvpsindto, sum(pvptotal) as pvptotal, sum(iva) as iva, pvpunitario from factura_detalles_jrsa where idfactura in (select idfactura from facturas_jrsa where idasientop=35192) group by idarti, referencia,pvpunitario;
//el anterior une las dos primeras
//$datos_clientes=carga_datos_clientes();
	//echo $_SESSION["rucempresa"]."<br>";
	//echo $datos_clientes[0]."<br>";
//carga_datos_facturacion();
/*$datos_factuacion=carga_datos_facturacion();
$secuencial=rellena_ceros($idasiento,9);
$clave=generaclave();
$dig = new modulo();
$clave=$clave.$dig->getMod11Dv($clave);
echo $clave;*/
$idasiento=$_GET['asiento'];
if (isset($_GET['codcliente']))
{
	$idcliente=$_GET['codcliente'];
}
else
{
	$link=Conectar0();
	$sql="SELECT id_cliente,id_asiento FROM ecomprobantes_jrsa where id=".$idasiento.";";	
	$result = pg_query($link,$sql);		
	if (pg_num_rows($result)>0)
		{			
			$valores=pg_fetch_array($result);
			$idcliente=$valores[0];
			$idasiento=$valores[1];
		}	
}

switch (creaxml())
{
	case 0:
	echo "Archivo no creado";
	break;
	case 1:
	$VD6ka=directoriofjrsa();		
	//$FF2Zh = "\152\x61\166\141\40\x2d\x6a\x61\x72\x20" . directoriofjrsa() . "\x5c\x6d\x6f\144\145\154\134\x74\141\142\154\145\x5c\x63\x6f\x6e\146\x69\147\x75\x72\x61\x5f\x6a\x72\163\x61\170\x6d\x6c\x5c\152\162\x73\141\170\x6d\154\x5c\x65\144\x69\164\x5f\146\x6f\x72\155\137\x6a\162\x73\141\x5c\x31\x5c\x32\x5c\x33\x5c\x46\x69\x72\155\x61\x64\x6f\x72\55\x6a\x72\163\141\x2d\62\56\x31\56\152\141\x72\40" . directoriofjrsa() . "\134\143\157\x6d\x70\x72\x6f\x62\x61\156\x74\145\163\xc\141\x63\164\165\x72\141\55\x73\x69\156\55\146\x69\162\155\x61\x72\134\x20" . $LIGW0 . "\40" . directoriofjrsa() . "\xc\x69\162\x6d\x61\163\x5c\x20" . $jIa0r . "\40" . directoriofjrsa() . "\x5c\x63\157\155\160\x72\x6f\x62\x61\156\x74\x65\163\14\141\143\x74\x75\162\x61\55\146\x69\x72\155\x61\x64\141\x73\134\40" . $xhLS0 . "\40" . $GRx0i;
	//$FF2Zh=str_replace("\\","/",$FF2Zh);	
	//exec($FF2Zh, $o);
	$JG8NL = $VD6ka . "\x5c\x6d\x6f\x64\145\x6c\x5c\164\141\x62\154\x65\134\x63\x6f\x6e\x66\151\147\165\x72\x61\x5f\x6a\162\163\x61\x78\x6d\154\134\x6a\x72\163\141\170\155\x6c\134\x65\x64\151\x74\x5f\146\157\162\155\x5f\152\x72\x73\x61\x5c\61\x5c\x32\x5c\63\134";
	$CnvLt = $VD6ka . "\x5c\143\157\155\x70\x72\157\142\x61\x6e\x74\x65\x73\14\x61\143\164\x75\x72\141\55\x73\151\x6e\55\146\151\162\x6d\141\x72\134";
	$rnPi3 = $VD6ka . "\14\x69\x72\x6d\x61\x73\x5c";
	$Iq996 = $VD6ka . "\134\143\x6f\x6d\160\x72\x6f\142\x61\156\164\x65\x73\14\x61\x63\x74\x75\162\x61\x2d\x66\151\x72\155\141\144\x61\163\134";
	$EJC2z = $VD6ka . "\134\143\x6f\x6d\160\162\x6f\x62\x61\x6e\x74\145\163\14\x61\x63\x74\165\x72\141\55\145\x6e\166\x69\141\x64\141\163\x5c";
	$bQLan = "\152\x61\x76\141\x20\x2d\152\141\162\40" . $JG8NL . "\106\151\162\x6d\141\144\x6f\162\x2d\152\162\x73\x61\55\62\x2e\61\x2e\152\x61\x72\40" . $CnvLt . "\40" . $LIGW0 . "\x20" . $rnPi3 . "\x20" . $jIa0r . "\x20" . $Iq996 . "\40" . $xhLS0 . "\x20" . $GRx0i;
	$bQLan = str_replace("\x5c", "\57", $bQLan);	
	exec($bQLan, $o);
	if (count($o)>0)
	{
		if(strpos($o[0],"salvada")!=="" and strpos($o[0],"salvada")>0)
		{
			echo "Archivo creado con exito";
			
		}
		else
		{
			echo $o[0]."jrsa";
		}
	}
	else
	{
		echo "Error al firmar archivo";
	}
	break;
	case 3:
	echo "Factura se encuentra en estado enviada o autorizada";
	break;
	default:
	echo "Error desconocido";
	break;
}
 ?>