<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$container = get_theme_mod( 'understrap_container_type' );
?>



<div class="footer">
	<div class="container">
			<img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/img/footer/telefonica.png" width="126" height="38" class="telefonica" alt="Telefónica">
			
	  <div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>
	
	
	

</div><!-- #page we need this extra closing tag here -->


<?php wp_footer(); ?>
</body>

</html>

