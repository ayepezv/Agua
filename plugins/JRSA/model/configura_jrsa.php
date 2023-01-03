<?php

/// la clase se debe llamar igual que el archivo
class configura_jrsa extends fs_extended_model {
//class kdb extends fs_model {
  /*public $idkdb;
  public $sintoma;
  public $causa;
  public $solucion;
  public $observaciones;*/
  public $id;
  public $ambiente;
  public $contabilidad;
  public $rimpe;
  public $establecimiento;
  public $punto;
  public $cespecialresolucion;
  public $firma;
  public $clave;
  public $logo;
  public $web_produccion;
  public $web_pruebas;
  public $generadas;
  public $firmadas;
  public $enviadas;
  public $autorizadas;
  public $noautorizadas;
  public $codigo_activacion;
  public $tipo_firma;
  public $estado;
  public $directoriofjrsa;
  
  public function __construct($data = FALSE) {
    //parent::__construct('kdb'); /// aquí indicamos el NOMBRE DE LA TABLA
	parent::__construct('configura_jrsa',$data); /// aquí indicamos el NOMBRE DE LA TABLA
    
  }
  public function model_class_name()
  {
	  return 'configura_jrsa';
  }
  public function directoriofjrsa()
  {
	$directoriofjrsa=dirname(dirname(dirname(__DIR__)))."\plugins\\JRSA\\firmas\\";
	return $directoriofjrsa;
  }
  public function primary_column()
  {
	return 'id';
  }
  public function url($type='auto')
  {
	  if($type==='list')
	  {
		  return 'index.php?page=admin_jrsa';
	  }
	  if($type==='edit')
	  {
		return 'index.php?page=edit_configura_jrsa';  
	  }  
	  return parent::url($type);
	}
protected function save_insert()
    {
        $columns = [];
        $values = [];
		$estadofirma=0;
		$estadologo=0;
        foreach (array_keys($this->get_model_fields()) as $field) {
            if ($field != $this->primary_column() and $field !='firma' and $field !='logo') {
                $columns[] = $field;
                $values[] = $this->var2str($this->{$field});
            }
        }
		$columns[] = 'id';
        $values[] = $this->var2str(1);
		$columns[] = 'firma';
        $values[] = $this->var2str(basename($_FILES['firma']['name']));		
		$estadofirma=upload_file(1);				
		$columns[] = 'logo';
        $values[] = $this->var2str(basename($_FILES['logo']['name']));
		$estadologo=upload_file(2);
        $sql = 'INSERT INTO ' . $this->table_name() . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ');';				
        if ($estadologo==1 and $estadofirma==1)
		{
			if ($this->db->exec($sql)) {
				if (null === $this->primary_column_value()) {
					//$this->{$this->primary_column()} = $this->db->lastval();
					$this->{$this->primary_column()} = 1;
				}

				return true;
			}
		}
		else
		{
			$this->new_message('Error cargando firma o logo');
		}
        return false;
    }

    /**
     * 
     * @return bool
     */
    protected function save_update()
    {
        //upload_file();
		$sql = 'UPDATE ' . $this->table_name();
        $coma = ' SET ';
        foreach (array_keys($this->get_model_fields()) as $field) {
            if ($field == $this->primary_column()) {
                continue;
            }			
			if ($field=='firma' or $field=='logo')
			{
				if ($field=='logo')
				{
					if ($_FILES['logo']['name']!=null)
					{
						$sql .= $coma . $field . ' = ' . $this->var2str(basename($_FILES['logo']['name']));
						$estadologo=upload_file(2);
					}
					else
					{
						$sql .= $coma . $field . ' = ' .$this->var2str($_SESSION["logo"]);
					}
				}
				if ($field=='firma')
				{
					if ($_FILES['firma']['name']!=null)
					{
						$sql .= $coma . $field . ' = ' . $this->var2str(basename($_FILES['firma']['name']));
						$estadofirma=upload_file(1);
					}
					else
					{
						$sql .= $coma . $field . ' = ' .$this->var2str($_SESSION["firma"]);
					}
				}				
			}			
			else
			{
				$sql .= $coma . $field . ' = ' . $this->var2str($this->{$field});
			}
            $coma = ', ';
        }

        $sql .= ' WHERE ' . $this->primary_column() . ' = ' . $this->var2str($this->primary_column_value()) . ';';
        return (bool) $this->db->exec($sql);
    }
	
}
