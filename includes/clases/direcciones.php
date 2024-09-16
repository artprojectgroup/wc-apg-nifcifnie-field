<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en el Pedido.
 */
class APG_Campo_NIF_en_Direcciones {
	//Inicializa las acciones de Direcciones
	public function __construct() {
		add_filter( 'woocommerce_formatted_address_replacements', [ $this, 'apg_nif_formato_direccion_de_facturacion' ], 1, 2 );
		add_filter( 'woocommerce_localisation_address_formats', [ $this, 'apg_nif_formato_direccion_localizacion' ] );
		add_filter( 'wpo_wcpdf_billing_address', [ $this, 'apg_nif_direccion_factura_pdf' ], 1, 2 );

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
        
        if ( ! is_checkout() ) {
            foreach ( $direccion as $id => $formato ) {
                $direccion[ $id ] = str_replace( "{company}", "{company}\n{nif}", $formato );
            }
        }

        return $direccion;
	}
	
	//Añade los campos en WooCommerce PDF Invoices & Packing Slips
	public function apg_nif_direccion_factura_pdf( $direccion, $documento ) {
        if (!empty($documento->order) && $nif = $documento->get_custom_field('billing_nif') ) {
            $direccion = $direccion . "<br />$nif";
        }

		return $direccion;
	}
}
new APG_Campo_NIF_en_Direcciones();
