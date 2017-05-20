<?php
//Igual no deberías poder abrirme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Añade los campos en el Pedido.
 */
class APG_Campo_NIF_en_Pedido {
	public $nombre_nif = 'NIF/CIF/NIE'; //Nombre original del campo
	
	//Inicializa las acciones de Pedido
	public function __construct() {	
		add_filter( 'woocommerce_default_address_fields', array( $this, 'apg_nif_campos_de_direccion' ) );
		add_filter( 'woocommerce_billing_fields', array( $this, 'apg_nif_formulario_de_facturacion' ) );
		add_filter( 'woocommerce_shipping_fields', array( $this, 'apg_nif_formulario_de_envio' ) );
		$configuracion = get_option( 'apg_nif_settings' );
		//Valida el campo NIF/CIF/NIE
		if ( isset( $configuracion['validacion'] ) && $configuracion['validacion'] == "1" ) {	
			add_action( 'woocommerce_checkout_process', array( $this, 'apg_nif_validacion_de_campo' ) );
		}
		//Añade el número VIES
		if ( isset( $configuracion['validacion_vies'] ) && $configuracion['validacion_vies'] == "1" ) {	
			add_action( 'wp_enqueue_scripts', array( $this, 'apg_nif_carga_ajax' ) );
			add_action( 'wp_ajax_nopriv_apg_nif_valida_VIES', array( $this, 'apg_nif_valida_VIES' ) );
			add_action( 'wp_ajax_apg_nif_valida_VIES', array( $this, 'apg_nif_valida_VIES' ) );
			add_action( 'init', array( $this, 'apg_nif_quita_iva' ) );
			$this->nombre_nif = 'NIF/CIF/NIE/VAT number'; //Nombre modificado del campo
		}
	}

	//Arreglamos la dirección predeterminada
	public function apg_nif_campos_de_direccion( $campos ) {
		$campos['nif']		= array( 
			'label'			=> $this->nombre_nif,
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
		$nif_falso = true;

		for ( $i = 0; $i < 9; $i ++ ) {
			$num[$i] = substr( $nif, $i, 1 );
		}
 
		if ( !preg_match( '/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/', $nif ) ) { //No tiene formato válido
			$nif_falso = true;
		}
 
		if ( preg_match( '/(^[0-9]{8}[A-Z]{1}$)/', $nif ) ) {
			if ( $num[8] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( $nif, 0, 8 ) % 23, 1 ) ) { //NIF válido
				$nif_falso = false;
			}
		}
 
		$suma = $num[2] + $num[4] + $num[6];
		for ( $i = 1; $i < 8; $i += 2 ) {
			$suma += substr( ( 2 * $num[$i] ), 0, 1 ) + substr( ( 2 * $num[$i] ), 1, 1 );
		}
		$n = 10 - substr( $suma, strlen( $suma ) - 1, 1 );
 
		if ( preg_match( '/^[KLM]{1}/', $nif ) ) { //NIF especial válido
			if ( $num[8] == chr( 64 + $n ) ) {
				$nif_falso = false;
			}
		}
 
		if ( preg_match( '/^[ABCDEFGHJNPQRSUVW]{1}/', $nif ) && isset ( $num[8] ) ) {
			if ( $num[8] == chr( 64 + $n ) || $num[8] == substr( $n, strlen( $n ) - 1, 1 ) ) { //CIF válido
				$nif_falso = false;
			}
		}
 
		if ( preg_match( '/^[T]{1}/', $nif ) ) {
			if ( $num[8] == preg_match( '/^[T]{1}[A-Z0-9]{8}$/', $nif ) ) { //NIE válido (T)
				$nif_falso = false;
			}
		}
 
		if ( preg_match( '/^[XYZ]{1}/', $nif ) ) { //NIE válido (XYZ)
			if ( $num[8] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( str_replace( array( 'X', 'Y', 'Z' ), array( '0', '1', '2' ), $nif ), 0, 8 ) % 23, 1 ) ) {
				$nif_falso = false;
			}
		}
		
		return $nif_falso;
	}
	
	//Validando el campo NIF/CIF/NIE
	public function apg_nif_validacion_de_campo() {
		$facturacion	= true;
		$envio			= true;
		
		//Comprobamos si es un número VIES
		$pais	= strtoupper( substr( $_POST['billing_nif'], 0, 2 ) );
		$nif	= substr( $_POST['billing_nif'], 2 );
		if ( $pais == $_POST['billing_country'] || isset( $_SESSION['apg_nif'] ) ) {
			$_POST['billing_nif'] = $nif;
		}

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
	
	//
	public function apg_nif_carga_ajax() {
		wp_enqueue_script( 'apg_nif_vies', plugin_dir_url( DIRECCION_apg_nif ) . '/assets/js/valida-vies.js', array() );
		wp_localize_script( 'apg_nif_vies', 'apg_nif_ajax', admin_url( 'admin-ajax.php' ) );
	}
	
	//Validando el campo VIES
	public function apg_nif_valida_VIES() {
		$_SESSION['apg_nif']	= false;
		$valido					= true;
	
		if ( isset( $_POST['billing_nif'] ) ) {
			$pais	= strtoupper( substr( $_POST['billing_nif'], 0, 2 ) );
			$nif	= substr( $_POST['billing_nif'], 2 );
			if ( $pais == $_POST['billing_country'] ) {
				$validacion = new SoapClient( "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl" );

				if ( $validacion ) {
					$parametros = array(
						'countryCode' => $pais, 
						'vatNumber' => $nif
					);
					try {
						$respuesta = $validacion->checkVat( $parametros );
						if ( $respuesta->valid == true ) {
							$valido = true;
						} else {
							$valido = false;
						}
					} catch( SoapFault $e ) {
						$valido = false;
					}
				} else {
					$valido = false;
				}
				if ( $valido && $_POST['billing_country'] != "ES" ) {
					$_SESSION['apg_nif'] = true;
				}
			}
		}
		
		if ( !$valido ) {
			wc_add_notice( __( 'Please enter a valid VIES VAT number.', 'apg_nif' ), 'error' );
		}
	}
	
	//Quita impuestos a VIES válido
	public function apg_nif_quita_iva( $carro ) {
		if ( is_checkout() || is_cart() || defined( 'WOOCOMMERCE_CHECKOUT' ) || defined( 'WOOCOMMERCE_CART' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			if ( !session_id() ) {
				session_start();
			}
			if ( isset( $_SESSION['apg_nif'] ) ) {
				WC()->customer->set_is_vat_exempt( $_SESSION['apg_nif'] );
			}
		}
	}
}
new APG_Campo_NIF_en_Pedido();