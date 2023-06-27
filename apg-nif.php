<?php
/*
Plugin Name: WC - APG NIF/CIF/NIE Field
Version: 2.1.0.2
Plugin URI: https://wordpress.org/plugins/wc-apg-nifcifnie-field/
Description: Add to WooCommerce a NIF/CIF/NIE field.
Author URI: https://artprojectgroup.es/
Author: Art Project Group
Requires at least: 3.8
Tested up to: 6.3
WC requires at least: 2.4
WC tested up to: 7.9

Text Domain: wc-apg-nifcifnie-field
Domain Path: /languages

@package WC - APG NIF/CIF/NIE Field
@category Core
@author Art Project Group
*/

//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Definimos constantes
define( 'DIRECCION_apg_nif', plugin_basename( __FILE__ ) );

//Funciones generales de APG
include_once( 'includes/admin/funciones-apg.php' );

//¿Está activo WooCommerce?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
    //Añade compatibilidad con HPOS
    add_action( 'before_woocommerce_init', function() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    } );

    class APG_Campo_NIF {
		//Inicializa las acciones de Usuario
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'apg_nif_admin_menu' ], 15 );
			add_action( 'admin_init', [ $this, 'apg_nif_registra_opciones' ] );
			add_action( 'woocommerce_screen_ids', [ $this, 'apg_nif_screen_id' ] );
			
			//Carga funciones externas 
			include_once 'includes/clases/pedido.php';
			include_once 'includes/clases/mi-cuenta.php';
			include_once 'includes/clases/direcciones.php';
			include_once 'includes/clases/plantillas.php';
		}

		//Pinta el formulario de configuración
		public function apg_nif_tab() {
			include( 'includes/formulario.php' );
		}

		//Añade en el menú a WooCommerce
		public function apg_nif_admin_menu() {
			add_submenu_page( 'woocommerce', __( 'APG NIF/CIF/NIE field', 'wc-apg-nifcifnie-field' ),  __( 'NIF/CIF/NIE field', 'wc-apg-nifcifnie-field' ) , 'manage_woocommerce', 'wc-apg-nifcifnie-field',  [ $this, 'apg_nif_tab' ] );
		}

		//Registra las opciones
		public function apg_nif_registra_opciones() {
            //Comprueba si existe la librería SOAP
            $apg_nif_settings = get_option( 'apg_nif_settings' );
            if ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] == "1" && ! class_exists( 'Soapclient' ) ) {
                add_action( 'admin_notices', 'apg_nif_requiere_soap' );
                $apg_nif_settings[ 'validacion_vies' ] = 0;
                update_option( 'apg_nif_settings', $apg_nif_settings );
            }

            register_setting( 'apg_nif_settings_group', 'apg_nif_settings' );
			
			//Carga funciones externas exclusivas del Panel de Administración
			include_once 'includes/clases/admin/pedidos.php';
			include_once 'includes/clases/admin/usuario.php';
		}

		//Carga los scripts y CSS de WooCommerce
		public function apg_nif_screen_id( $woocommerce_screen_ids ) {
			$woocommerce_screen_ids[] = 'woocommerce_page_wc-apg-nifcifnie-field';

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
		
	echo '<div class="notice notice-error is-dismissible" id="wc-apg-nifcifnie-field"><h3>' . $apg_nif[ 'plugin' ] . '</h3><h4>' . __( 'This plugin requires WooCommerce active to run!', 'wc-apg-nifcifnie-field' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_apg_nif );
}

//Muestra el mensaje de requerimiento de SOAP
function apg_nif_requiere_soap() {
	global $apg_nif;
		
	echo '<div class="notice notice-error is-dismissible" id="wc-apg-nifcifnie-field"><h3>' . $apg_nif[ 'plugin' ] . '</h3><h4>' . __( 'This plugin requires the <a href="http://php.net/manual/en/class.soapclient.php">SoapClient</a> PHP class active to run!', 'wc-apg-nifcifnie-field' ) . '</h4></div>';
}

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_nif_desinstalar() {
	delete_transient( 'apg_nif_plugin' );
	delete_option( 'apg_nif_settings' );
}
register_uninstall_hook( __FILE__, 'apg_nif_desinstalar' );
