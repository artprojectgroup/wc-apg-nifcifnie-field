<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en Mi Cuenta.
 */
class APG_Campo_NIF_en_Cuenta {
	//Inicializa las acciones de Mi Cuenta
	public function __construct() {
		add_filter( 'woocommerce_my_account_my_address_formatted_address', [ $this, 'apg_nif_anade_campo_nif_editar_direccion' ], 10, 3 );
        add_filter( 'woocommerce_address_to_edit', [ $this, 'apg_nif_anade_campo_nif_formulario_direccion' ], 99, 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'apg_nif_oculta_campo_nif_duplicado' ] );
        add_action( 'woocommerce_customer_save_address', [ $this, 'apg_nif_guardar_nif_en_mi_cuenta' ], 10, 4 );
    }
    
	//Añade el campo NIF a Editar mi dirección
	public function apg_nif_anade_campo_nif_editar_direccion( $campos, $cliente, $formulario ) {
        if ( ! has_action( 'woocommerce_my_account_after_my_address' ) ) {
            $campos[ 'nif' ]      = get_user_meta( $cliente, $formulario . '_nif', true );
            $campos[ 'email' ]    = get_user_meta( $cliente, $formulario . '_email', true );
            $campos[ 'phone' ]    = get_user_meta( $cliente, $formulario . '_phone', true );

            //Ordena los campos
            $orden_de_campos      = [
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
                if ( ! isset( $campos_ordenados[ $campo ] ) ) {
                    $campos_ordenados[ $campo ] = $datos;
                }
            }

            return $campos_ordenados;
        }

        return $campos;
	}
    
    //Quita el campo duplicado requerido formulario de Mi cuenta
    public function apg_nif_anade_campo_nif_formulario_direccion( $address, $load_address ) {
        global $apg_nif_settings;

        $address[ '_wc_' . $load_address . '/apg/nif' ][ 'required' ] = false;

        return $address;
    }

    //Oculta el campo duplicado en el formulario de direcciones de Mi cuenta
    public function apg_nif_oculta_campo_nif_duplicado() {
        if ( is_account_page() && is_wc_endpoint_url( 'edit-address' ) ) {
            wp_register_style( 'apg-nif-hack', false, [], VERSION_apg_nif );
            wp_enqueue_style( 'apg-nif-hack' );
            wp_add_inline_style( 'apg-nif-hack', '#apg\\/nif_field { display: none !important; }' );
        }
    }

    //Sincroniza el campo xxx_nif y _wc_xxx/apg/nif
    public function apg_nif_guardar_nif_en_mi_cuenta( $user_id, $address_type ) {
        $contador_argmunentos   = func_num_args();
        $argmunentos            = func_get_args();

        $campo_origen           = "{$address_type}_nif";
        $campo_destino          = "_wc_{$address_type}/apg/nif";

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'woocommerce_customer_save_address'
        if ( isset( $_POST[ $campo_origen ] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'woocommerce_customer_save_address'
            $valor  = sanitize_text_field( wp_unslash( $_POST[ $campo_origen ] ) );
            
            if ( $contador_argmunentos === 4 && isset( $argmunentos[ 3 ] ) && is_object( $argmunentos[ 3 ] ) ) {
                // Caso backend: tenemos el objeto WC_Customer
                $customer   = $argmunentos[ 3 ];
                $customer->update_meta_data( $campo_origen, $valor );
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'woocommerce_customer_save_address'
                if ( isset( $_POST[ $campo_destino ] ) ) {
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'woocommerce_customer_save_address'
                    $_POST[ $campo_destino ]    = $valor;
                    $customer->update_meta_data( $campo_origen, $valor );
                }
                $customer->save();
            } else {
                update_user_meta( $user_id, $campo_origen, $valor );
                update_user_meta( $user_id, $campo_destino, $valor );
            }
        }
    }
}
new APG_Campo_NIF_en_Cuenta();
