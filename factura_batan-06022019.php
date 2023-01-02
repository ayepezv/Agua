<!DOCTYPE html>
<html lang="es">

<head>
<title>Factura Impresa</title> 
</head>
<body>
<font face="sans-serif" size=2>
</font></p>
<font face="sans-serif" size=2>
<div style="margin: 115px 0 0 70px;">
<table width="284" border="0">
  <tbody>
    <tr>
      <td colspan="2"><?php echo $_GET['fecha1'];?></td>
    </tr>
    <tr>
      <td colspan="2"><?php echo $_GET['cliente'];?></td>
    </tr>
    <tr>
      <td colspan="2">El Batan</td>
    </tr>
    <tr>
      <td width="194"><?php echo $_GET['cedula'];?></td>
      <td width="162">&nbsp;</td>
    </tr>
  </tbody>
</table>
	</div>

<div style="margin: 30px 0 0 5px;">
  <table width="325" border="0">
    <tbody>
      <tr>
        <td width="21">1</td>
        <td width="168">Servicio de consumo de Agua Potable</td>
        <td width="56">$ <?php echo $_GET['valor'];?></td>
        <td width="62">$ <?php echo $_GET['valor'];?></td>
      </tr>
      <tr>
        <td width="21"></td>
        <td width="168"><font face="sans-serif" size=1>Número de Meses Pagado: <?php echo $_GET['Me'];?>.</font></td>
        <td width="56"></td>
        <td width="62"></td>
      </tr>
      <tr>
        <td width="21"></td>
        <td width="168"><font face="sans-serif" size=1>Ultimo Pago de la <?php echo $_GET['Tex'];?></font><br></td>
        <td width="56"><br></td>
        <td width="62"></td>
      </tr>
      <tr>
        <td width="21"></td>
        <td width="168"><font face="sans-serif" size=1><?php echo $_GET['Tex1'];?></font></td>
        <td width="56"></td>
        <td width="62"></td>
      </tr>
    </tbody>
  </table>
</div>
<p>&nbsp;</p>
<div style="margin: 42px 0 0 180px;" >
  <table width="74"  border="0">
    <tbody>
      <tr>
        <td> TOTAL: $ <?php echo $_GET['valor'];?></td>
      </tr>
    </tbody>
  </table>
</div>
</font>
</body>

</html>
