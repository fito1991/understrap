<?php
//require('class.database.php');
/*
 * Make sure the class only exists once
 */
if ( !class_exists( 'funciones' ) ) {
	
	/**
	 * Class for mysql
	 */
	class funciones {
		private $bd;
		public  $itemPage= 20;

		/*Inicializar conexión a base de datos*/
		public function __construct(DB $database = null)
		{
			$this->bd = new DB('labtarco_wp','%Pnf!55zk{DT','labtarco_mv');
			
		}
		
		/*Obtener conexión de base de datos, con variable protegida*/
		protected function getDbHandler(){
			return $this->bd;
		}

	
		/*
			obtener precio de terminal, contado, financiamiento, prima, seguro, cargo basico
		*/
		public function listarAreas(){
			$bdhandler = $this->getDbHandler();
			
			return  $bdhandler ->query("select dps_nombre_area, dps_slug_area, dps_table_name, dps_table_name, dps_activo from dh_permisos_areas where dps_activo = 'si'");
		}
		
		public function dashboardBD($databaseName, DB $database = null){
			$this->bd = new DB('dashdmov_pr','Qh5ztMSWamz1',$databaseName);
		}
		
		// función para cambiar la conexión cuando el dashboard pertenece a otra base de datos
		public function datosDashboardAcceso($slug_area){
			$bdhandler = $this->getDbHandler();
			
			$areas = "select * from dh_permisos_areas where dps_slug_area = ?";
			$areaData = $bdhandler->select($areas,array($slug_area),array('s'));
			
			if(!empty($areaData)){
				$this->bd = new DB('dashdmov_dash','gbVQEbnas^c^',$areaData[0]->dps_database_name);
			}
			
			return "33333333";
		}
		
		/*
			Query con todos los datos de la tabla
		*/
		public function datosDeshaboard($table_name){
			$bdhandler = $this->getDbHandler();
			
			/* 
				1. obtener el número de columnas que posee la tabla
			*/
			$tablaColumnas 		= "SELECT count(*) as totalColumnas FROM information_schema.columns WHERE table_name = ? ";
			$tablaColumnasData 	= $bdhandler->select($tablaColumnas,array($table_name),array('s'));
			/*
				2. obtener la data de toda la tabla y ordenar asc por el último campo que es la fecha
			*/
			
			return $bdhandler ->query("select * from ".$table_name." order by ".$tablaColumnasData[0]->totalColumnas." desc limit ".$this->itemPage);
			
		}
		
		/*
			obtener alias de columnas de cada tabla
		*/
		public function tableAlias($slug_table){
			$bdhandler = $this->getDbHandler();
						
			$aliasColumnas 		= "SELECT *  FROM dh_alias_tables WHERE das_slug_table = ?";
			$aliasColumnasData 	= $bdhandler->select($aliasColumnas,array($slug_table),array('s'));
			
			//convertir objeto stdclass a array asociativo
			$columnas =array_filter(get_object_vars($aliasColumnasData[0]));
			unset($columnas['das_slug_table']);
			unset($columnas['das_col1']);
			

			return $columnas;
		}
		
		/*
			generar tabla con la data del query y los alias de las columnas, retorna html o array con todos los campos y alias
		*/
		public function generarTabla($alias,$dataSource, $html){
			$tableHTML = '';
			if($html){
			
				if(!empty($alias)){
					$tableHTML = '
					<div class="table-responsive">
						<table width="100%" class="tableData table table-sm table-striped ">
							<thead class="thead-dark ">
								<tr>
							';
							
								foreach($alias as $aliasKey){
										$tableHTML .= "<th scope='col'>".$aliasKey."</th>";
								}
					$tableHTML .= '				
							</tr>
						</thead>
						<tbody class="">
						';
				}
				
							
				
				$tableHTML .= '	
						
							
						';
									/*
										se convierte el objecto stdclass a array y se obtienen los keys del array assoc
									*/
									$keysArray = array_keys(get_object_vars($dataSource[0]));
									
									for($i=0; $i<count($dataSource);$i++) {
										$colID = $keysArray[0];
										$tableHTML .= "<tr id='".$dataSource[$i]->$colID."'>";
										for($b=1;$b<count($keysArray);$b++){
											$colName =$keysArray[$b];
											
											if (strpos($colName, 'estado')){ // buscar columna de estado
												if($dataSource[$i]->$colName == 1){
													$tableHTML .= 
														"<td>Sin contactar</td>"
													;
												}else{
													$tableHTML .= 
														"<td>Contactado</td>"
													;
												}
												
											}else{
												$tableHTML .= 
													"<td>".utf8_decode($dataSource[$i]->$colName)."</td>"
												;
											}
											
										}
										$tableHTML .= "</tr>";
									 
									}
				
				
				if(!empty($alias)){
					$tableHTML .='
							</tbody>
						</table>
					</div>
					';
				}
				
			}else{
				
			}
			
			return $tableHTML;
		}
		
		
		public function generarEstadisticas($tableName){
			$bdhandler = $this->getDbHandler();
			$dataSource = $bdhandler ->query("select * from ".$tableName." order by 1 asc");
			$keysArray = array_keys(get_object_vars($dataSource[0]));
			
			//fecha inicio
			$fechaInicio = "";
			$fechaFinal	 = "";
			for($i=0; $i<count($dataSource);$i++) {
				for($b=0;$b<count($keysArray);$b++){
						$colName =$keysArray[$b];
						if (strpos($colName, 'fecha_registro')){
							if($i==0){
								$fechaInicio = $dataSource[$i]->$colName;
							}
							if($i==(count($dataSource) - 1)){
								$fechaFinal = $dataSource[$i]->$colName;
							}
						}
				}
				
				
			}
			
			$table='
			<div class="statics">
				
					<span class="circulo1">
						<span class="number">'.number_format(count($dataSource)).'</span>
						<span class="subtitle">Total registros</span>
					</span> 
			
					<span class="circulo2">
						<span class="number">'.date('d/m/Y', strtotime( $fechaInicio )).'</span>
						<span class="subtitle">Fecha inicio</span>
					</span> 
			
					<span class="circulo3">
						<span class="number">'.date('d/m/Y', strtotime( $fechaFinal )).'</span>
						<span class="subtitle">Último registro</span>
					</span> 
				
			</div>';
			
			
				
			return $table;
			
		}
		
		public function generarPaginacion($tableName){
			$bdhandler = $this->getDbHandler();
			
			/*Obtener slug tabla*/
			$tablaColumnas 		= "SELECT * FROM dh_permisos_areas WHERE dps_table_name = ? ";
			$tablaColumnasData 	= $bdhandler->select($tablaColumnas,array($tableName),array('s'));
			
			/*obtener toda la data de la tabla para paginar*/
			$dataSource = $bdhandler ->query("select * from ".$tableName." order by 1 asc");
			
			
		
			$select="";
			$totalPage = count($dataSource)/$this->itemPage;
			
			if(gettype($totalPage) == "double"){
				$totalPage = intval($totalPage) + 1;
			}
			for($i=1; $i<=($totalPage);$i++) {
				$select .= "<option value='".$i."'>Página ".number_format($i)."</option>";
			}
			
			$paginate = '
				<div class="dash-pagination">
				
						<div id="first" class="button-pag disable" data-seleccion="'.$tablaColumnasData[0]->dps_slug_area.'"><i class="fa fa-angle-double-left" aria-hidden="true"></i></div>
						<div id="prev" class="button-pag disable" data-seleccion="'.$tablaColumnasData[0]->dps_slug_area.'"><i class="fa fa-angle-left" aria-hidden="true"></i></div>
						<div class="input-pag" >Página <input id="goto" type="text"  value="1" data-seleccion="'.$tablaColumnasData[0]->dps_slug_area.'"> de <span class="totalPaginas">'.$totalPage.'</span></div>
						<div id="next" class="button-pag" data-seleccion="'.$tablaColumnasData[0]->dps_slug_area.'"><i class="fa fa-angle-right" aria-hidden="true"></i></div>
						<div id="last" class="button-pag" data-seleccion="'.$tablaColumnasData[0]->dps_slug_area.'"><i class="fa fa-angle-double-right" aria-hidden="true"></i></div>
						<select class="select-pag" data-seleccion="'.$tablaColumnasData[0]->dps_slug_area.'">
							'.$select.'
						
						</select>
				</div>
			';
			return $paginate;
		}
		
		//
		public function totalPage($slugTabla){
			$bdhandler = $this->getDbHandler();
			
			/*Obtener slug tabla*/
			$tablaDatos 		= "SELECT * FROM dh_permisos_areas WHERE dps_slug_area = ? ";
			$tablaDatosResu 	= $bdhandler->select($tablaDatos,array($slugTabla),array('s'));
			
			/*obtener el número de registro para calcular total de paginas*/
			$tablaRegistrosData 	= $bdhandler ->query("select count(*) as totalRegistros from ".$tablaDatosResu[0]->dps_table_name);
			
			//calcular total de paginas, si  retorna un numero double, se le suma uno y se convierte a int
			$totalNoPagina =  $tablaRegistrosData[0]->totalRegistros  / $this->itemPage;
			
			if(gettype($totalNoPagina) == "double"){
				$totalNoPagina = intval($totalNoPagina) + 1;
			}
			
			return $totalNoPagina;
		}
		public function filtrarDataPaginacion($typePage, $currentPage, $slugTabla){
			$bdhandler = $this->getDbHandler();
			
			/*Obtener slug tabla*/
			$tablaDatos 		= "SELECT * FROM dh_permisos_areas WHERE dps_slug_area = ? ";
			$tablaDatosResu 	= $bdhandler->select($tablaDatos,array($slugTabla),array('s'));
			
			/*obtener el número de columnas para filtrar por fecha*/
			$tablaColumnas 		= "SELECT count(*) as totalColumnas FROM information_schema.columns WHERE table_name = ? ";
			$tablaColumnasData 	= $bdhandler->select($tablaColumnas,array($tablaDatosResu[0]->dps_table_name),array('s'));
			
			
			$totalNoPagina = $this->totalPage($slugTabla);
			
			
			
			

			
			if($typePage == "next"){
				if($currentPage == $totalNoPagina){
					$newCurrentPage = $totalNoPagina;
					$limitFin = $newCurrentPage * $this->itemPage;
					$limitInicio = $limitFin - $this->itemPage;
				}else{
					$newCurrentPage = $currentPage + 1;
					$limitFin = $newCurrentPage * $this->itemPage;
					$limitInicio = $limitFin - $this->itemPage;
				}
				
			}
			
			if($typePage == "prev"){
				//si la pagina actual es 1 y anterior es 1, dejar los valores de la misma pagina
				if($currentPage == 1){
					$newCurrentPage = 1;
					$limitFin = $this->itemPage;
					$limitInicio = 0;
				}else{
					$newCurrentPage = $currentPage - 1;
					$limitFin = $newCurrentPage * $this->itemPage;
					$limitInicio = $limitFin - $this->itemPage;
				}
				
			}
			if($typePage == "first"){
					$newCurrentPage = 1;
					$limitFin = $this->itemPage;
					$limitInicio = 0;
			}
			
			if($typePage == "last"){
					$newCurrentPage = $totalNoPagina;
					$limitFin = $this->itemPage *  $totalNoPagina;
					$limitInicio = $limitFin - $this->itemPage;
			}
			
			if($typePage == "goTo"){
				
					$newCurrentPage = $currentPage;
					$limitFin = $newCurrentPage * $this->itemPage;
					$limitInicio = $limitFin - $this->itemPage;
				
				
			}
		
			/*
				2. obtener la data de toda la tabla y ordenar asc por el último campo que es la fecha
			*/
			
			$dataSource = $bdhandler ->query("select * from ".$tablaDatosResu[0]->dps_table_name." order by ".$tablaColumnasData[0]->totalColumnas." desc limit ".$limitInicio.",".$this->itemPage);
			return array(
				$this->generarTabla("",$dataSource, true),
				$newCurrentPage
			);
			
			
			return $this->generarTabla("",$dataSource, true);
			//print_r($tablaRegistrosData);
			
			
		}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		/*Filtrar data segun select*/
		public function filtrarDataPaginacionSelect($fecha1, $fecha2, $typePage, $currentPage, $slugTabla){
			$bdhandler = $this->getDbHandler();
			
			$tablaDatosResu = $this->obtenerNombreUltimaColumna($slugTabla);
			/*
				0 - nombre tabla
				
			*/
			
			
			$totalNoPagina = $this->totalPage($slugTabla);
			
			
			
			

			
			if($typePage == "next"){
				if($currentPage == $totalNoPagina){
					$newCurrentPage = $totalNoPagina;
					$limitFin = $newCurrentPage * $this->itemPage;
					$limitInicio = $limitFin - $this->itemPage;
				}else{
					$newCurrentPage = $currentPage + 1;
					$limitFin = $newCurrentPage * $this->itemPage;
					$limitInicio = $limitFin - $this->itemPage;
				}
				
			}
			
			if($typePage == "prev"){
				//si la pagina actual es 1 y anterior es 1, dejar los valores de la misma pagina
				if($currentPage == 1){
					$newCurrentPage = 1;
					$limitFin = $this->itemPage;
					$limitInicio = 0;
				}else{
					$newCurrentPage = $currentPage - 1;
					$limitFin = $newCurrentPage * $this->itemPage;
					$limitInicio = $limitFin - $this->itemPage;
				}
				
			}
			if($typePage == "first"){
					$newCurrentPage = 1;
					$limitFin = $this->itemPage;
					$limitInicio = 0;
			}
			
			if($typePage == "last"){
					$newCurrentPage = $totalNoPagina;
					$limitFin = $this->itemPage *  $totalNoPagina;
					$limitInicio = $limitFin - $this->itemPage;
			}
			
			if($typePage == "goTo"){
				
					$newCurrentPage = $currentPage;
					$limitFin = $newCurrentPage * $this->itemPage;
					$limitInicio = $limitFin - $this->itemPage;
				
				
			}
		
			/*
				2. obtener la data de toda la tabla y ordenar asc por el último campo que es la fecha
			*/
			
			$dataSource = $bdhandler ->query("select * from ".$tablaDatosResu[0]->dps_table_name." order by ".$tablaColumnasData[0]->totalColumnas." desc limit ".$limitInicio.",".$this->itemPage);
			return array(
				$this->generarTabla("",$dataSource, true),
				$newCurrentPage
			);
			
			
			//return $this->generarTabla("",$dataSource, true);
			//print_r($tablaRegistrosData);
			
			
		}
		
		
		
		public function filtrarData($fecha1, $fecha2, $slugTabla){
			
			$bdhandler = $this->getDbHandler();
			/*se obtiene el nombre de la ultima columna de la tabla y el nombre de la tabla*/
			$tablaDatosResu = $this->obtenerNombreUltimaColumna($slugTabla);
			
			$table = "";

			/*se convierte el formato dd/mm/yyyy a dd/mm/yyyy*/
			if(!empty($fecha1) && !empty($fecha2)){
				$convert1 = DateTime::createFromFormat('d/m/Y', $fecha1);
				$fecha1 = $convert1->format('Y-m-d');
				
				$convert2 = DateTime::createFromFormat('d/m/Y', $fecha2);
				$fecha2 = $convert2->format('Y-m-d');
				
				$query = "select * from ".$tablaDatosResu[1]." where DATE_FORMAT(".$tablaDatosResu[0].",'%Y-%m-%d') between '".$fecha1."' and '".$fecha2."' order by ".$tablaDatosResu[0]." desc limit 0,20";

			}else{
				$query = "select * from ".$tablaDatosResu[1]." order by ".$tablaDatosResu[0]." desc limit 0,20";
			}
			
			$table = $this->generarTabla("",$bdhandler ->query($query), true);
			return $table;
		}
		
		
		public function obtenerNumeroPaginacion($fecha1, $fecha2, $slugTabla){
			$bdhandler = $this->getDbHandler();
			$nombreUltimaColumna = $this->obtenerNombreUltimaColumna($slugTabla);
			$table = "";
			$select="";
			
			
			
			if(!empty($fecha1) && !empty($fecha2)){
				$convert1 = DateTime::createFromFormat('d/m/Y', $fecha1);
				$fecha1 = $convert1->format('Y-m-d');
				
				$convert2 = DateTime::createFromFormat('d/m/Y', $fecha2);
				$fecha2 = $convert2->format('Y-m-d');
				
				
				/*query segun limite*/
				$queryLimite = "select * from ".$nombreUltimaColumna[1]." where DATE_FORMAT(".$nombreUltimaColumna[0].",'%Y-%m-%d') between '".$fecha1."' and '".$fecha2."' order by ".$nombreUltimaColumna[0]." desc limit 0,20";
				/*query con todos los resultados segun rango de fecha*/
				$queryTotal = "select count(".$nombreUltimaColumna[0].") as total from ".$nombreUltimaColumna[1]." where DATE_FORMAT(".$nombreUltimaColumna[0].",'%Y-%m-%d') between '".$fecha1."' and '".$fecha2."' order by ".$nombreUltimaColumna[0]." desc";
				
			}else{
				$queryLimite = "select * from ".$nombreUltimaColumna[1]." order by ".$nombreUltimaColumna[0]." desc limit 0,20";
				$queryTotal = "select count(".$nombreUltimaColumna[0].") as total from ".$nombreUltimaColumna[1]." order by ".$nombreUltimaColumna[0]." desc";
			}
			
			$queryTotalResult = $bdhandler->query($queryTotal);
			
			$paginasNo = intval($queryTotalResult[0]->total) / $this->itemPage;
			if(gettype($paginasNo) == "double"){
				$paginasNo = intval($paginasNo) + 1;
			}
		
			for($i=1; $i<=($paginasNo);$i++) {
				$select .= '<option value="'.$i.'">Página '.$i.'</option>';
			}
			
			$primaResult = Array
			(
				"RegistrosPaginacion" => 0,
				"cantidadPaginas" => $paginasNo,
				"selected" => $select
			);
	
	
			return $primaResult;
			
		}
		
		public function obtenerNombreUltimaColumna($slugTabla){
			$bdhandler = $this->getDbHandler();
			/*Obtener slug tabla*/
			$tablaDatos 		= "SELECT * FROM dh_permisos_areas WHERE dps_slug_area = ? ";
			$tablaDatosResu 	= $bdhandler->select($tablaDatos,array($slugTabla),array('s'));
			
			
			/*obtener el número de columnas para filtrar por fecha*/
			$tablaColumnas 		= "SELECT count(*) as totalColumnas FROM information_schema.columns WHERE table_name = ? ";
			$tablaColumnasData 	= $bdhandler->select($tablaColumnas,array($tablaDatosResu[0]->dps_table_name),array('s'));
			
			/*obtener el nombre de la última columna*/
			$tablaUltimaColumna = "SELECT 
							COLUMN_NAME,
							ORDINAL_POSITION
							FROM information_schema.COLUMNS 
							WHERE TABLE_SCHEMA = '".$tablaDatosResu[0]->dps_database_name."'
							AND TABLE_NAME ='".$tablaDatosResu[0]->dps_table_name."'
							ORDER BY ORDINAL_POSITION DESC 
							LIMIT 1";
							
			$tablaUltimaColumnaData = $bdhandler ->query($tablaUltimaColumna);
			
			
			/*
				0 - nombre ultima columna de la tabla
				1 - nombre de la tabla
			*/
			return array(
					$tablaUltimaColumnaData[0]->COLUMN_NAME,
					$tablaDatosResu[0]->dps_table_name
				);
		}
	}
}














					
					
				