-----------filtrar usuario abonado planillas

SELECT
"public".agua_planilla.pagado,
"public".agua_planilla.cuenta,
"public".agua_planilla.mes,
"public".agua_planilla.anio,
"public".agua_planilla.agua,
"public".agua_planilla.total,
"public".agua_abonados.cedula,
"public".agua_abonados.apellidos,
"public".clientes.codcliente
FROM
"public".agua_abonados
INNER JOIN "public".agua_planilla ON "public".agua_abonados.cuenta = "public".agua_planilla.cuenta
INNER JOIN "public".clientes ON "public".clientes.cifnif = "public".agua_abonados.cedula
WHERE
"public".agua_planilla.pagado = 0

----PARA PONER EN OBSERVACIONES DE FACTURA CUENTA MES Y AÑO DE EMISION
update facturascli
set observaciones= 'Cuenta: ' || albaranescli.cuenta || ' -> Emisión: ' || albaranescli.mes || '-' || albaranescli.anio 
from albaranescli
where albaranescli.idfactura = facturascli.idfactura