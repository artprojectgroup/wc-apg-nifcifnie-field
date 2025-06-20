<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en Panel de Administración - Pedidos.
 */
class APG_Campo_NIF_en_Admin_Pedidos {
	//Inicializa las acciones de Pedido
	public function __construct() {
        add_filter( 'woocommerce_shop_order_search_fields', [ $this, 'apg_nif_anade_campo_nif_busqueda' ] );
		add_filter( 'woocommerce_admin_billing_fields', [ $this, 'apg_nif_anade_campo_nif_editar_direccion_pedido' ] );
		add_filter( 'woocommerce_admin_shipping_fields', [ $this, 'apg_nif_anade_campo_nif_editar_direccion_pedido' ] );
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) { 
			add_filter( 'woocommerce_found_customer_details', [ $this, 'apg_nif_ajax' ] );
      	} else { 
        	add_filter( 'woocommerce_ajax_get_customer_details', [ $this, 'apg_dame_nif_ajax' ], 10, 2 ); 
      	} 
		add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'apg_nif_carga_hoja_de_estilo_editar_direccion_pedido' ] );
	}
	
    //Añade el NIF en las búsquedas de pedidos
    public function apg_nif_anade_campo_nif_busqueda( $search_fields ) { 
        $search_fields[]    = 'billing_nif';
        $search_fields[]    = 'shipping_nif';
        
        return $search_fields;
    }

	//Añade el campo NIF a Detalles del pedido
	public function apg_nif_anade_campo_nif_editar_direccion_pedido( $campos ) {
		global $apg_nif_settings;

        $etiqueta           = isset( $apg_nif_settings[ 'etiqueta' ] ) && $apg_nif_settings[ 'etiqueta' ] ? sanitize_text_field( $apg_nif_settings[ 'etiqueta' ] ) : esc_attr__( 'NIF/CIF/NIE', 'wc-apg-nifcifnie-field' );
        $campos[ 'nif' ]    = [ 
			'label'	=> $etiqueta,
			'show'	=> false
		];
        $campos[ 'phone' ]  = [ 
			'label'	=> esc_attr__( 'Phone', 'wc-apg-nifcifnie-field' ),
			'show'	=> true
		];
        $campos[ 'email' ]  = [ 
			'label'	=> esc_attr__( 'Email address', 'wc-apg-nifcifnie-field' ),
			'show'	=> true
		];

		//Ordena los campos
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
        		
        foreach ( $orden_de_campos as $campo ) {
            if ( isset( $campos[ $campo ] ) ) {
                $campos_ordenados[ $campo ] = $campos[ $campo ];
            }
        }

        foreach ( $campos as $campo => $datos ) {
            if ( ! isset( $campos_ordenados[ $campo ] ) && $datos[ 'label' ] != $etiqueta ) {
                $campos_ordenados[ $campo ] = $datos;
            }
        }

        return $campos_ordenados;
	}

	//Carga el campo NIF en los pedidos creados manualmente
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
	public function apg_dame_nif_ajax( $datos_cliente, $cliente ) { 
		$datos_cliente[ 'billing' ][ 'nif' ]  = $cliente->get_meta( 'billing_nif', true );
		$datos_cliente[ 'shipping' ][ 'nif' ] = $cliente->get_meta( 'shipping_nif', true );
 
		return $datos_cliente; 
	} 

	//Carga hoja de estilo personalizada a Detalles del pedido
	public function apg_nif_carga_hoja_de_estilo_editar_direccion_pedido( $pedido ) {
		echo '</pre>
	<style type="text/css"><!-- #order_data .order_data_column ._billing_company_field, #order_data .order_data_column ._shipping_company_field, #order_data .order_data_column ._billing_phone_field { float: left; margin: 9px 0 0; padding: 0; width: 48%; } #order_data .order_data_column ._billing_nif_field, #order_data .order_data_column ._shipping_nif_field { float: right; margin: 9px 0 0; padding: 0; width: 48%; } --></style>
	<pre>';
	}
}
new APG_Campo_NIF_en_Admin_Pedidos();
