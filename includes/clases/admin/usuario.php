<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en Usuarios.
 */
class APG_Campo_NIF_en_Usuarios {
	//Inicializa las acciones de Usuario
	public function __construct() {
		add_filter( 'woocommerce_customer_meta_fields', [ $this, 'apg_nif_anade_campos_administracion_usuarios' ] );
		add_filter( 'woocommerce_user_column_billing_address', [ $this, 'apg_nif_anade_campo_nif_usuario_direccion_facturacion' ], 1, 2 );
		add_filter( 'woocommerce_user_column_shipping_address', [ $this, 'apg_nif_anade_campo_nif_usuario_direccion_envio' ], 1, 2 );
    }

	//Añade el campo CIF/NIF a usuarios
	public function apg_nif_anade_campos_administracion_usuarios( $campos ) {
		global $apg_nif_settings;

        $etiqueta                                               = isset( $apg_nif_settings['etiqueta'] ) && $apg_nif_settings['etiqueta'] ? sanitize_text_field( $apg_nif_settings['etiqueta'] ) : esc_attr__( 'NIF/CIF/NIE', 'wc-apg-nifcifnie-field' );
        $campos[ 'billing' ][ 'fields' ][ 'billing_nif' ]       = [ 
				'label'			=> $etiqueta,
				'description'	=> '',
		];
        $campos[ 'shipping' ][ 'fields' ][ 'shipping_nif' ]     = [ 
				'label'			=> $etiqueta,
				'description'	=> '',
		];
        $campos[ 'shipping' ][ 'fields' ][ 'shipping_email' ]   = [ 
				'label'			=> esc_attr__( 'Email address', 'wc-apg-nifcifnie-field' ),
				'description'	=> '',
		];
        $campos[ 'shipping' ][ 'fields' ][ 'shipping_phone' ]   = [ 
				'label'			=> esc_attr__( 'Phone', 'wc-apg-nifcifnie-field' ),
				'description'	=> '',
        ];

        //Ordena los campos
		$orden_de_campos  = [
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
		
        //Rellena el nuevo listado
		$campos_ordenados[ 'billing' ][ 'title' ]     = $campos[ 'billing' ][ 'title' ];
		$campos_ordenados[ 'shipping' ][ 'title' ]    = $campos[ 'shipping' ][ 'title' ];
		foreach ( $orden_de_campos as $campo ) {
            $billing    = $campo === 'nif' ? 'billing_nif' : 'billing_' . $campo;
            $shipping   = $campo === 'nif' ? 'shipping_nif' : 'shipping_' . $campo;
            
            if ( isset( $campos[ 'billing' ][ 'fields' ][ $billing ] ) ) {
                $campos_ordenados[ 'billing' ][ 'fields' ][ $billing ]    = $campos[ 'billing' ][ 'fields' ][ $billing ];
            }
            
            if ( isset( $campos[ 'shipping' ][ 'fields' ][ $shipping ] ) ) {
                $campos_ordenados[ 'shipping' ][ 'fields' ][ $shipping ]  = $campos[ 'shipping' ][ 'fields' ][ $shipping ];
            }
		}
        foreach ( $campos[ 'billing' ][ 'fields' ] as $campo => $datos ) {
            if ( ! isset( $campos_ordenados[ 'billing' ][ 'fields' ][ $campo ] ) ) {
                $campos_ordenados[ 'billing' ][ 'fields' ][ $campo ] = $datos;
            }
        }
        foreach ( $campos[ 'shipping' ][ 'fields' ] as $campo => $datos ) {
            if ( ! isset( $campos_ordenados[ 'shipping' ][ 'fields' ][ $campo ] ) ) {
                $campos_ordenados[ 'shipping' ][ 'fields' ][ $campo ] = $datos;
            }
        }

        $campos_ordenados = apply_filters( 'wcbcf_customer_meta_fields', $campos_ordenados );

        return $campos_ordenados;
	}
	
	//Añade el NIF a la dirección de facturación
	public function apg_nif_anade_campo_nif_usuario_direccion_facturacion( $campos, $cliente ) {
		$campos[ 'nif' ]      = get_user_meta( $cliente, 'billing_nif', true );
		$campos[ 'phone' ]    = get_user_meta( $cliente, 'billing_phone', true );
		$campos[ 'email' ]    = get_user_meta( $cliente, 'billing_email', true );

        return $campos;
	}
	 
	//Añade el NIF a la dirección de envío
	public function apg_nif_anade_campo_nif_usuario_direccion_envio( $campos, $cliente ) {
		$campos[ 'nif' ]      = get_user_meta( $cliente, 'shipping_nif', true );
		$campos[ 'phone' ]    = get_user_meta( $cliente, 'shipping_phone', true );
		$campos[ 'email' ]    = get_user_meta( $cliente, 'shipping_email', true );
		
		return $campos;
	}
}
new APG_Campo_NIF_en_Usuarios();
