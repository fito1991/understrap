<?php
/**
 * The template for displaying all single posts.
 *
 * @package understrap
 */

if ( !boolval (is_user_logged_in()) ) {
   wp_redirect( 'https://lab.movistar.com.sv/iniciar-sesion/' ); 
} else {
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	get_header();
	$container   = get_theme_mod( 'understrap_container_type' );
	?>

		<div class="container-fluid" id="single-wrapper">

			<div class="row" id="content" tabindex="-1">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'loop-templates/'.$post->post_name, 'single' ); ?>

				<?php endwhile; // end of the loop. ?>

		</div><!-- Container end -->

	</div><!-- Wrapper end -->

<?php 
	get_footer(); 
}	
?>
