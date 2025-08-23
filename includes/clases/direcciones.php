<?php
/**
 * Extensiones de dirección para mostrar NIF/CIF/NIE, email y teléfono
 * en pedidos de WooCommerce (frontend y admin).
 *
 * - Define reemplazos ({nif}, {email}, {phone}, y sus versiones *_upper)
 *   para usarlos en los formatos de dirección.
 * - Inserta {nif} debajo de {company} en los formatos de localización.
 * - Añade NIF/email/teléfono a los arrays formateados de facturación y envío.
 * - Oculta campos duplicados en la página "Gracias" cuando se usan Bloques.
 *
 * @package WC_APG_NIFCIFNIE_Field
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en el Pedido.
 */
class APG_Campo_NIF_en_Direcciones {

	/**
	 * Inicializa los hooks relacionados con el formateo de direcciones y estilos.
	 *
	 * Hooks:
	 * - `woocommerce_formatted_address_replacements` para registrar reemplazos
	 *    ({nif}, {email}, {phone}, …) usados en `woocommerce_localisation_address_formats`.
	 * - `woocommerce_store_api_checkout_update_order` para compatibilidad con
	 *    el Checkout de bloques (mantener los reemplazos disponibles).
	 * - `woocommerce_localisation_address_formats` para insertar `{nif}` tras `{company}`.
	 * - `woocommerce_order_formatted_billing_address` para añadir valores al array formateado.
	 * - `woocommerce_order_formatted_shipping_address` idem para envío.
	 * - `wp_enqueue_scripts` para ocultar campos duplicados en "order-received".
	 *
	 * @return void
	 */
	 public function __construct() {
		add_filter( 'woocommerce_formatted_address_replacements', [ $this, 'apg_nif_formato_direccion_de_facturacion' ], 10, 2 );
        add_filter( 'woocommerce_store_api_checkout_update_order', [ $this, 'apg_nif_formato_direccion_de_facturacion' ], 10, 2 );
		add_filter( 'woocommerce_localisation_address_formats', [ $this, 'apg_nif_formato_direccion_localizacion' ], PHP_INT_MAX );
		add_filter( 'woocommerce_order_formatted_billing_address', [ $this, 'apg_nif_anade_campo_nif_direccion' ], 10, 2 );
		add_filter( 'woocommerce_order_formatted_shipping_address', [ $this, 'apg_nif_anade_campo_nif_direccion' ], 10, 2 );    
        add_action( 'wp_enqueue_scripts', [ $this, 'apg_nif_oculta_campo_nif_duplicado' ] );        
    }

	/**
	 * Registra reemplazos para los formatos de dirección.
	 *
	 * Hook principal: `woocommerce_formatted_address_replacements`.
	 * (También se reutiliza como callback en `woocommerce_store_api_checkout_update_order`
	 * por compatibilidad con el Checkout de bloques.)
	 *
	 * Reemplazos añadidos:
	 * - `{nif}`, `{nif_upper}`
	 * - `{email}`, `{email_upper}`
	 * - `{phone}`, `{phone_upper}`
	 *
	 * @param array<string,string>   $campos      Reemplazos existentes.
	 * @param array<string,mixed>    $argumentos  Datos de dirección (billing_/shipping_).
	 * @return array<string,string>  Reemplazos con NIF/email/teléfono incluidos.
	 */
	public function apg_nif_formato_direccion_de_facturacion( $campos, $argumentos ) {
		$campos[ '{nif}' ]            = ( isset( $argumentos[ 'nif' ] ) ) ? $argumentos[ 'nif' ] : '';
		$campos[ '{nif_upper}' ]      = ( isset( $argumentos[ 'nif' ] ) ) ? strtoupper( $argumentos[ 'nif' ] ) : '';
		$campos[ '{phone}' ]          = ( isset( $argumentos[ 'phone' ] ) ) ? $argumentos[ 'phone' ] : '';
		$campos[ '{phone_upper}' ]    = ( isset( $argumentos[ 'phone' ] ) ) ? strtoupper( $argumentos[ 'phone' ] ) : '';
		$campos[ '{email}' ]          = ( isset( $argumentos[ 'email' ] ) ) ? $argumentos[ 'email' ] : '';
		$campos[ '{email_upper}' ]    = ( isset( $argumentos[ 'email' ] ) ) ? strtoupper( $argumentos[ 'email' ] ) : '';

        return $campos;
	}
	
