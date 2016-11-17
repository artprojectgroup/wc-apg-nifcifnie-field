<?php
/*
Plugin Name: WC - APG NIF/CIF/NIE Field
Version: 0.3
Plugin URI: https://wordpress.org/plugins/wc-apg-nifcifnie-field/
Description: Add to WooCommerce a NIF/CIF/NIE field.
Author URI: http://www.artprojectgroup.es/
Author: Art Project Group
Requires at least: 3.8
Tested up to: 4.7

Text Domain: apg_nif
Domain Path: /i18n/languages

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
	'donacion' 		=> 'http://www.artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'http://www.wcprojectgroup.es/tienda/ticket-de-soporte',
	'plugin_url' 	=> 'http://www.artprojectgroup.es/plugins-para-wordpress/plugins-para-woocommerce/wc-apg-nifcifnie-field', 
	'ajustes' 		=> 'admin.php?page=apg_nif', 
	'puntuacion' 	=> 'https://wordpress.org/support/view/plugin-reviews/wc-apg-nifcifnie-field'
);
$envios_adicionales = $limpieza = NULL;

//Carga el idioma
load_plugin_textdomain( 'apg_nif', null, dirname( DIRECCION_apg_nif ) . '/i18n/languages' );

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

//¿Está activo WooCommerce?
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	//Pinta el formulario de configuración
	function apg_nif_tab() {
		include( 'includes/formulario.php' );
	}

	//Añade en el menú a WooCommerce
	function apg_nif_admin_menu() {
		add_submenu_page( 'woocommerce', __( 'APG NIF/CIF/NIE field', 'apg_nif' ),  __( 'NIF/CIF/NIE field', 'apg_nif' ) , 'manage_woocommerce', 'apg_nif', 'apg_nif_tab' );
	}
	add_action( 'admin_menu', 'apg_nif_admin_menu', 15 );

	//Registra las opciones
	function apg_nif_registra_opciones() {
		register_setting( 'apg_nif_settings_group', 'apg_nif_settings' );
	}
	add_action( 'admin_init', 'apg_nif_registra_opciones' );

	//Carga los scripts y CSS de WooCommerce
	function apg_nif_screen_id( $woocommerce_screen_ids ) {
		$woocommerce_screen_ids[] = 'woocommerce_page_apg_nif';

		return $woocommerce_screen_ids;
	}
	add_filter( 'woocommerce_screen_ids', 'apg_nif_screen_id' );
	
	//Arreglamos la dirección predeterminada
	function apg_nif_campos_de_direccion( $campos ) {
		$configuracion = get_option( 'apg_nif_settings' );

		$campos['nif']		= array( 
			'label'			=> 'NIF/CIF/NIE',
			'placeholder'	=> _x( 'NIF/CIF/NIE number', 'placeholder', 'apg_nif' ),
			'required'		=> ( isset( $configuracion['requerido'] ) && $configuracion['requerido'] == "1" ) ? true : false,
			'class'			=> array( 
				'form-row-last' 
			),
			'clear'			=> true,
		);
		$campos['email']	= array( 
			'label'			=> __( 'Email Address', 'woocommerce' ),
			'required'		=> true,
			'class'			=> array( 
				'form-row-last'
			),
			'validate'		=> array( 
				'email'
			),
			'clear'			=> true,
		);
		$campos['phone']	= array( 
			'label'			=> __( 'Phone', 'woocommerce' ),
			'required'		=> true,
			'class'			=> array( 
				'form-row-first'
			),
		);
	 
		$campos['company']['class'][0]	= 'form-row-first';
		$campos['city']['class'][0]		= 'form-row-first';
		$campos['state']['class'][0]	= 'form-row-last update_totals_on_change';
		$campos['postcode']['class'][0]	.= ' update_totals_on_change';
	 
		//Reordenamos los campos
		$campos_nuevos['country']		= $campos['country'];
		$campos_nuevos['first_name']	= $campos['first_name'];
		$campos_nuevos['last_name']		= $campos['last_name'];
		$campos_nuevos['company']		= $campos['company'];
		$campos_nuevos['nif']			= $campos['nif'];
		$campos_nuevos['address_1']		= $campos['address_1'];
		$campos_nuevos['address_2']		= $campos['address_2'];
		$campos_nuevos['postcode']		= $campos['postcode'];
		$campos_nuevos['city']			= $campos['city'];
		$campos_nuevos['state']			= $campos['state'];
		if ( isset( $campos['email'] ) ) {
			$campos_nuevos['email'] = $campos['email'];
		}
		if ( isset( $campos['phone'] ) ) {
			$campos_nuevos['phone']['required'] = true;
			$campos_nuevos['phone'] = $campos['phone'];
		}

		return $campos_nuevos;
	}
	add_filter( 'woocommerce_default_address_fields', 'apg_nif_campos_de_direccion' );
	 
	//Nueva función para hacer compatible el código con WooCommerce 2.1
	function apg_nif_dame_campo_personalizado( $campo, $pedido ) {
		$valor = get_post_meta( $pedido, $campo, false );
		if ( isset( $valor[0] ) ) {
			return $valor[0];
		}
		
		return NULL;
	}
	 
	//Añadimos el NIF y el teléfono a la dirección de facturación y envío
	function apg_nif_anade_campo_nif_direccion_facturacion( $campos, $pedido ) {
		$campos['nif']		= apg_nif_dame_campo_personalizado( '_billing_nif', $pedido->id );
		$campos['phone']	= apg_nif_dame_campo_personalizado( '_billing_phone', $pedido->id );
		 
		return $campos;
	}
	add_filter( 'woocommerce_order_formatted_billing_address', 'apg_nif_anade_campo_nif_direccion_facturacion', 1, 2 );
	 
	function apg_nif_anade_campo_nif_direccion_envio( $campos, $pedido ) {
		$campos['nif']		= apg_nif_dame_campo_personalizado( '_shipping_nif', $pedido->id );
		$campos['phone']	= apg_nif_dame_campo_personalizado( '_shipping_phone', $pedido->id );
		 
		return $campos;
	}
	add_filter( 'woocommerce_order_formatted_shipping_address', 'apg_nif_anade_campo_nif_direccion_envio', 1, 2 );
	 
	function apg_nif_formato_direccion_de_facturacion( $campos, $argumentos ) {
		$campos['{nif}']			= $argumentos['nif'];
		$campos['{nif_upper}']		= strtoupper( $argumentos['nif'] );
		$campos['{phone}']			= $argumentos['phone'];
		$campos['{phone_upper}']	= strtoupper( $argumentos['phone'] );
		 
		return $campos;
	}
	add_filter( 'woocommerce_formatted_address_replacements', 'apg_nif_formato_direccion_de_facturacion', 1, 2 );
	 
	//Reordenamos los campos de la dirección predeterminada
	function apg_nif_formato_direccion_localizacion( $campos ) {
		$campos['default']	= "{name}\n{company}\n{nif}\n{address_1}\n{address_2}\n{city}\n{state}\n{postcode}\n{country}\n{phone}";
		$campos['ES']		= "{name}\n{company}\n{nif}\n{address_1}\n{address_2}\n{postcode} {city}\n{state}\n{country}\n{phone}";
		 
		return $campos;
	}
	add_filter( 'woocommerce_localisation_address_formats', 'apg_nif_formato_direccion_localizacion' );
	 
	//Arreglamos el formulario de envío
	function apg_nif_formulario_de_envio( $campos ) {
		$campos['shipping_nif']			= array( 
			'label'			=> 'NIF/CIF/NIE',
			'placeholder'	=> _x( 'NIF/CIF/NIE number', 'placeholder', 'apg_nif' ),
			'required'		=> ( isset( $configuracion['requerido_envio'] ) && $configuracion['requerido_envio'] == "1" ) ? true : false,
			'class'			=> array( 
				'form-row-last' 
			),
			'clear'			=> true,
		);
		$campos['shipping_email']		= array( 
			'label'				=> __( 'Email Address', 'woocommerce' ),
			'required'			=> false,
			'class'				=> array( 
				'form-row-first'
			),
			'validate'			=> array( 
				'email'
			),
		);
		$campos['shipping_phone']		= array( 
			'label'				=> __( 'Phone', 'woocommerce' ),
			'required'			=> true,
			'class'				=> array( 
				'form-row-last'
			),
			'clear'				=> true,
		);
		$campos['shipping_postcode']	= array( 
			'label'				=> __( 'Postcode / ZIP', 'woocommerce' ),
			'required'			=> true,
			'class'				=> array( 
				'form-row-wide', 
				'address-field'
			),
			'clear'				=> true,
			'custom_attributes'	=> array( 
				'autocomplete'	=> 'no'
			)
		);
		  
		return $campos;
	}
	add_filter( 'woocommerce_shipping_fields', 'apg_nif_formulario_de_envio' );
	 
	//Arreglamos el formulario de facturación
	function apg_nif_formulario_de_facturacion( $campos ) {
		$campos['billing_postcode']	= array( 
			'label'				=> __( 'Postcode / ZIP', 'woocommerce' ),
			'required'			=> true,
			'class'				=> array( 
				'form-row-wide', 
				'address-field'
			),
			'clear'				=> true,
			'custom_attributes'	=> array( 
				'autocomplete' => 'no'
			)
		);
		 
		return $campos;
	}
	add_filter( 'woocommerce_billing_fields', 'apg_nif_formulario_de_facturacion' );
	 
	//Añade el campo CIF/NIF a usuarios
	function apg_nif_anade_campos_administracion_usuarios( $campos ) {
		$campos['billing']['fields']['billing_nif']		= array( 
				'label'			=> 'NIF/CIF/NIE',
				'description'	=> ''
		);
	 
		$campos['shipping']['fields']['shipping_nif']	= array( 
				'label'			=> 'NIF/CIF/NIE',
				'description'	=> ''
		);
		$campos['shipping']['fields']['shipping_email']	= array( 
				'label'			=> __( 'Email', 'woocommerce' ),
				'description'	=> '',
				'class'			=> array( 
					'form-row-last'
				),
		);
		$campos['shipping']['fields']['shipping_phone']	= array( 
				'label'			=> __( 'Telephone', 'woocommerce' ),
				'description'	=> '',
				'class'			=> array( 
					'form-row-first'
				),
		);
	 
		//Reordenamos los campos
		$campos_nuevos['billing']['title']							= $campos['billing']['title'];
		$campos_nuevos['billing']['fields']['billing_first_name']	= $campos['billing']['fields']['billing_first_name'];
		$campos_nuevos['billing']['fields']['billing_last_name']	= $campos['billing']['fields']['billing_last_name'];
		$campos_nuevos['billing']['fields']['billing_company']		= $campos['billing']['fields']['billing_company'];
		$campos_nuevos['billing']['fields']['billing_nif']			= $campos['billing']['fields']['billing_nif'];
		$campos_nuevos['billing']['fields']['billing_address_1']	= $campos['billing']['fields']['billing_address_1'];
		$campos_nuevos['billing']['fields']['billing_address_2']	= $campos['billing']['fields']['billing_address_2'];
		$campos_nuevos['billing']['fields']['billing_postcode']		= $campos['billing']['fields']['billing_postcode'];
		$campos_nuevos['billing']['fields']['billing_city']			= $campos['billing']['fields']['billing_city'];
		$campos_nuevos['billing']['fields']['billing_state']		= $campos['billing']['fields']['billing_state'];
		$campos_nuevos['billing']['fields']['billing_country']		= $campos['billing']['fields']['billing_country'];
		$campos_nuevos['billing']['fields']['billing_phone']		= $campos['billing']['fields']['billing_phone'];
		$campos_nuevos['billing']['fields']['billing_email']		= $campos['billing']['fields']['billing_email'];
	 
		$campos_nuevos['shipping']['title']							= $campos['shipping']['title'];
		$campos_nuevos['shipping']['fields']['shipping_first_name']	= $campos['shipping']['fields']['shipping_first_name'];
		$campos_nuevos['shipping']['fields']['shipping_last_name']	= $campos['shipping']['fields']['shipping_last_name'];
		$campos_nuevos['shipping']['fields']['shipping_company']	= $campos['shipping']['fields']['shipping_company'];
		$campos_nuevos['shipping']['fields']['shipping_nif']		= $campos['shipping']['fields']['shipping_nif'];
		$campos_nuevos['shipping']['fields']['shipping_address_1']	= $campos['shipping']['fields']['shipping_address_1'];
		$campos_nuevos['shipping']['fields']['shipping_address_2']	= $campos['shipping']['fields']['shipping_address_2'];
		$campos_nuevos['shipping']['fields']['shipping_postcode']	= $campos['shipping']['fields']['shipping_postcode'];
		$campos_nuevos['shipping']['fields']['shipping_city']		= $campos['shipping']['fields']['shipping_city'];
		$campos_nuevos['shipping']['fields']['shipping_state']		= $campos['shipping']['fields']['shipping_state'];
		$campos_nuevos['shipping']['fields']['shipping_country']	= $campos['shipping']['fields']['shipping_country'];
		$campos_nuevos['shipping']['fields']['shipping_phone']		= $campos['shipping']['fields']['shipping_phone'];
		$campos_nuevos['shipping']['fields']['shipping_email']		= $campos['shipping']['fields']['shipping_email'];
	 
		$campos_nuevos = apply_filters( 'wcbcf_customer_meta_fields', $campos_nuevos );
		 
		return $campos_nuevos;
	}
	add_filter( 'woocommerce_customer_meta_fields', 'apg_nif_anade_campos_administracion_usuarios' );
	 
	//Añadimos el NIF a la dirección de facturación y envío
	function apg_nif_anade_campo_nif_usuario_direccion_facturacion( $campos, $usuario ) {
		$campos['nif']		= get_user_meta( $usuario, 'billing_nif', true );
		$campos['phone']	= get_user_meta( $usuario, 'billing_phone', true );
		
		return $campos;
	}
	add_filter( 'woocommerce_user_column_billing_address', 'apg_nif_anade_campo_nif_usuario_direccion_facturacion', 1, 2 );
	 
	function apg_nif_anade_campo_nif_usuario_direccion_envio( $campos, $usuario ) {
		$campos['nif']		= get_user_meta( $usuario, 'shipping_nif', true );
		$campos['phone']	= get_user_meta( $usuario, 'shipping_phone', true );
		
		return $campos;
	}
	add_filter( 'woocommerce_user_column_shipping_address', 'apg_nif_anade_campo_nif_usuario_direccion_envio', 1, 2 );
	 
	//Añade el campo NIF a Editar mi dirección
	function apg_nif_anade_campo_nif_editar_direccion( $campos, $usuario, $nombre ) {
		$campos['nif']		= get_user_meta( $usuario, $nombre . '_nif', true );
		$campos['phone']	= get_user_meta( $usuario, $nombre . '_phone', true );
	 
		//Ordena los campos
		$campos_nuevos['first_name']	= $campos['first_name'];
		$campos_nuevos['last_name']		= $campos['last_name'];
		$campos_nuevos['company']		= $campos['company'];
		$campos_nuevos['nif']			= $campos['nif'];
		$campos_nuevos['address_1']		= $campos['address_1'];
		$campos_nuevos['address_2']		= $campos['address_2'];
		$campos_nuevos['postcode']		= $campos['postcode'];
		$campos_nuevos['city']			= $campos['city'];
		$campos_nuevos['state']			= $campos['state'];
		$campos_nuevos['country']		= $campos['country'];
		$campos_nuevos['phone']			= $campos['phone'];
		 
		return $campos_nuevos;
	}
	add_filter( 'woocommerce_my_account_my_address_formatted_address', 'apg_nif_anade_campo_nif_editar_direccion', 10, 3 );
	 
	//Añade el campo NIF a Detalles del pedido
	function apg_nif_anade_campo_nif_editar_direccion_pedido( $campos ) {
		$campos['nif'] = array( 
			'label'	=> 'NIF/CIF/NIE',
			'show'	=> false
		);
		$campos['phone'] = array( 
			'label'	=> __( 'Telephone', 'woocommerce' ),
			'show'	=> false
		);
	 
		//Ordena los campos
		$campos_nuevos['first_name']	= $campos['first_name'];
		$campos_nuevos['last_name']		= $campos['last_name'];
		$campos_nuevos['company']		= $campos['company'];
		$campos_nuevos['nif']			= $campos['nif'];
		$campos_nuevos['address_1']		= $campos['address_1'];
		$campos_nuevos['address_2']		= $campos['address_2'];
		$campos_nuevos['postcode']		= $campos['postcode'];
		$campos_nuevos['city']			= $campos['city'];
		$campos_nuevos['state']			= $campos['state'];
		$campos_nuevos['country']		= $campos['country'];
		$campos_nuevos['phone']			= $campos['phone'];
	 
		return $campos_nuevos;
	}
	add_filter( 'woocommerce_admin_billing_fields', 'apg_nif_anade_campo_nif_editar_direccion_pedido' );
	add_filter( 'woocommerce_admin_shipping_fields', 'apg_nif_anade_campo_nif_editar_direccion_pedido' );

	//Carga el campo NIF en los pedidos creados manualmente
	function apg_nif_ajax( $datos_cliente ) {
		$usuario	= ( int )trim( stripslashes( $_POST[ 'user_id' ] ) );
		$formulario	= esc_attr( trim( stripslashes( $_POST[ 'type_to_load' ] ) ) );

		$datos_cliente[$formulario . '_nif'] = get_user_meta( $usuario, $formulario . '_nif', true );

		return $datos_cliente;
	}
	add_filter( 'woocommerce_found_customer_details', 'apg_nif_ajax' );

	function apg_nif_carga_hoja_de_estilo_editar_direccion_pedido() {
		echo '</pre>
	<style type="text/css"><!-- #order_data .order_data_column ._billing_company_field, #order_data .order_data_column ._shipping_company_field { float: left; margin: 9px 0 0; padding: 0; width: 48%; } #order_data .order_data_column ._billing_nif_field, #order_data .order_data_column ._shipping_nif_field { float: right; margin: 9px 0 0; padding: 0; width: 48%; } --></style>
	<pre>';
	}
	add_action( 'woocommerce_admin_order_data_after_billing_address', 'apg_nif_carga_hoja_de_estilo_editar_direccion_pedido' );
	
	//Validando el campo NIF/CIF/NIE
	function apg_nif_validacion( $nif ) {
		$falso = true;

		for ( $i = 0; $i < 9; $i ++ ) {
			$num[$i] = substr( $nif, $i, 1 );
		}
 
		if ( !preg_match( '/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/', $nif ) ) { //No tiene formato válido
			$falso = true;
		}
 
		if ( preg_match( '/(^[0-9]{8}[A-Z]{1}$)/', $nif ) ) {
			if ( $num[8] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( $nif, 0, 8 ) % 23, 1 ) ) { //NIF válido
				$falso = false;
			}
		}
 
		$suma = $num[2] + $num[4] + $num[6];
		for ( $i = 1; $i < 8; $i += 2 ) {
			$suma += substr( ( 2 * $num[$i] ), 0, 1 ) + substr( ( 2 * $num[$i] ), 1, 1 );
		}
		$n = 10 - substr( $suma, strlen( $suma ) - 1, 1 );
 
		if ( preg_match( '/^[KLM]{1}/', $nif ) ) { //NIF especial válido
			if ( $num[8] == chr( 64 + $n ) ) {
				$falso = false;
			}
		}
 
		if ( preg_match( '/^[ABCDEFGHJNPQRSUVW]{1}/', $nif ) && isset ( $num[8] ) ) {
			echo $num[8] ." == ".chr( 64 + $n )." - " . substr( $n, strlen( $n ) - 1, 1 );
			if ( $num[8] == chr( 64 + $n ) || $num[8] == substr( $n, strlen( $n ) - 1, 1 ) ) { //CIF válido
				$falso = false;
			}
		}
 
		if ( preg_match( '/^[T]{1}/', $nif ) ) {
			if ( $num[8] == preg_match( '/^[T]{1}[A-Z0-9]{8}$/', $nif ) ) { //NIE válido (T)
				$falso = false;
			}
		}
 
		if ( preg_match( '/^[XYZ]{1}/', $nif ) ) { //NIE válido (XYZ)
			if ( $num[8] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( str_replace( array( 'X', 'Y', 'Z' ), array( '0', '1', '2' ), $nif ), 0, 8 ) % 23, 1 ) ) {
				$falso = false;
			}
		}
		
		return $falso;
	}
	
	//Validando el campo NIF/CIF/NIE
	function apg_nif_validacion_de_campo() {
		$facturacion = $envio = true;

		if ( isset( $_POST['billing_nif'] ) && strlen( $_POST['billing_nif'] ) == 9 ) {
			$facturacion = apg_nif_validacion( strtoupper( $_POST['billing_nif'] ) );
		}

		if ( isset( $_POST['shipping_nif'] ) && strlen( $_POST['shipping_nif'] ) == 9 ) {
			$envio = apg_nif_validacion( strtoupper( $_POST['shipping_nif'] ) );
		}
	 
		if ( $facturacion || $envio ) {
			if ( ( $facturacion && !empty( $_POST['billing_nif'] ) ) || ( $envio && !empty( $_POST['shipping_nif'] ) ) ) {
				wc_add_notice( __( 'Please enter a valid NIF/CIF/NIE.', 'apg_nif' ), 'error' );
			}
		}
	}
	$configuracion = get_option( 'apg_nif_settings' );
	if ( isset( $configuracion['validacion'] ) && $configuracion['validacion'] == "1" ) {	
		add_action( 'woocommerce_checkout_process', 'apg_nif_validacion_de_campo' );
	}
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
		'slug' => $nombre 
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
	   'rating'	=> $plugin['rating'],
	   'type'	=> 'percent',
	   'number'	=> $plugin['num_ratings'],
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
