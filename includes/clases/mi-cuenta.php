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

            foreach( $orden_de_campos as $campo ) {
                $campos_ordenados[$campo] = $campos[$campo];
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
    
    //Elimina el campo duplicado en el formulario de Mi cuenta
    public function apg_nif_anade_campo_nif_formulario_direccion( $address, $load_address ) {
        global $apg_nif_settings;
        
        if ( ! has_block( 'woocommerce/checkout', wc_get_page_id( 'checkout' ) ) ) {
            unset ( $address[ '_wc_' . $load_address . '/apg/nif' ] );
        } else {
            unset ( $address[ $load_address . '_nif' ] );            
            $address[ '_wc_' . $load_address . '/apg/nif' ][ 'priority' ] = ( isset( $apg_nif_settings[ 'prioridad' ] ) ? esc_attr( $apg_nif_settings[ 'prioridad' ] ) : 31 ); //Prioridad del campo
        }

        return $address;
    }
}
new APG_Campo_NIF_en_Cuenta();
