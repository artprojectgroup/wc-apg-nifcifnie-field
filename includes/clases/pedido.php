<?php
//Igual no deberías poder abrirme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Añade los campos en el Pedido.
 */
class APG_Campo_NIF_en_Pedido {
	//Inicializa las acciones de Pedido
	public function __construct() {
		add_filter( 'woocommerce_default_address_fields', array( $this, 'apg_nif_campos_de_direccion' ) );
		add_filter( 'woocommerce_billing_fields', array( $this, 'apg_nif_formulario_de_facturacion' ) );
		add_filter( 'woocommerce_shipping_fields', array( $this, 'apg_nif_formulario_de_envio' ) );
		$configuracion = get_option( 'apg_nif_settings' );
		if ( isset( $configuracion['validacion'] ) && $configuracion['validacion'] == "1" ) {	
			add_action( 'woocommerce_checkout_process', array( $this, 'apg_nif_validacion_de_campo' ) );
		}
	}

	//Arreglamos la dirección predeterminada
	public function apg_nif_campos_de_direccion( $campos ) {
		$campos['nif']		= array( 
			'label'			=> 'NIF/CIF/NIE',
			'placeholder'	=> _x( 'NIF/CIF/NIE number', 'placeholder', 'apg_nif' ),
		);
		$campos['email']	= array( 
			'label'			=> __( 'Email Address', 'woocommerce' ),
			'required'		=> true,
			'validate'		=> array( 
				'email'
			),
		);
		$campos['phone']	= array( 
			'label'			=> __( 'Phone', 'woocommerce' ),
			'required'		=> true,
		);

		$campos['postcode']['class'][]	= 'update_totals_on_change';
		$campos['state']['class'][]		= 'update_totals_on_change';

		//Ordena los campos
		$orden_de_campos = array(
			"country", 
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
		);
		
		foreach( $orden_de_campos as $campo ) {
			$campos_ordenados[$campo] = $campos[$campo];
		}
		
		return $campos_ordenados;
	}
	
	//Arreglamos el formulario de facturación
	function apg_nif_formulario_de_facturacion( $campos ) {
		$configuracion = get_option( 'apg_nif_settings' );
		
		$campos['billing_nif']['required']	= ( isset( $configuracion['requerido'] ) && $configuracion['requerido'] == "1" ) ? true : false;
		$campos['billing_state']['clear']	= true;
		
		//Ordena los campos
		$orden_de_campos = array(
			"billing_country", 
			"billing_first_name", 
			"billing_last_name", 
			"billing_company", 
			"billing_nif", 
			"billing_email",
			"billing_phone",
			"billing_address_1", 
			"billing_address_2", 
			"billing_postcode", 
			"billing_city",
			"billing_state",
		);
		
		foreach( $orden_de_campos as $campo ) {
			$campos_ordenados[$campo] = $campos[$campo];
		}

		return $campos_ordenados;
	}
	
	//Arreglamos el formulario de envío
	public function apg_nif_formulario_de_envio( $campos ) {
		$configuracion = get_option( 'apg_nif_settings' );
		
		$campos['shipping_nif']['required'] = ( isset( $configuracion['requerido_envio'] ) && $configuracion['requerido_envio'] == "1" ) ? true : false;
		$campos['shipping_state']['clear'] = true;
		
		//Ordena los campos
		$orden_de_campos = array(
			"shipping_country", 
			"shipping_first_name", 
			"shipping_last_name", 
			"shipping_company", 
			"shipping_nif", 
			"shipping_email",
			"shipping_phone",
			"shipping_address_1", 
			"shipping_address_2", 
			"shipping_postcode", 
			"shipping_city",
			"shipping_state",
		);
		
		foreach( $orden_de_campos as $campo ) {
			$campos_ordenados[$campo] = $campos[$campo];
		}
		
		return $campos;
	}

	//Validando el campo NIF/CIF/NIE
	public function apg_nif_validacion( $nif ) {
		$falso = true;

		for ( $i = 0; $i < 9; $i ++ ) {
			$num[$i] = substr( $nif, $i, 1 );
		}
 
		if ( !preg_match( '/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/', $nif ) ) { //No tiene formato válido
			$falso = true;
		}
 
		if ( preg_match( '/(^[0-9]{8}[A-Z]{1}$)/', $nif ) ) {
			if ( $num[8] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( $nif, 0, 8 ) % 23, 1 ) ) { //NIF válido
				$falso = false;
			}
		}
 
		$suma = $num[2] + $num[4] + $num[6];
		for ( $i = 1; $i < 8; $i += 2 ) {
			$suma += substr( ( 2 * $num[$i] ), 0, 1 ) + substr( ( 2 * $num[$i] ), 1, 1 );
		}
		$n = 10 - substr( $suma, strlen( $suma ) - 1, 1 );
 
		if ( preg_match( '/^[KLM]{1}/', $nif ) ) { //NIF especial válido
			if ( $num[8] == chr( 64 + $n ) ) {
				$falso = false;
			}
		}
 
		if ( preg_match( '/^[ABCDEFGHJNPQRSUVW]{1}/', $nif ) && isset ( $num[8] ) ) {
			echo $num[8] ." == ".chr( 64 + $n )." - " . substr( $n, strlen( $n ) - 1, 1 );
			if ( $num[8] == chr( 64 + $n ) || $num[8] == substr( $n, strlen( $n ) - 1, 1 ) ) { //CIF válido
				$falso = false;
			}
		}
 
		if ( preg_match( '/^[T]{1}/', $nif ) ) {
			if ( $num[8] == preg_match( '/^[T]{1}[A-Z0-9]{8}$/', $nif ) ) { //NIE válido (T)
				$falso = false;
			}
		}
 
		if ( preg_match( '/^[XYZ]{1}/', $nif ) ) { //NIE válido (XYZ)
			if ( $num[8] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( str_replace( array( 'X', 'Y', 'Z' ), array( '0', '1', '2' ), $nif ), 0, 8 ) % 23, 1 ) ) {
				$falso = false;
			}
		}
		
		return $falso;
	}
	
	//Validando el campo NIF/CIF/NIE
	public function apg_nif_validacion_de_campo() {
		$facturacion	= true;
		$envio			= true;

		if ( isset( $_POST['billing_nif'] ) && strlen( $_POST['billing_nif'] ) == 9 ) {
			$facturacion = $this->apg_nif_validacion( strtoupper( $_POST['billing_nif'] ) );
		}

		if ( isset( $_POST['shipping_nif'] ) && strlen( $_POST['shipping_nif'] ) == 9 ) {
			$envio = $this->apg_nif_validacion( strtoupper( $_POST['shipping_nif'] ) );
		}
	 
		if ( $facturacion || $envio ) {
			if ( ( $facturacion && !empty( $_POST['billing_nif'] ) ) || ( $envio && !empty( $_POST['shipping_nif'] ) ) ) {
				wc_add_notice( __( 'Please enter a valid NIF/CIF/NIE.', 'apg_nif' ), 'error' );
			}
		}
	}
}
new APG_Campo_NIF_en_Pedido();