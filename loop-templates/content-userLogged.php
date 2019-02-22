<?php

echo "<br /><br /><br /><br /><br /><br />";
echo 234;

echo "logeado ".is_user_logged_in();
if ( is_user_logged_in() ) {
   echo 234;
} else {
   //wp_redirect( 'https://lab.movistar.com.sv/iniciar-sesion/' ); 
}
?>