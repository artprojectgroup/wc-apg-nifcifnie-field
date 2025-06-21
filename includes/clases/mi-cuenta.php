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
        add_action( 'wp_enqueue_scripts', [ $this, 'apg_oculta_campo_nif_duplicado' ] );
        add_action( 'woocommerce_after_save_address_validation', [ $this, 'apg_nif_validar_direccion_despues_de_guardar' ], 10, 2 );
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
    public function apg_oculta_campo_nif_duplicado() {
        if ( is_account_page() && is_wc_endpoint_url( 'edit-address' ) ) {
            wp_register_style( 'apg-nif-hack', false, [], VERSION_apg_nif );
            wp_enqueue_style( 'apg-nif-hack' );
            wp_add_inline_style( 'apg-nif-hack', '#apg\\/nif_field { display: none !important; }' );
        }
    }

    //Sincroniza el campo xxx_nif y _wc_xxx/apg/nif
    public function apg_nif_validar_direccion_despues_de_guardar( $address, $load_address ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'woocommerce_after_save_address_validations'
        if ( isset( $_POST[ "_wc_{$load_address}/apg/nif" ] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'woocommerce_after_save_address_validation'
            $_POST[ "{$load_address}_nif" ] = sanitize_text_field( wp_unslash( $_POST[ "_wc_{$load_address}/apg/nif" ] ) );
        }
    }
}
new APG_Campo_NIF_en_Cuenta();
