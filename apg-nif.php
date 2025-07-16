<?php
/*
Plugin Name: WC - APG NIF/CIF/NIE Field
Requires Plugins: woocommerce
Version: 4.4.0.1
Plugin URI: https://wordpress.org/plugins/wc-apg-nifcifnie-field/
Description: Add to WooCommerce a NIF/CIF/NIE field.
Author URI: https://artprojectgroup.es/
Author: Art Project Group
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.9
WC requires at least: 5.6
WC tested up to: 10.0

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
define( 'VERSION_apg_nif', '4.4.0.1' );

//Funciones generales de APG
include_once( 'includes/admin/funciones-apg.php' );

//Actualiza los usermeta
function apg_nif_actualiza_usermeta() {
    global $wpdb;
    
    //Versión que registramos tras la última actualización
    $version    = get_option( 'apg_nif_actualizado', '0.0.0' );

    //Si la versión anterior es menor que la 4.1.0.1  ejecuta la actualización
    if ( version_compare( $version, '4.1', '<' ) ) {
        //Migra _billing_nif a billing_nif
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( "UPDATE {$wpdb->usermeta} SET meta_key = 'billing_nif' WHERE meta_key = '_billing_nif'" );

        //Migra _shipping_nif a shipping_nif
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( "UPDATE {$wpdb->usermeta} SET meta_key = 'shipping_nif' WHERE meta_key = '_shipping_nif'" );

        //Marcamos que ya actualizamos los usermeta
        update_option( 'apg_nif_actualizado', VERSION_apg_nif );
    }
}
add_action( 'admin_init', 'apg_nif_actualiza_usermeta' );

//¿Está activo WooCommerce?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
    //Añade compatibilidad con HPOS y con el editor de bloques
    add_action( 'before_woocommerce_init', function() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
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
		}

		//Pinta el formulario de configuración
		public function apg_nif_tab() {
			include( 'includes/formulario.php' );
		}

		//Añade en el menú a WooCommerce
		public function apg_nif_admin_menu() {
			add_submenu_page( 'woocommerce', esc_attr__( 'APG NIF/CIF/NIE field', 'wc-apg-nifcifnie-field' ),  esc_attr__( 'NIF/CIF/NIE field', 'wc-apg-nifcifnie-field' ) , 'manage_woocommerce', 'wc-apg-nifcifnie-field',  [ $this, 'apg_nif_tab' ] );
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

            register_setting( 'apg_nif_settings_group', 'apg_nif_settings', [
                'sanitize_callback' => 'apg_nif_sanitiza_opciones'
            ] );			
			
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

    //Sanitización de opciones
    function apg_nif_sanitiza_opciones( $opciones ) {
        $predeterminadas    = [
            //Campos de texto
            'etiqueta'             => 'NIF/CIF/NIE',
            'placeholder'          => __( 'NIF/CIF/NIE number', 'wc-apg-nifcifnie-field' ),
            'error'                => __( 'Please enter a valid NIF/CIF/NIE.', 'wc-apg-nifcifnie-field' ),
            'prioridad'            => '31',
             //Checkboxes
            'requerido'            => '0',
            'requerido_envio'      => '0',
            'validacion'           => '0',
            //VIES
            'validacion_vies'      => '0',
            'etiqueta_vies'        => 'NIF/CIF/NIE/VAT number',
            'placeholder_vies'     => __( 'NIF/CIF/NIE/VAT number', 'wc-apg-nifcifnie-field' ),
            'error_vies'           => __( 'Please enter a valid VIES VAT number.', 'wc-apg-nifcifnie-field' ),
            'error_vies_max'       => __( 'Error: maximum number of concurrent requests exceeded.', 'wc-apg-nifcifnie-field' ),
            //EORI
            'validacion_eori'      => '0',
            'etiqueta_eori'        => 'NIF/CIF/NIE/EORI number',
            'placeholder_eori'     => __( 'NIF/CIF/NIE/EORI number', 'wc-apg-nifcifnie-field' ),
            'error_eori'           => __( 'Please enter a valid EORI number.', 'wc-apg-nifcifnie-field' ),
            'eori_paises'          => [],
        ];

        $opciones           = wp_parse_args( $opciones, $predeterminadas );

        return [
            'etiqueta'             => sanitize_text_field( $opciones[ 'etiqueta' ] ),
            'placeholder'          => sanitize_text_field( $opciones[ 'placeholder' ] ),
            'error'                => sanitize_text_field( $opciones[ 'error' ] ),
            'prioridad'            => strval( intval( $opciones[ 'prioridad' ] ) ),
            'requerido'            => $opciones[ 'requerido' ] === '1' ? '1' : '0',
            'requerido_envio'      => $opciones[ 'requerido_envio' ] === '1' ? '1' : '0',
            'validacion'           => $opciones[ 'validacion' ] === '1' ? '1' : '0',
            'validacion_vies'      => $opciones[ 'validacion_vies' ] === '1' ? '1' : '0',
            'etiqueta_vies'        => sanitize_text_field( $opciones[ 'etiqueta_vies' ] ),
            'placeholder_vies'     => sanitize_text_field( $opciones[ 'placeholder_vies' ] ),
            'error_vies'           => sanitize_text_field( $opciones[ 'error_vies' ] ),
            'error_vies_max'       => sanitize_text_field( $opciones[ 'error_vies_max' ] ),
            'validacion_eori'      => $opciones[ 'validacion_eori' ] === '1' ? '1' : '0',
            'etiqueta_eori'        => sanitize_text_field( $opciones[ 'etiqueta_eori' ] ),
            'placeholder_eori'     => sanitize_text_field( $opciones[ 'placeholder_eori' ] ),
            'error_eori'           => sanitize_text_field( $opciones[ 'error_eori' ] ),
            'eori_paises'          => is_array( $opciones[ 'eori_paises' ] ) ? array_map( 'sanitize_text_field', $opciones[ 'eori_paises' ] ) : [],
        ];
    }
    
    //Carga el plugin asegurándonos de que se haya cargado WooCommerce previamente. Previene error con wc_get_page_id
    add_action( 'woocommerce_loaded', function() {
        new APG_Campo_NIF();        
    } ); 
} else {
	add_action( 'admin_notices', 'apg_nif_requiere_wc' );
}

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function apg_nif_requiere_wc() {
	global $apg_nif;
		
	echo '<div class="notice notice-error is-dismissible" id="wc-apg-nifcifnie-field"><h3>' . esc_attr( $apg_nif[ 'plugin' ] ) . '</h3><h4>' . esc_attr__( 'This plugin requires WooCommerce active to run!', 'wc-apg-nifcifnie-field' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_apg_nif );
}

//Muestra el mensaje de requerimiento de SOAP
function apg_nif_requiere_soap() {
	global $apg_nif;
		
	echo '<div class="notice notice-error is-dismissible" id="wc-apg-nifcifnie-field"><h3>' . esc_attr( $apg_nif[ 'plugin' ] ) . '</h3><h4>' . esc_attr__( 'This plugin requires the <a href="http://php.net/manual/en/class.soapclient.php">SoapClient</a> PHP class active to run!', 'wc-apg-nifcifnie-field' ) . '</h4></div>';
}

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_nif_instalar() {
	register_uninstall_hook( __FILE__, 'apg_nif_desinstalar' );
}
register_activation_hook( __FILE__, 'apg_nif_instalar' );

function apg_nif_desinstalar() {    
	delete_transient( 'apg_nif_plugin' );
	delete_option( 'apg_nif_settings' );
    delete_option( 'apg_nif_actualizado' );
}
