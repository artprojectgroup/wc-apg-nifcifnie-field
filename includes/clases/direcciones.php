<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en el Pedido.
 */
class APG_Campo_NIF_en_Direcciones {
	//Inicializa las acciones de Direcciones
	public function __construct() {
		add_filter( 'woocommerce_order_formatted_billing_address', [ $this, 'apg_nif_formato_direccion_facturacion_pedido' ], 10, 2 );
		add_filter( 'woocommerce_order_formatted_shipping_address', [ $this, 'apg_nif_formato_direccion_envio_pedido' ], 10, 2 );
		add_filter( 'woocommerce_formatted_address_replacements', [ $this, 'apg_nif_formato_direccion_de_facturacion' ], 1, 2 );
		add_filter( 'woocommerce_localisation_address_formats', [ $this, 'apg_nif_formato_direccion_localizacion' ] );
		add_filter( 'wpo_wcpdf_billing_address', [ $this, 'apg_nif_direccion_factura_pdf' ], 1, 2 );
	}

	//Añade los campos en la dirección de facturarión del pedido y correo electrónico
	public function apg_nif_formato_direccion_facturacion_pedido( $campos, $pedido ) {
		$campos[ 'nif' ]      = $pedido->get_meta( '_billing_nif' );
		$campos[ 'email' ]    = $pedido->get_billing_email();
		$campos[ 'phone' ]    = $pedido->get_billing_phone();
	
		return $campos;
	}
	
	//Añade los campos en la dirección de envío del pedido y correo electrónico
	public function apg_nif_formato_direccion_envio_pedido( $campos, $pedido ) {
		if ( is_array( $campos ) ) {
			$campos[ 'nif' ]     = $pedido->get_meta( '_shipping_nif' );
			$campos[ 'email' ]   = $pedido->get_meta( '_shipping_email' );
			$campos[ 'phone' ]   = $pedido->get_shipping_phone();
		}
	
		return $campos;
	}
	
	//Reemplaza los nombres de los campos con sus datos
	public function apg_nif_formato_direccion_de_facturacion( $campos, $argumentos ) {
		$campos[ '{nif}' ]            = ( isset( $argumentos[ 'nif' ] ) ) ? $argumentos[ 'nif' ] : '';
		$campos[ '{nif_upper}' ]      = ( isset( $argumentos[ 'nif' ] ) ) ? strtoupper( $argumentos[ 'nif' ] ) : '';
		$campos[ '{phone}' ]          = ( isset( $argumentos[ 'nif' ] ) ) ? $argumentos[ 'phone' ] : '';
		$campos[ '{phone_upper}' ]    = ( isset( $argumentos[ 'nif' ] ) ) ? strtoupper( $argumentos[ 'phone' ] ) : '';
		$campos[ '{email}' ]          = ( isset( $argumentos[ 'nif' ] ) ) ? $argumentos[ 'email' ] : '';
		$campos[ '{email_upper}' ]    = ( isset( $argumentos[ 'nif' ] ) ) ? strtoupper( $argumentos[ 'email' ] ) : '';
		
		return $campos;
	}
	
	//Reordena los campos de la dirección predeterminada
	public function apg_nif_formato_direccion_localizacion( $campos ) {
		global $apg_nif_settings;
        
		if ( isset( $apg_nif_settings[ 'campos' ] ) && $apg_nif_settings[ 'campos' ] == "1" ) {	
            $campos[ 'default' ]  = "{name}\n{company}\n{nif}\n{address_1}\n{address_2}\n{city}\n{state}\n{postcode}\n{country}";
            $campos[ 'ES' ]   = "{name}\n{company}\n{nif}\n{address_1}\n{address_2}\n{postcode} {city}\n{state}\n{country}";
        } else {
            $campos[ 'default' ]  = "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{postcode}\n{country}\n{phone}\n{email}";
            $campos[ 'AT' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'AU' ]   = "{name}\n{company}\n{address_1}\n{address_2}\n{city} {state} {postcode}\n{country}\n{phone}\n{email}";
            $campos[ 'BE' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'CA' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{city} {state_code} {postcode}\n{country}\n{phone}\n{email}";
            $campos[ 'CH' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'CL' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{state}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'CN' ]   = "{country} {postcode}\n{state}, {city}, {address_2}, {address_1}\n{company}\n{name}\n{phone}\n{email}";
            $campos[ 'CZ' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'DE' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'DK' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'EE' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'ES' ]   = "{name}\n{company}\n{nif}\n{address_1}\n{address_2}\n{postcode} {city}\n{state}\n{country}\n{phone}\n{email}";
            $campos[ 'FI' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'FR' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city_upper}\n{country}\n{phone}\n{email}";
            $campos[ 'HK' ]   = "{company}\n{first_name} {last_name_upper}\n{address_1}\n{address_2}\n{city_upper}\n{state_upper}\n{country}\n{phone}\n{email}";
            $campos[ 'HU' ]   = "{last_name} {first_name}\n{company}\n{city}\n{address_1}\n{address_2}\n{postcode}\n{country}\n{phone}\n{email}";
            $campos[ 'IN' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{city} {postcode}\n{state}, {country}\n{phone}\n{email}";
            $campos[ 'IS' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'IT' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode}\n{city}\n{state_upper}\n{country}\n{phone}\n{email}";
            $campos[ 'JM' ]   = "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{postcode_upper}\n{country}\n{phone}\n{email}";
            $campos[ 'JP' ]   = "{postcode}\n{state} {city} {address_1}\n{address_2}\n{company}\n{last_name} {first_name}\n{country}\n{phone}\n{email}";
            $campos[ 'LI' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'NL' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'NO' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'NZ' ]   = "{name}\n{company}\n{address_1}\n{address_2}\n{city} {postcode}\n{country}\n{phone}\n{email}";
            $campos[ 'PL' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'PR' ]   = "{company}\n{name}\n{address_1} {address_2}\n{city} \n{country} {postcode}\n{phone}\n{email}";
            $campos[ 'PT' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'RS' ]   = "{name}\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'SE' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'SI' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'SK' ]   = "{company}\n{name}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}\n{phone}\n{email}";
            $campos[ 'TR' ]   = "{name}\n{company}\n{address_1}\n{address_2}\n{postcode} {city} {state}\n{country}\n{phone}\n{email}";
            $campos[ 'TW' ]   = "{company}\n{last_name} {first_name}\n{address_1}\n{address_2}\n{state}, {city} {postcode}\n{country}\n{phone}\n{email}";
            $campos[ 'UG' ]   = "{name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}, {country}\n{phone}\n{email}";
            $campos[ 'US' ]   = "{name}\n{company}\n{address_1}\n{address_2}\n{city}, {state_code} {postcode}\n{country}\n{phone}\n{email}";
            $campos[ 'VN' ]   = "{name}\n{company}\n{address_1}\n{city}\n{country}\n{phone}\n{email}";	            
        }

        return $campos;
	}
	
	//Añade los campos en WooCommerce PDF Invoices & Packing Slips para facturas con direcciones fuera de España
	public function apg_nif_direccion_factura_pdf( $direccion, $pedido ) {
        if ( $pedido->order->get_billing_country() != 'ES' ) {
	        $pedido_wc	= wc_get_order( $pedido->order->get_id() );
			$direccion	.= "<br />" . $pedido_wc->get_meta( '_billing_nif' );
        }

		return $direccion;
	}
}
new APG_Campo_NIF_en_Direcciones();
