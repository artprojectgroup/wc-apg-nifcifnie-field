<?php
/*
Plugin Name: WC - APG NIF/CIF/NIE Field
Version: 1.1
Plugin URI: https://wordpress.org/plugins/wc-apg-nifcifnie-field/
Description: Add to WooCommerce a NIF/CIF/NIE field.
Author URI: https://artprojectgroup.es/
Author: Art Project Group
Requires at least: 3.8
Tested up to: 4.8

Text Domain: apg_nif
Domain Path: /languages

@package WC - APG NIF/CIF/NIE Field
@category Core
@author Art Project Group
*/

//Igual no deberías poder abrirme
if ( !defined( 'ABSPATH' ) ) {
    exit();
}

//Definimos constantes
define( 'DIRECCION_apg_nif', plugin_basename( __FILE__ ) );

//Definimos las variables
$apg_nif = array(	
	'plugin' 		=> 'WC - APG NIF/CIF/NIE Field', 
	'plugin_uri' 	=> 'wc-apg-nifcifnie-field', 
	'donacion' 		=> 'https://artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'https://wcprojectgroup.es/tienda/ticket-de-soporte',
	'plugin_url' 	=> 'https://artprojectgroup.es/plugins-para-wordpress/plugins-para-woocommerce/wc-apg-nifcifnie-field', 
	'ajustes' 		=> 'admin.php?page=apg_nif', 
	'puntuacion' 	=> 'https://www.wordpress.org/support/view/plugin-reviews/wc-apg-nifcifnie-field'
);
$envios_adicionales = $limpieza = NULL;

//Carga el idioma
load_plugin_textdomain( 'apg_nif', null, dirname( DIRECCION_apg_nif ) . '/languages' );

//Enlaces adicionales personalizados
function apg_nif_enlaces( $enlaces, $archivo ) {
	global $apg_nif;

	if ( $archivo == DIRECCION_apg_nif ) {
		$plugin		= apg_nif_plugin( $apg_nif['plugin_uri'] );
		$enlaces[]	= '<a href="' . $apg_nif['donacion'] . '" target="_blank" title="' . __( 'Make a donation by ', 'apg_nif' ) . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[]	= '<a href="'. $apg_nif['plugin_url'] . '" target="_blank" title="' . $apg_nif['plugin'] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[]	= '<a href="https://www.facebook.com/artprojectgroup" title="' . __( 'Follow us on ', 'apg_nif' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __( 'Follow us on ', 'apg_nif' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://plus.google.com/+ArtProjectGroupES" title="' . __( 'Follow us on ', 'apg_nif' ) . 'Google+" target="_blank"><span class="genericon genericon-googleplus-alt"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="' . __( 'Follow us on ', 'apg_nif' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[]	= '<a href="https://profiles.wordpress.org/artprojectgroup/" title="' . __( 'More plugins on ', 'apg_nif' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[]	= '<a href="mailto:info@artprojectgroup.es" title="' . __( 'Contact with us by ', 'apg_nif' ) . 'e-mail"><span class="genericon genericon-mail"></span></a> <a href="skype:artprojectgroup" title="' . __( 'Contact with us by ', 'apg_nif' ) . 'Skype"><span class="genericon genericon-skype"></span></a>';
		$enlaces[]	= apg_nif_plugin( $apg_nif['plugin_uri'] );
	}
	
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'apg_nif_enlaces', 10, 2 );

//Añade el botón de configuración
function apg_nif_enlace_de_ajustes( $enlaces ) { 
	global $apg_nif;

	$enlaces_de_ajustes = array(
		'<a href="' . $apg_nif['ajustes'] . '" title="' . __( 'Settings of ', 'apg_nif' ) . $apg_nif['plugin'] .'">' . __( 'Settings', 'apg_nif' ) . '</a>', 
		'<a href="' . $apg_nif['soporte'] . '" title="' . __( 'Support of ', 'apg_nif' ) . $apg_nif['plugin'] .'">' . __( 'Support', 'apg_nif' ) . '</a>'
	);
	foreach ( $enlaces_de_ajustes as $enlace_de_ajustes ) {
		array_unshift( $enlaces, $enlace_de_ajustes );
	}
	
	return $enlaces; 
}
$plugin = DIRECCION_apg_nif; 
add_filter( "plugin_action_links_$plugin", 'apg_nif_enlace_de_ajustes' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
//¿Está activo WooCommerce?
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
	class APG_Campo_NIF {
		//Inicializa las acciones de Usuario
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'apg_nif_admin_menu' ), 15 );
			add_action( 'admin_init', array( $this, 'apg_nif_registra_opciones' ) );
			add_action( 'woocommerce_screen_ids', array( $this, 'apg_nif_screen_id' ) );
			
			//Carga funciones externas 
			include_once 'includes/clases/pedido.php';
			include_once 'includes/clases/mi-cuenta.php';
			include_once 'includes/clases/direcciones.php';
		}

		//Pinta el formulario de configuración
		public function apg_nif_tab() {
			include( 'includes/formulario.php' );
		}

		//Añade en el menú a WooCommerce
		public function apg_nif_admin_menu() {
			add_submenu_page( 'woocommerce', __( 'APG NIF/CIF/NIE field', 'apg_nif' ),  __( 'NIF/CIF/NIE field', 'apg_nif' ) , 'manage_woocommerce', 'apg_nif',  array( $this, 'apg_nif_tab' ) );
		}

		//Registra las opciones
		public function apg_nif_registra_opciones() {
			register_setting( 'apg_nif_settings_group', 'apg_nif_settings' );
			
			//Carga funciones externas exclusivas del Panel de Administración
			include_once 'includes/clases/admin/pedidos.php';
			include_once 'includes/clases/admin/usuario.php';
		}

		//Carga los scripts y CSS de WooCommerce
		public function apg_nif_screen_id( $woocommerce_screen_ids ) {
			$woocommerce_screen_ids[] = 'woocommerce_page_apg_nif';

			return $woocommerce_screen_ids;
		}
	}
	new APG_Campo_NIF();
} else {
	add_action( 'admin_notices', 'apg_nif_requiere_wc' );
}

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function apg_nif_requiere_wc() {
	global $apg_nif;
		
	echo '<div class="error fade" id="message"><h3>' . $apg_nif['plugin'] . '</h3><h4>' . __( "This plugin require WooCommerce active to run!", 'apg_nif' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_apg_nif );
}

