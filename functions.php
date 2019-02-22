<?php
/**
 * Understrap functions and definitions
 *
 * @package understrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$understrap_includes = array(
	'/theme-settings.php',                  // Initialize theme default settings.
	'/setup.php',                           // Theme setup and custom theme supports.
	'/widgets.php',                         // Register widget area.
	'/enqueue.php',                         // Enqueue scripts and styles.
	'/template-tags.php',                   // Custom template tags for this theme.
	'/pagination.php',                      // Custom pagination for this theme.
	'/hooks.php',                           // Custom hooks.
	'/extras.php',                          // Custom functions that act independently of the theme templates.
	'/customizer.php',                      // Customizer additions.
	'/custom-comments.php',                 // Custom Comments file.
	'/jetpack.php',                         // Load Jetpack compatibility file.
	'/class-wp-bootstrap-navwalker.php',    // Load custom WordPress nav walker.
	'/woocommerce.php',                     // Load WooCommerce functions.
	'/editor.php',                          // Load Editor functions.	               
);

foreach ( $understrap_includes as $file ) {
	$filepath = locate_template( '/inc' . $file );
	if ( ! $filepath ) {
		trigger_error( sprintf( 'Error locating /inc%s for inclusion', $file ), E_USER_ERROR );
	}
	require_once $filepath;
}

add_role('backoffice', __(
    'Backoffice'),
    array(
        'read'              => true, // Allows a user to read
        )
);

function disable_password_reset() { return false; }
add_filter ( 'allow_password_reset', 'disable_password_reset' );

function my_custom_login() {
	$time = new DateTime();
	$time->setTimezone(new DateTimeZone('America/El_Salvador'));
	$horas = $time->format('H');
	$hora = (int)$horas;	$txt  = "";

	if($hora < 12 && $hora > 0){
		$txt = "Buenos días";
	}
	if($hora >= 12 && $hora < 18){
		$txt = "Buenas tardes";
	}
	if($hora >= 18){
		$txt = "Buenos noches";
	}
	echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/css/custom-login-styles.css?'.time().'" />';
	echo "
		<style>
			@font-face {
				font-family: telefonica-light;
				src: url('".get_bloginfo('stylesheet_directory')."/fonts/TelefonicaWeb-Light.eot');
				src: url('".get_bloginfo('stylesheet_directory')."/fonts/TelefonicaWeb-Light.eot?#iefix') format('embedded-opentype'), 
					url('".get_bloginfo('stylesheet_directory')."/fonts/TelefonicaWeb-Light.woff2') format('woff2'), 
					url('".get_bloginfo('stylesheet_directory')."/fonts/TelefonicaWeb-Light.woff') format('woff'), 
					url('".get_bloginfo('stylesheet_directory')."/fonts/TelefonicaWeb-Light.ttf') format('truetype'), 
					url('".get_bloginfo('stylesheet_directory')."/fonts/TelefonicaWeb-Light.svg#AftaserifRegular') format('svg');
			}
		</style>
	";
	echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>';
	echo "<script>
			$(document).ready(function(){
				$('#login h1').append('<p class=\'msg-welcome\'>¡".$txt."!, <br />Bienvenido.</p>');
				$('#user_login').attr('placeholder', 'Usuario');
				$('#user_pass').attr('placeholder', 'Contraseña');
			})
		</script>";
}
add_action('login_head', 'my_custom_login');/*	cambiar logo de formulario de inicio de sesión*/
function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );
/*	Cambiar title de la página de inicio de sesión*/function my_login_logo_url_title() {
    return 'Movistar';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );
/*	agregar nuevas librerías a la plantilla*/

