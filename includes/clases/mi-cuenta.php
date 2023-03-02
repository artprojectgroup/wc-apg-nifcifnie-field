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
	}
	
	//Añade el campo NIF a Editar mi dirección
	function apg_nif_anade_campo_nif_editar_direccion( $campos, $cliente, $formulario ) {
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

		return $campos_ordenados;
	}
}
new APG_Campo_NIF_en_Cuenta();