//Obtiene toda la información sobre el plugin
function apg_nif_plugin( $nombre ) {
	global $apg_nif;

	$argumentos = ( object ) array( 
		'slug'		=> $nombre 
	);
	$consulta = array( 
		'action'	=> 'plugin_information', 
		'timeout'	=> 15, 
		'request'	=> serialize( $argumentos )
	);
	$respuesta = get_transient( 'apg_nif_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_post( 'http://api.wordpress.org/plugins/info/1.0/', array( 
			'body' => $consulta)
		);
		set_transient( 'apg_nif_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( !is_wp_error( $respuesta ) ) {
		$plugin = get_object_vars( unserialize( $respuesta['body'] ) );
	} else {
		$plugin['rating'] = 100;
	}
	
	$rating = array(
	   'rating'		=> $plugin['rating'],
	   'type'		=> 'percent',
	   'number'		=> $plugin['num_ratings'],
	);
	ob_start();
	wp_star_rating( $rating );
	$estrellas = ob_get_contents();
	ob_end_clean();

	return '<a title="' . sprintf( __( 'Please, rate %s:', 'apg_nif' ), $apg_nif['plugin'] ) . '" href="' . $apg_nif['puntuacion'] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

//Carga la hoja de estilo
function apg_nif_muestra_mensaje() {
	wp_register_style( 'apg_nif_hoja_de_estilo', plugins_url( 'assets/css/style.css', __FILE__ ) );
	wp_enqueue_style( 'apg_nif_hoja_de_estilo' );
}
add_action( 'admin_init', 'apg_nif_muestra_mensaje' );

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_nif_desinstalar() {
	delete_transient( 'apg_nif_plugin' );
}
register_uninstall_hook( __FILE__, 'apg_nif_desinstalar' );
?>
