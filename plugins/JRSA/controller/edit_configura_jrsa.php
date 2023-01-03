
<?php
/// la clase se debe llamar igual que el archivo
class edit_configura_jrsa extends fs_edit_controller
{
  public function __construct() {
    /// se crea una entrada 'Mi controlador' dentro del menú 'Mio'
    parent::__construct(__CLASS__, 'Factura-JRSA', 'admin', FALSE, FALSE);
  }

  public function get_model_class_name()
    {
        return 'configura_jrsa';
    }

	protected function set_edit_columns()
    {
        
		$this->form->add_column('id','string','ID',1,false,1);
		$tambiente = $this->sql_distinct('ambiente_jrsa', 'numero', 'descripcion');
        $this->form->add_column_select('ambiente', $tambiente, 'Ambiente',1, true);		
		//$this->form->add_column('ambiente','number','Ambiente',1,true);
		$this->form->add_column('contabilidad','bool','Contabilidad',1,true);
		$this->form->add_column('rimpe','bool','RIMPE',1,true);
		$this->form->add_column('establecimiento','string','Establecimiento',1,true);
		$this->form->add_column('punto','string','Punto',1,true);
		$this->form->add_column('cespecialresolucion','string','Especial',1);
		/*$firmabase = $this->sql_distinct('configura_jrsa', 'firma');		
		if (isset($firmabase[0]) and $firmabase[0]!='')
		{
			$this->form->add_column('firma','string','Firma',4);
		}
		else*/
		{
			$_SESSION['firma']=$this->sql_distinct3('configura_jrsa', 'firma','logo');						
			$this->form->add_column('firma','file','Firma',4);	
		}
		
		$this->form->add_column('clave','string','Clave',2,true);
		$_SESSION['logo']=$this->sql_distinct3('configura_jrsa', 'logo');								
		$this->form->add_column('logo','file','Logo',4);		
		$tipos = $this->sql_distinct2('certificantes_jrsa', 'numero', 'descripcion','estado','1');
        $this->form->add_column_select('tipo_firma', $tipos, 'Tipo Firma', 3, true);		
		$this->form->add_column('estado','bool','Estado',1,true);      
		/*$this->new_advice('tu texto');
		$this->new_message('tu texto');
		$this->new_error_msg('tu texto');
		$this->user;*/		
		/*echo '<script type="text/javascript">window.onload = function () {$("#btn_delete_model").remove();$(".btn-success").remove();$("input[name=id]").attr("onclick","this.blur()");$("input[name=firma]").attr("onclick","cambiatipo();");$(".form").attr("enctype","multipart/form-data");}</script>';
		echo '<script type="text/javascript">function cambiatipo(){$("input[name=firma]").attr("type","file");$("input[name=firma]").attr("accept",".p12");}</script>';		estas líneas es sin cambiar nada en los archivos base fs_edit_controller*/
		echo '<script type="text/javascript">window.onload = function () {$("#btn_delete_model").remove();$(".btn-success").remove();$("input[name=id]").attr("onclick","this.blur()");$(".form").attr("enctype","multipart/form-data");$("input[name=firma]").attr("accept",".p12");$("input[name=id]").attr("value","1");}</script>';
		$this->new_message('Recuerde agregar su firma electrónica');						
    }
	protected function sql_distinct2($tabla, $columna1, $columna2 = '',$campofiltro,$filtro)
    {
        if (!$this->db->table_exists($tabla)) {
            return [];
        }

        $columna2 = empty($columna2) ? $columna1 : $columna2;
        $final = [];
        $sql = "SELECT DISTINCT " . $columna1 . ", " . $columna2 . " FROM " . $tabla . " WHERE ".$campofiltro."=".$filtro." ORDER BY " . $columna2 . " ASC;";		
        $data = $this->db->select($sql);
        if (!empty($data)) {
            foreach ($data as $d) {
                if ($d[$columna1] != '') {
                    $final[$d[$columna1]] = $d[$columna2];
                }
            }
        }

        return $final;
    }
	protected function sql_distinct3($tabla, $columna1, $columna2 = '')
		{
			if (!$this->db->table_exists($tabla)) {
				return [];
			}

			$columna2 = empty($columna2) ? $columna1 : $columna2;
			$final =0;			$sql = "SELECT DISTINCT " . $columna1 . "," .$columna2 . " FROM " . $tabla . ";";			
			$data = $this->db->select($sql);			
			if (!empty($data)) {
				foreach ($data as $d) {
					
						$final = $d[$columna1];
					
				}
			}
			return $final;
		}	
}
?>
