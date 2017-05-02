<?php
//Igual no deberías poder abrirme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Añade los campos en el Pedido.
 */
class APG_Campo_NIF_en_Direcciones {
	//Inicializa las acciones de Direcciones
	public function __construct() {
		add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'apg_nif_formato_direccion_facturacion_pedido' ), 10,2 );
		add_filter( 'woocommerce_order_formatted_shipping_address', array( $this, 'apg_nif_formato_direccion_envio_pedido' ), 10,2 );
		add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'apg_nif_formato_direccion_de_facturacion' ), 1, 2 );
		add_filter( 'woocommerce_localisation_address_formats', array( $this, 'apg_nif_formato_direccion_localizacion' ) );
	}

	//Añade los campos en la dirección de facturarión del pedido y correo electrónico
	public function apg_nif_formato_direccion_facturacion_pedido( $campos, $pedido ) {
		$numero_de_pedido	= is_callable( array( $pedido, 'get_id' ) ) ? $pedido->get_id() : $pedido->id;
		$campos['nif']		= get_post_meta( $numero_de_pedido, '_billing_nif', true );
		$campos['email']	= get_post_meta( $numero_de_pedido, '_billing_email', true );
		$campos['phone']	= get_post_meta( $numero_de_pedido, '_billing_phone', true );
	
		return $campos;
	}
	
	//Añade los campos en la dirección de envío del pedido y correo electrónico
	public function apg_nif_formato_direccion_envio_pedido( $campos, $pedido ) {
		$numero_de_pedido	= is_callable( array( $pedido, 'get_id' ) ) ? $pedido->get_id() : $pedido->id;
		$campos['nif']		= get_post_meta( $numero_de_pedido, '_shipping_nif', true );
		$campos['email']	= get_post_meta( $numero_de_pedido, '_shipping_email', true );
		$campos['phone']	= get_post_meta( $numero_de_pedido, '_shipping_phone', true );
	
		return $campos;
	}
	
	//Reemplaza los nombres de los campos con sus datos
	public function apg_nif_formato_direccion_de_facturacion( $campos, $argumentos ) {
		$campos['{nif}']			= $argumentos['nif'];
		$campos['{nif_upper}']		= strtoupper( $argumentos['nif'] );
		$campos['{phone}']			= $argumentos['phone'];
		$campos['{phone_upper}']	= strtoupper( $argumentos['phone'] );
		$campos['{email}']			= $argumentos['email'];
		$campos['{email_upper}']	= strtoupper( $argumentos['email'] );
		
		return $campos;
	}
	
	
	//Reordenamos los campos de la dirección predeterminada
	public function apg_nif_formato_direccion_localizacion( $campos ) {
		$campos['default']	= "{name}\n{company}\n{nif}\n{address_1}\n{address_2}\n{city}\n{state}\n{postcode}\n{country}\n{phone}\n{email}";
		$campos['ES']		= "{name}\n{company}\n{nif}\n{address_1}\n{address_2}\n{postcode} {city}\n{state}\n{country}\n{phone}\n{email}";
		 
		return $campos;
	}
}
new APG_Campo_NIF_en_Direcciones();