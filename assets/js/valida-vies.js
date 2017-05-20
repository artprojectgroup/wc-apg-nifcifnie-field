jQuery( function( $ ) {
	//Valida al inicio
	ValidaVIES();

	//Valida al cambiar
	$( 'input[name="billing_nif"]' ).on( 'change', function() {
		ValidaVIES();
	} );

	//Valida el VIES
	function ValidaVIES() {
		if ( $( 'input[name="billing_nif"]' ).val() != '' ) {
			var datos = {
				'action'			: 'apg_nif_valida_VIES',
				'billing_nif'		: $( '#billing_nif' ).val(),
				'billing_country'	: $( '#billing_country' ).val(),
			};
			$.ajax( {
				type: "POST",
				url: apg_nif_ajax,
				data: datos,
				success: function( response ) {
					$( 'body' ).trigger( 'update_checkout' );
				},
			} );
		} else {
			$( 'body' ).trigger( 'update_checkout' );
		}
	};
} );