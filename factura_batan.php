<!DOCTYPE html>
<html lang="es">

<head>
<title>Factura Impresa</title> 
<meta charset="utf-8">
<style type="text/css">
body,td,th {
	font-family: Tahoma, Geneva, sans-serif;
}
</style>
</head>
<body>
<font face="sans-serif" size=2>
</font></p>
<font face="sans-serif" size=2>
<div style="margin: 5px 0 0 100px;" >
  <table width="386"  border="0">
    <tbody>
      <tr>
        <td colspan="3"> <b> COMPROBANTE DE PAGO</b></td>
      </tr>
    </tbody>
  </table>
</div>
<div style="margin: 10px 0 0 60px;" >
  <table width="386"  border="0">
    <tbody>
      <tr>
        <td colspan="1"> <b>JUNTA DE AGUA POTABLE EL BATAN</b></td>
      </tr>
	  <tr>
        <td colspan="1"> <b>RUC: 0691760901001</b></td>
      </tr>
    </tbody>
  </table>
</div>



<div style="margin: 10px 0 0 70px;">
<table width="284" border="0">
  <tbody>
    <tr>
      <td colspan="1"><b>Fecha:</b></td>
	  <td colspan="1"><?php echo $_GET['fecha1'];?></td>
    </tr>
    <tr>
	  <td colspan="1"><b>Cliente:</b></td>
      <td colspan="1"><?php echo $_GET['cliente'];?></td>
    </tr>
    <tr>
	  <td colspan="1"><b>Direccion:</b></td>
      <td colspan="1">El Batan</td>
    </tr>
    <tr>
      <td colspan="1"><b>Cedula:</b></td>
	  <td width="194"><?php echo $_GET['cedula'];?></td>
      
    </tr>
  </tbody>
</table>
	</div>

<div style="margin: 30px 0 0 5px;">
  <table width="371" border="0">
    <tbody>
	  <tr>
        <td width="27"><b>CANT.</b></td>
        <td width="235"><b>DETALLE</b></td>
        
        <td width="95"><b>VALOR</b></td>
      </tr>
      <tr>
        <td width="27">1</td>
        <td width="235"><?php echo $_GET['Det'];?></td>
        
        <td width="95">$ <?php echo $_GET['valor'];?></td>
      </tr>
      <tr>
        <td width="27"></td>
        <td width="235"><font face="sans-serif" size=1>#Meses: <?php echo $_GET['Me'];?>.</font></td>
        
        <td width="95"></td>
      </tr>
      <tr>
        <td width="27"></td>
        <td width="235"><font face="sans-serif" size=1><?php echo $_GET['Tex'];?></font><br></td>
        
        <td width="95"></td>
      </tr>
      <tr>
        <td width="27"></td>
        <td width="235"><font face="sans-serif" size=1><?php echo $_GET['Tex1'];?></font></td>
        
        <td width="95"></td>
      </tr>
    </tbody>
  </table>
</div>
<p>&nbsp;</p>
<div style="margin: 5px 0 0 230px;" >
  <table width="106"  border="0">
    <tbody>
      <tr>
        <td width="27"> TOTAL: $ <?php echo $_GET['valor'];?></td>
      </tr>
    </tbody>
  </table>
</div>
<div style="margin: 5px 0 0 70px;" >
  <table width="386"  border="0">
    <tbody>
      <tr>
        <td colspan="1"> <b>FACTURACION ELECTRONICA SRI</b></td>
      </tr>
    </tbody>
  </table>
</div>
</font>
</body>

</html>