	/**
	 * Inserta `{nif}` debajo de `{company}` en los formatos de dirección.
	 *
	 * Hook: `woocommerce_localisation_address_formats`.
	 *
	 * Nota: se evita tocar el formato en la página de Finalizar compra
	 * y en la de "Gracias" para prevenir conflictos con el Checkout de bloques.
	 *
	 * @param array<string,string> $direccion Mapa país => formato de dirección.
	 * @return array<string,string> Formatos con `{nif}` añadido cuando corresponde.
	 */
	public function apg_nif_formato_direccion_localizacion( $direccion ) {
		global $apg_nif_settings;
        
		// Comprueba si no es la página de Finalizar compra ni la de Gracias (previene problemas con Bloques).
        if ( ! is_page( wc_get_page_id( 'checkout' ) ) || ! empty( is_wc_endpoint_url( 'order-received' ) ) ) {
            foreach ( $direccion as $id => $formato ) {
                $direccion[ $id ] = str_replace( "{company}", "{company}\n{nif}", $formato );
            }
        }

        return $direccion;
	 }

	 /**
	 * Añade NIF, email y teléfono al array de dirección ya formateado del pedido.
	 *
	 * Hooks:
	 * - `woocommerce_order_formatted_billing_address`
	 * - `woocommerce_order_formatted_shipping_address`
	 *
	 * @param array<string,mixed> $campos Array de campos formateados (clave => valor).
	 * @param \WC_Order           $pedido Pedido actual.
	 * @return array<string,mixed> Array con claves `nif`, `email` y `phone` añadidas/actualizadas.
	 */
	public function apg_nif_anade_campo_nif_direccion( $campos, $pedido ) {
        if ( ! is_array( $campos ) ) {
            return $campos;
        }

		// Detecta si es billing o shipping en función del filtro actual.
        $tipo       = strpos( current_filter(), 'billing' ) !== false ? 'billing' : 'shipping';

        $meta_nif   = $pedido->get_meta( "_{$tipo}_nif", true );
        if ( empty( $meta_nif ) ) {
            $meta_nif   = $pedido->get_meta( "_wc_{$tipo}/apg/nif", true );
        }
        $campos['nif']      = $meta_nif;

		// Email y teléfono.
        $campos['email']    = $tipo === 'billing' ? $pedido->get_billing_email() : $pedido->get_meta( "_{$tipo}_email", true );
        $campos['phone']    = $tipo === 'billing' ? $pedido->get_billing_phone() : $pedido->get_shipping_phone();

        return $campos;
	 }
    
	/**
	 * Oculta el listado de "additional fields" duplicados en la página de Gracias,
	 * para evitar mostrar el NIF (y otros) por partida doble con el Checkout de bloques.
	 *
	 * Hook: `wp_enqueue_scripts`.
	 *
	 * @return void
	 */
	public function apg_nif_oculta_campo_nif_duplicado() {
        if ( is_wc_endpoint_url( 'order-received' ) ) {
            wp_register_style( 'apg-nif-hack', false, [], VERSION_apg_nif );
            wp_enqueue_style( 'apg-nif-hack' );
            wp_add_inline_style( 'apg-nif-hack', '.wc-block-components-additional-fields-list { display: none !important; } .woocommerce-customer-details--phone, .woocommerce-customer-details--email { margin: 0; }' );
        }
    }
}

new APG_Campo_NIF_en_Direcciones();
