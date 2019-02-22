<?php
/**
 * Post rendering content according to caller of get_template_part.
 *
 * @package understrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/*
	crear objecto de la clase funciones para utilizar en toda la plantilla
*/


?>


<div class="col-xs-12 col-sm-3 col-md-3 col-lg-2  sidebar">
	
	<span class="filtros">
		<span class="subfiltro">
			<span class="titleF"><i class="fa fa-filter" aria-hidden="true"></i> Filtros</span>
			
			<div class="input-group mb-2">
				<div class="input-group-prepend">
					<div class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></div>
				</div>
				<input id="fecha1" type="text" class="form-control" placeholder="Desde">
			</div>
			
			
			
			
			
			
			
			
			
			
			
			

			<div class="input-group mb-2">
				<div class="input-group-prepend">
					<div class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></div>
				</div>
				<input id="fecha2" type="text" class="form-control" placeholder="Hasta">
			</div>
			
			
			<button id="filtrar" type="button" class="btn btn-success btn-block filtrobtn" data-seleccion="<?php echo $slugArea; ?>">Filtrar</button>
			<button id="limpiar" type="button" class="btn btn-light btn-block" data-seleccion="<?php echo $slugArea; ?>">Limpiar filtros</button>
			<br><br>
			<button type="button" class="btn btn-outline-success btn-block" data-seleccion="<?php echo $slugArea; ?>"><i class="fa fa-file-excel-o" aria-hidden="true"></i>  Generar excel</button>
	</span>
</span></div>


<div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 module-dash " id="post-<?php the_ID(); ?>">
	<?php 
		$slugArea = $post->post_name;
		
		
		echo "<h1>"
				.get_the_title();
				
				echo generarEstadisticas("form_cargo_automatico");
		
		echo "</h1>";
		

		echo suscripcionCargoAutomatico();
		
		
		
		
		echo generarPaginacion("form_cargo_automatico");
		
		//$func->generarTabla($func->tableAlias($slugTable),$func->datosDeshaboard($tableName), true);
	?>


</div>




















