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

        $campos[ 'billing' ][ 'fields' ][ 'billing_nif' ]       = [ 
				'label'			=> __( ( isset( $apg_nif_settings[ 'etiqueta' ] ) ? esc_attr( $apg_nif_settings[ 'etiqueta' ] ) : 'NIF/CIF/NIE' ), 'wc-apg-nifcifnie-field' ),
				'description'	=> ''
		];
	 
        $campos[ 'shipping' ][ 'fields' ][ 'shipping_nif' ]     = [ 
				'label'			=> __( ( isset( $apg_nif_settings[ 'etiqueta' ] ) ? esc_attr( $apg_nif_settings[ 'etiqueta' ] ) : 'NIF/CIF/NIE' ), 'wc-apg-nifcifnie-field' ),
				'description'	=> ''
		];
        $campos[ 'shipping' ][ 'fields' ][ 'shipping_email' ]   = [ 
				'label'			=> __( 'Email', 'woocommerce' ),
				'description'	=> ''
		];
        $campos[ 'shipping' ][ 'fields' ][ 'shipping_phone' ]   = [ 
				'label'			=> __( 'Telephone', 'woocommerce' ),
				'description'	=> ''
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
		
		$campos_ordenados[ 'billing' ][ 'title' ]     = $campos[ 'billing' ][ 'title' ];
		$campos_ordenados[ 'shipping' ][ 'title' ]    = $campos[ 'shipping' ][ 'title' ];
		foreach( $orden_de_campos as $campo ) {
			$campos_ordenados[ 'billing' ][ 'fields' ][ 'billing_' . $campo ]    = $campos[ 'billing' ][ 'fields' ][ 'billing_' . $campo ];
			$campos_ordenados[ 'shipping' ][ 'fields' ][ 'shipping_' . $campo ]  = $campos[ 'shipping' ][ 'fields' ][ 'shipping_' . $campo ];
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
