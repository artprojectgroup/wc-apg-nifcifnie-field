<?php
/**
 * Campos NIF/CIF/NIE en el Panel de Administración de Pedidos de WooCommerce.
 *
 * - Permite buscar pedidos por NIF (facturación y envío).
 * - Añade el campo NIF (y reordena teléfono/email) en los metaboxes de
 *   dirección de facturación y envío al editar un pedido.
 * - Completa los datos de NIF en pedidos creados manualmente vía AJAX
 *   (compatibilidad con versiones antiguas y nuevas de WooCommerce).
 * - Inyecta una pequeña hoja de estilos para la maquetación de campos.
 *
 * @package WC_APG_NIFCIFNIE_Field
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

/**
 * Añade y gestiona campos NIF en la administración de Pedidos.
 */
class APG_Campo_NIF_en_Admin_Pedidos {

	/**
	 * Inicializa los hooks relacionados con la edición/listado de pedidos.
	 *
	 * Hooks:
	 * - `woocommerce_shop_order_search_fields` para incluir claves de metadatos en la búsqueda.
	 * - `woocommerce_admin_billing_fields` para añadir/ordenar campos en la dirección de facturación.
	 * - `woocommerce_admin_shipping_fields` para añadir/ordenar campos en la dirección de envío.
	 * - `woocommerce_found_customer_details` (WC < 2.7) para cargar NIF vía AJAX.
	 * - `woocommerce_ajax_get_customer_details` (WC >= 2.7) para cargar NIF vía AJAX.
	 * - `admin_enqueue_scripts` para inyectar CSS en la edición de pedidos (clásico y HPOS).
	 *
	 * @return void
	 */
	public function __construct() {
        add_filter( 'woocommerce_shop_order_search_fields', [ $this, 'apg_nif_anade_campo_nif_busqueda' ] );
		add_filter( 'woocommerce_admin_billing_fields', [ $this, 'apg_nif_anade_campo_nif_editar_direccion_pedido' ], 10, 3 );
		add_filter( 'woocommerce_admin_shipping_fields', [ $this, 'apg_nif_anade_campo_nif_editar_direccion_pedido' ], 10, 3 );
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) { 
			add_filter( 'woocommerce_found_customer_details', [ $this, 'apg_nif_ajax' ] );
      	} else { 
        	add_filter( 'woocommerce_ajax_get_customer_details', [ $this, 'apg_dame_nif_ajax' ], 10, 2 ); 
      	} 
		add_action( 'admin_enqueue_scripts', [ $this, 'apg_nif_carga_hoja_de_estilo_editar_direccion_pedido' ] );
	}
	
    /**
	 * Añade los metadatos NIF a los campos de búsqueda de pedidos.
	 *
	 * Hook: `woocommerce_shop_order_search_fields`.
	 *
	 * @param string[] $search_fields Lista de meta-keys por las que buscar pedidos.
	 * @return string[] Lista con `billing_nif` y `shipping_nif` añadidos.
	 */
    public function apg_nif_anade_campo_nif_busqueda( $search_fields ) { 
        $search_fields[]    = 'billing_nif';
        $search_fields[]    = 'shipping_nif';
        
        return $search_fields;
    }

	/**
	 * Añade el campo NIF y reordena campos visibles en la edición de dirección
	 * (facturación o envío) dentro del pedido en el panel de administración.
	 *
	 * Hooks:
	 * - `woocommerce_admin_billing_fields`
	 * - `woocommerce_admin_shipping_fields`
	 *
	 * @global array<string,mixed> $apg_nif_settings Ajustes del plugin (p. ej. etiqueta del campo).
	 *
     * @param array<string,array<string,mixed>> $campos  Conjunto de campos actuales mostrados en el metabox
     *                                                   (clave => definición), antes de la reordenación.
     * @param WC_Order|mixed                      $order   Pedido actual. Si está disponible y es instancia de
     *                                                   WC_Order, se utilizarán los metadatos `billing_nif` o
     *                                                   `shipping_nif` según el hook en ejecución.
     * @param string                              $context Contexto de los campos: suele ser 'billing' o 'shipping',
	 * @return array<string,array<string,mixed>> Conjunto de campos reordenado, con NIF añadido.
	 */
	public function apg_nif_anade_campo_nif_editar_direccion_pedido( $campos, $order, $context ) {
		global $apg_nif_settings;

		$etiqueta           = isset( $apg_nif_settings[ 'etiqueta' ] ) && $apg_nif_settings[ 'etiqueta' ] ? sanitize_text_field( $apg_nif_settings[ 'etiqueta' ] ) : esc_attr__( 'NIF/CIF/NIE', 'wc-apg-nifcifnie-field' );
		$campos[ 'nif' ]    = [
				'label' => $etiqueta,
				'show'  => false,
		];

		if ( $order instanceof WC_Order ) {
			if ( 'woocommerce_admin_shipping_fields' === current_filter() ) {
				$campos[ 'nif' ][ 'value' ] = $order->get_meta( 'shipping_nif' );
			} else {
				$campos[ 'nif' ][ 'value' ] = $order->get_meta( 'billing_nif' );
			}
		}
		$campos[ 'phone' ]  = [
				'label' => esc_attr__( 'Phone', 'wc-apg-nifcifnie-field' ),
				'show'  => true
		];
		$campos[ 'email' ]  = [
				'label' => esc_attr__( 'Email address', 'wc-apg-nifcifnie-field' ),
				'show'  => true
		];

		// Orden recomendado de campos.
		$orden_de_campos = [
			"first_name",
			"last_name",
			"company",
			"nif",
			"email",
			"phone",
			"address_1",
			"address_2",
			"postcode",
			"city",
			"state",
			"country",
		];
        		
		$campos_ordenados = [];

		foreach ( $orden_de_campos as $campo ) {
			if ( isset( $campos[ $campo ] ) ) {
				$campos_ordenados[ $campo ] = $campos[ $campo ];
			}
		}

		// Asegura que no se pierda ningún campo no contemplado en el orden anterior.
		foreach ( $campos as $campo => $datos ) {
			if ( ! isset( $campos_ordenados[ $campo ] ) && $datos[ 'label' ] != $etiqueta ) {
				$campos_ordenados[ $campo ] = $datos;
			}
		}

        return $campos_ordenados;
	}

	/**
	 * (Compatibilidad WC < 2.7) Carga el NIF del usuario vía AJAX
	 * al crear/editar un pedido manualmente en el panel de administración.
	 *
	 * Hook: `woocommerce_found_customer_details`.
	 *
	 * @param array<string,mixed> $datos_cliente Datos de cliente devueltos por la llamada AJAX.
	 * @return array<string,mixed> Datos con `billing_nif`/`shipping_nif` añadidos cuando corresponda.
	 */
	public function apg_nif_ajax( $datos_cliente ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        if ( isset( $_POST[ 'user_id' ], $_POST[ 'type_to_load' ] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
            $cliente    = absint( wp_unslash( $_POST[ 'user_id' ] ) );
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
            $formulario = sanitize_text_field( wp_unslash( $_POST[ 'type_to_load' ] ) );
            
            if ( $cliente && in_array( $formulario, [ 'billing', 'shipping' ], true ) ) {
                $datos_cliente[ $formulario . '_nif' ]  = get_user_meta( $cliente, $formulario . '_nif', true );
            }
        }

		return $datos_cliente;
	}
	
	/**
	 * (WC >= 2.7) Añade el NIF del cliente a la respuesta AJAX
	 * al seleccionar un usuario al crear/editar un pedido.
	 *
	 * Hook: `woocommerce_ajax_get_customer_details`.
	 *
	 * @param array<string,mixed> $datos_cliente Datos de cliente a devolver (estructurados por 'billing' y 'shipping').
	 * @param \WC_Customer        $cliente       Objeto de cliente seleccionado.
	 * @return array<string,mixed> Datos con `nif` en secciones de 'billing' y 'shipping'.
	 */
	public function apg_dame_nif_ajax( $datos_cliente, $cliente ) { 
		$datos_cliente[ 'billing' ][ 'nif' ]  = $cliente->get_meta( 'billing_nif', true );
		$datos_cliente[ 'shipping' ][ 'nif' ] = $cliente->get_meta( 'shipping_nif', true );
 
		return $datos_cliente; 
	} 

	/**
	 * Encola CSS inline para maquetar los campos en la pantalla de edición de pedidos
	 * (compatible con la UI clásica y HPOS).
	 *
	 * Hook: `admin_enqueue_scripts`.
	 *
	 * @param string $hook Sufijo del hook de la pantalla actual en el admin.
	 * @return void
	 */
	public function apg_nif_carga_hoja_de_estilo_editar_direccion_pedido( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return;
		}

		$is_classic_order_edit	= in_array( $screen->base, [ 'post', 'post-new' ], true ) && 'shop_order' === $screen->post_type;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only access to 'action' for UI/view logic; value is sanitized and not used to change state or process form data.
		$is_hpos_order_edit		= 'woocommerce_page_wc-orders' === $screen->id && isset( $_GET[ 'action' ] ) && 'edit' === sanitize_key( wp_unslash( $_GET[ 'action' ] ) );
		if ( ! ( $is_classic_order_edit || $is_hpos_order_edit ) ) {
			return;
		}

		$css = '/* Cuando el inline style es "display:block", usa grid */
				#order_data .order_data_column .edit_address[style*="display:block"],
				#order_data .order_data_column .edit_address[style*="display: block"] {
				  display: grid !important;
				  grid-template-columns: 1fr 1fr;
				  column-gap: 4%;
				}

				/* Alinear el input al fondo del <p> (label arriba, control abajo) */
				#order_data .order_data_column .edit_address[style*="display:block"] .form-field,
				#order_data .order_data_column .edit_address[style*="display: block"] .form-field {
				  display: flex;
				  flex-direction: column;
				  justify-content: flex-end;
				  margin: 9px 0 0;
				  padding: 0;
				  box-sizing: border-box;
				  width: 100%;
				}

				/* Los campos "anchos" (wide) ocupan las 2 columnas */
				#order_data .order_data_column .edit_address[style*="display:block"] .form-field-wide,
				#order_data .order_data_column .edit_address[style*="display: block"] .form-field-wide {
				  grid-column: 1 / -1;
				}

				/* Responsive */
				@media (max-width: 782px) {
				  #order_data .order_data_column .edit_address[style*="display:block"],
				  #order_data .order_data_column .edit_address[style*="display: block"] {
					grid-template-columns: 1fr;
					column-gap: 0;
				  }
				}';

		// Preferimos colgarnos del handle de WooCommerce si está encolado.
		if ( wp_style_is( 'woocommerce_admin_styles', 'enqueued' ) ) {
			wp_add_inline_style( 'woocommerce_admin_styles', $css );
		} else {
			wp_register_style( 'apg-nif-admin-inline', false, [], VERSION_apg_nif, 'all' );
			wp_enqueue_style( 'apg-nif-admin-inline' );
			wp_add_inline_style( 'apg-nif-admin-inline', $css );
		}
	}
}

new APG_Campo_NIF_en_Admin_Pedidos();