function wpb_adding_scripts_js() {	
	wp_enqueue_style( 'style-ui-date', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', array(), time(), false );    
	wp_enqueue_script( 'jquery-ui-date', '//code.jquery.com/ui/1.11.4/jquery-ui.js', array(), time(), true );	
	wp_enqueue_script( 'filtros-scripts', get_template_directory_uri() . '/js/filtros.js', array(), time(), true );
}
add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts_js' );  

function datosDashboardAcceso($slug_area) {	
	global $wpdb;	
	$data = $wpdb->get_results("select * from " . $wpdb->prefix . "permisos_areas  where dps_slug_area = '".$slug_area."'");	
	return $data;
}	

function tableAlias($slug_table) {	
	global $wpdb;	
	$aliasColumnasData = $wpdb->get_results("select * from " . $wpdb->prefix . "alias_tables  WHERE das_slug_table = '".$slug_table."'");		
	$columnas =array_filter(get_object_vars($aliasColumnasData[0]));	
	unset($columnas['das_slug_table']);	unset($columnas['das_col1']);		
	
	return $columnas;
}

function datosDeshaboard($table_name) {	
	global $wpdb;	
	$data = $wpdb->get_results("SELECT count(*) as totalColumnas FROM information_schema.columns WHERE table_name '".$slug_area."'");	
	return $data;
}	

function conectarBase($DB_USER, $DB_PASSWORD, $database_name){	

	/**	 * Instantiate the wpdb class to connect to your second database, $database_name	 */	
	$second_db = new wpdb($DB_USER, $DB_PASSWORD, $database_name, 'localhost');	
	/**	 * Use the new database object just like you would use $wpdb	 */	
	return $second_db;
}

function suscripcionCargoAutomatico() {	
	$dbhandle = conectarBase('dashdmov_dash','gbVQEbnas^c^','dashdmov_dashboards');			
	$dataSource = $dbhandle->get_results("select * from form_cargo_automatico order by fot_fecha_registro desc limit  20");	
	$tableHeader= array("Nombre","Correo","Teléfono","DUI","Fecha registro");		
	
	return generarTabla($tableHeader, $dataSource);
}

function generarTabla($tableHeader, $dataSource){	
	$tableHTML = '';			
	/*		encabezado tabla	*/			
	$tableHTML = '		
		<div class="table-responsive">			
		<table width="100%" class="tableData table table-sm table-striped ">				
		<thead class="thead-dark ">					
		<tr>				';									
		
		foreach($tableHeader as $aliasKey){							
			$tableHTML .= "<th scope='col'>".$aliasKey."</th>";					
		}		
		
	$tableHTML .= '								
		</tr>			
		</thead>			
		<tbody class="">			';	
	/*		encabezado tabla	*/		
	$keysArray = array_keys(get_object_vars($dataSource[0]));				
	
	for($i=0; $i<count($dataSource);$i++) {			
		$colID = $keysArray[0];			
		$tableHTML .= "<tr id='".$dataSource[$i]->$colID."'>";							
			for($b=1;$b<count($keysArray);$b++){				
				$colName =$keysArray[$b];				
				$tableHTML .= 			"<td>".utf8_decode($dataSource[$i]->$colName)."</td>"				;			
			}			
		
		$tableHTML .= "</tr>";		
		
	}	
	/*			cierre tabla	*/		
	
	$tableHTML .='							
		</tbody>						
		</table>					
		</div>					';	
		
	return $tableHTML;
}

function generarEstadisticas($tableName){	
	$dbhandle = conectarBase('dashdmov_dash','gbVQEbnas^c^','dashdmov_dashboards');			
	$dataSource = $dbhandle->get_results("select * from ".$tableName." order by 1 asc");	
	$keysArray = array_keys(get_object_vars($dataSource[0]));		
	
	/*		fecha inicio	*/	
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
				<svg height="200" width="200">
				<circle class="circle" cx="100" cy="100" r="50" stroke="#954b97" stroke-width="4" fill-opacity="0"></circle></svg>
		<span class="number">'.number_format(count($dataSource)).'</span>				
		<span class="subtitle">Total registros</span>			
		</span> 				
		
		<span class="circulo2">	
				<svg height="200" width="200">
				<circle class="circle" cx="100" cy="100" r="50" stroke=" #ec6839" stroke-width="4" fill-opacity="0"></circle></svg>
		<span class="number">'.date('d/m/Y', strtotime( $fechaInicio )).'</span>				
		<span class="subtitle">Fecha inicio</span>			
		</span> 				
		
		
		
		
		<span class="circulo3">	
				<svg height="200" width="200">
				<circle class="circle" cx="100" cy="100" r="50" stroke="#f59c00" stroke-width="4" fill-opacity="0"></circle></svg>
				
		<span class="number">'.date('d/m/Y', strtotime( $fechaFinal )).'</span>				
		<span class="subtitle">Último registro</span>			
		</span> 			
		
		</div>';					
		
		return $table;			
}



function generarPaginacion($tableName){
	$dbhandle = conectarBase('dashdmov_dash','gbVQEbnas^c^','dashdmov_dashboards');			
	$itemPage = 20;
	
	/*obtener toda la data de la tabla para paginar*/
	$dataSource = $dbhandle->get_results("select * from ".$tableName." order by 1 asc");
	
	

	$select="";
	$totalPage = count($dataSource)/$itemPage;
	
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
				<div class="clear"></div>
		</div>
	';
	return $paginate;
}