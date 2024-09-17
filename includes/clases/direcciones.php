<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en el Pedido.
 */
class APG_Campo_NIF_en_Direcciones {
	//Inicializa las acciones de Direcciones
	public function __construct() {
		add_filter( 'woocommerce_formatted_address_replacements', [ $this, 'apg_nif_formato_direccion_de_facturacion' ], 10, 2 );
		add_filter( 'woocommerce_localisation_address_formats', [ $this, 'apg_nif_formato_direccion_localizacion' ], PHP_INT_MAX );
		add_filter( 'woocommerce_order_formatted_billing_address', [ $this, 'apg_nif_anade_campo_nif_direccion_facturacion' ], 10, 2 );
		add_filter( 'woocommerce_order_formatted_shipping_address', [ $this, 'apg_nif_anade_campo_nif_direccion_envio' ], 10, 2 );
	}

    //Reemplaza los nombres de los campos con sus datos
	public function apg_nif_formato_direccion_de_facturacion( $campos, $argumentos ) {
		$campos[ '{nif}' ]            = ( isset( $argumentos[ 'nif' ] ) ) ? $argumentos[ 'nif' ] : '';
		$campos[ '{nif_upper}' ]      = ( isset( $argumentos[ 'nif' ] ) ) ? strtoupper( $argumentos[ 'nif' ] ) : '';
		$campos[ '{phone}' ]          = ( isset( $argumentos[ 'phone' ] ) ) ? $argumentos[ 'phone' ] : '';
		$campos[ '{phone_upper}' ]    = ( isset( $argumentos[ 'phone' ] ) ) ? strtoupper( $argumentos[ 'phone' ] ) : '';
		$campos[ '{email}' ]          = ( isset( $argumentos[ 'email' ] ) ) ? $argumentos[ 'email' ] : '';
		$campos[ '{email_upper}' ]    = ( isset( $argumentos[ 'email' ] ) ) ? strtoupper( $argumentos[ 'email' ] ) : '';

        return $campos;
	}
	
	//Modificalos campos de las direcciones
	public function apg_nif_formato_direccion_localizacion( $direccion ) {
		global $apg_nif_settings;
        
        //Comprueba si no es la página de Finalizar compra ni la de Gracias 
        if ( ! is_page( wc_get_page_id( 'checkout' ) ) || ! empty( is_wc_endpoint_url( 'order-received' ) ) ) {
            foreach ( $direccion as $id => $formato ) {
                $direccion[ $id ] = str_replace( "{company}", "{company}\n{nif}", $formato );
            }
        }

        return $direccion;
	}

	//Añade el NIF y el teléfono a la dirección de facturación y envío
	public function apg_nif_anade_campo_nif_direccion_facturacion( $campos, $pedido ) {
        if ( is_array( $campos ) ) {
            $campos[ 'nif' ]     = ( ! empty ( $pedido->get_meta( '_billing_nif', true ) ) ) ? $pedido->get_meta( '_billing_nif', true ) : $pedido->get_meta( '_wc_billing/apg/nif', true );
            $campos[ 'email' ]   = $pedido->get_billing_email();
            $campos[ 'phone' ]   = $pedido->get_billing_phone();
        }

        return $campos;
	}
	 
	public function apg_nif_anade_campo_nif_direccion_envio( $campos, $pedido ) {
		if ( is_array( $campos ) ) {
			$campos[ 'nif' ]     = ( ! empty ( $pedido->get_meta( '_shipping_nif', true ) ) ) ? $pedido->get_meta( '_shipping_nif', true ) : $pedido->get_meta( '_wc_shipping/apg/nif', true );
			$campos[ 'email' ]   = $pedido->get_meta( '_shipping_email', true );
			$campos[ 'phone' ]   = $pedido->get_shipping_phone();
		}
		 
		return $campos;
	}
}
new APG_Campo_NIF_en_Direcciones();
