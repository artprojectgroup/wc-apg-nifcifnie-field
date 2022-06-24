jQuery( function( $ ) {
	//Valida al inicio
	ValidaVIES();

	//Valida al cambiar
	$( '#billing_nif,#billing_country' ).on( 'change', function() {
		ValidaVIES();
	} );

	//Valida el VIES
	function ValidaVIES() {
        var datos = {
            'action'			: 'apg_nif_valida_VIES',
            'billing_nif'		: $( '#billing_nif' ).val(),
            'billing_country'	: $( '#billing_country' ).val(),
        };
        $.ajax( {
            type: "POST",
            url: apg_nif_ajax.url,
            data: datos,
            success: function( response ) {
                console.log( "WC - APG NIF/CIF/NIE Field: " + response );
                if ( response == 0 && $( '#error_vies' ).length == 0 ) {
                    $( '#billing_nif_field' ).append( '<div id="error_vies"><strong>' + apg_nif_ajax.error + '</strong></div>' );
                } else if ( response != 0 && $( '#error_vies' ).length ) {
                    $( '#error_vies' ).remove();
                }
                $( 'body' ).trigger( 'update_checkout' );
            },
        } );
	}
} );