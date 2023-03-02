<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Definimos las variables
$apg_nif = [	
	'plugin' 		=> 'WC - APG NIF/CIF/NIE Field', 
	'plugin_uri' 	=> 'wc-apg-nifcifnie-field', 
	'donacion' 		=> 'https://artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'https://artprojectgroup.es/tienda/soporte-tecnico',
	'plugin_url' 	=> 'https://artprojectgroup.es/plugins-para-woocommerce/wc-apg-nifcifnie-field', 
	'ajustes' 		=> 'admin.php?page=wc-apg-nifcifnie-field', 
	'puntuacion' 	=> 'https://www.wordpress.org/support/view/plugin-reviews/wc-apg-nifcifnie-field'
];
$envios_adicionales = $limpieza = NULL;

//Carga el idioma
function apg_nif_inicia_idioma() {
    load_plugin_textdomain( 'wc-apg-nifcifnie-field', null, dirname( DIRECCION_apg_nif ) . '/languages' );
}
add_action( 'plugins_loaded', 'apg_nif_inicia_idioma' );

//Carga la configuración del plugin
$apg_nif_settings	= get_option( 'apg_nif_settings' );

//Enlaces adicionales personalizados
function apg_nif_enlaces( $enlaces, $archivo ) {
	global $apg_nif;

	if ( $archivo == DIRECCION_apg_nif ) {
		$plugin		= apg_nif_plugin( $apg_nif[ 'plugin_uri' ] );
		$enlaces[]	= '<a href="' . $apg_nif[ 'donacion' ] . '" target="_blank" title="' . __( 'Make a donation by ', 'wc-apg-nifcifnie-field' ) . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[]	= '<a href="'. $apg_nif[ 'plugin_url' ] . '" target="_blank" title="' . $apg_nif[ 'plugin' ] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[]	= '<a href="https://www.facebook.com/artprojectgroup" title="' . __( 'Follow us on ', 'wc-apg-nifcifnie-field' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __( 'Follow us on ', 'wc-apg-nifcifnie-field' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://es.linkedin.com/in/artprojectgroup" title="' . __( 'Follow us on ', 'wc-apg-nifcifnie-field' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[]	= '<a href="https://profiles.wordpress.org/artprojectgroup/" title="' . __( 'More plugins on ', 'wc-apg-nifcifnie-field' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[]	= '<a href="mailto:info@artprojectgroup.es" title="' . __( 'Contact with us by ', 'wc-apg-nifcifnie-field' ) . 'e-mail"><span class="genericon genericon-mail"></span></a> <a href="skype:artprojectgroup" title="' . __( 'Contact with us by ', 'wc-apg-nifcifnie-field' ) . 'Skype"><span class="genericon genericon-skype"></span></a>';
		$enlaces[]	= apg_nif_plugin( $apg_nif[ 'plugin_uri' ] );
	}
	
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'apg_nif_enlaces', 10, 2 );

//Añade el botón de configuración
function apg_nif_enlace_de_ajustes( $enlaces ) { 
	global $apg_nif;

	$enlaces_de_ajustes = [
		'<a href="' . $apg_nif[ 'ajustes' ] . '" title="' . __( 'Settings of ', 'wc-apg-nifcifnie-field' ) . $apg_nif[ 'plugin' ] .'">' . __( 'Settings', 'wc-apg-nifcifnie-field' ) . '</a>', 
		'<a href="' . $apg_nif[ 'soporte' ] . '" title="' . __( 'Support of ', 'wc-apg-nifcifnie-field' ) . $apg_nif[ 'plugin' ] .'">' . __( 'Support', 'wc-apg-nifcifnie-field' ) . '</a>'
	];
	foreach ( $enlaces_de_ajustes as $enlace_de_ajustes ) {
		array_unshift( $enlaces, $enlace_de_ajustes );
	}
	
	return $enlaces; 
}
$plugin = DIRECCION_apg_nif; 
add_filter( "plugin_action_links_$plugin", 'apg_nif_enlace_de_ajustes' );

//Obtiene toda la información sobre el plugin
function apg_nif_plugin( $nombre ) {
	global $apg_nif;

	$respuesta	= get_transient( 'apg_nif_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . $nombre  );
		set_transient( 'apg_nif_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( ! is_wp_error( $respuesta ) ) {
		$plugin = json_decode( wp_remote_retrieve_body( $respuesta ) );
	} else {
	   return '<a title="' . sprintf( __( 'Please, rate %s:', 'wc-apg-nifcifnie-field' ), $apg_nif[ 'plugin' ] ) . '" href="' . $apg_nif[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . __( 'Unknown rating', 'wc-apg-nifcifnie-field' ) . '</a>';
	}

    $rating = [
	   'rating'		=> $plugin->rating,
	   'type'		=> 'percent',
	   'number'		=> $plugin->num_ratings,
	];
	ob_start();
	wp_star_rating( $rating );
	$estrellas = ob_get_contents();
	ob_end_clean();

	return '<a title="' . sprintf( __( 'Please, rate %s:', 'wc-apg-nifcifnie-field' ), $apg_nif[ 'plugin' ] ) . '" href="' . $apg_nif[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

//Hoja de estilo
function apg_nif_estilo() {
	if ( strpos( $_SERVER[ 'REQUEST_URI' ], 'wc-apg-nifcifnie-field' ) !== false || strpos( $_SERVER[ 'REQUEST_URI' ], 'plugins.php' ) !== false ) {
		wp_register_style( 'apg_nif_hoja_de_estilo', plugins_url( 'assets/css/style.css', DIRECCION_apg_nif ) ); //Carga la hoja de estilo
		wp_enqueue_style( 'apg_nif_hoja_de_estilo' );
	}
}
add_action( 'admin_enqueue_scripts', 'apg_nif_estilo' );
