<?php
function directoriofjrsa()
  {
	//$directoriofjrsa=dirname(dirname(dirname(__DIR__)))."\plugins\\JRSA\\firmas\\";
	$directoriofjrsa=__DIR__;
	return $directoriofjrsa;
  }
 function upload_file($tipo)
	{
		$nombrebase="";
		$archivo;
		$destino;
		if ($tipo==1)
		{
			$destino = directoriofjrsa()."\\firmas\\";//funcion desde functions.php
			$nombrebase=basename($_FILES['firma']['name']);	
			$archivo=$_FILES['firma']['tmp_name'];
		}
		if ($tipo==2)
		{
			$destino = directoriofjrsa()."\\logos\\";//funcion desde functions.php
			$nombrebase=basename($_FILES['logo']['name']);	
			$archivo=$_FILES['logo']['tmp_name'];
		}
		@mkdir($destino, 0777);		
		$destino = $destino.$nombrebase;	
		if (move_uploaded_file($archivo, $destino))
		{
			//echo "El fichero es válido y se subió con éxito.\n";
			$cargacorrecta=1;
		}
		else
		{
			//echo "¡Posible ataque de subida de ficheros!\n";
			$cargacorrecta=0;
		}	
		return $cargacorrecta;
	}

 ?>