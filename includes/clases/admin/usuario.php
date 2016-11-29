<?php
//Igual no deberías poder abrirme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Añade los campos en Usuarios.
 */
class APG_Campo_NIF_en_Usuarios {
	//Inicializa las acciones de Usuario
	public function __construct() {
		add_filter( 'woocommerce_customer_meta_fields', array( $this, 'apg_nif_anade_campos_administracion_usuarios' ) );
		add_filter( 'woocommerce_user_column_billing_address', array( $this, 'apg_nif_anade_campo_nif_usuario_direccion_facturacion' ), 1, 2 );
		add_filter( 'woocommerce_user_column_shipping_address', array( $this, 'apg_nif_anade_campo_nif_usuario_direccion_envio' ), 1, 2 );
	}

	//Añade el campo CIF/NIF a usuarios
	public function apg_nif_anade_campos_administracion_usuarios( $campos ) {
		$campos['billing']['fields']['billing_nif']		= array( 
				'label'			=> 'NIF/CIF/NIE',
				'description'	=> ''
		);
	 
		$campos['shipping']['fields']['shipping_nif']	= array( 
				'label'			=> 'NIF/CIF/NIE',
				'description'	=> ''
		);
		$campos['shipping']['fields']['shipping_email']	= array( 
				'label'			=> __( 'Email', 'woocommerce' ),
				'description'	=> ''
		);
		$campos['shipping']['fields']['shipping_phone']	= array( 
				'label'			=> __( 'Telephone', 'woocommerce' ),
				'description'	=> ''
		);
	 
		//Ordena los campos
		$orden_de_campos = array(
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
		);
		
		$campos_ordenados['billing']['title']	= $campos['billing']['title'];
		$campos_ordenados['shipping']['title']	= $campos['shipping']['title'];
		foreach( $orden_de_campos as $campo ) {
			$campos_ordenados['billing']['fields']['billing_' . $campo]		= $campos['billing']['fields']['billing_' . $campo];
			$campos_ordenados['shipping']['fields']['shipping_' . $campo]	= $campos['shipping']['fields']['shipping_' . $campo];
		}
		
		$campos_ordenados = apply_filters( 'wcbcf_customer_meta_fields', $campos_ordenados );
		 
		return $campos_ordenados;
	}
	
	//Añadimos el NIF a la dirección de facturación y envío
	public function apg_nif_anade_campo_nif_usuario_direccion_facturacion( $campos, $usuario ) {
		$campos['nif']		= get_user_meta( $usuario, 'billing_nif', true );
		$campos['phone']	= get_user_meta( $usuario, 'billing_phone', true );
		$campos['email']	= get_user_meta( $usuario, 'billing_email', true );
		
		return $campos;
	}
	 
	//Añadimos el NIF a la dirección de envío
	function apg_nif_anade_campo_nif_usuario_direccion_envio( $campos, $usuario ) {
		$campos['nif']		= get_user_meta( $usuario, 'shipping_nif', true );
		$campos['phone']	= get_user_meta( $usuario, 'shipping_phone', true );
		$campos['email']	= get_user_meta( $usuario, 'shipping_email', true );
		
		return $campos;
	}
}
new APG_Campo_NIF_en_Usuarios();