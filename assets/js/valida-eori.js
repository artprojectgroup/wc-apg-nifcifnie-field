jQuery( function( $ ) {
    //Comprueba la lista de países
    var lista = apg_nif_eori_ajax.lista;
    if ( lista.includes( $( '#billing_country' ).val() ) == true ) {
        //Valida al inicio
        ValidaEORI();
    }
    //Valida al cambiar
    $( '#billing_nif,#billing_country' ).on( 'change', function() {
        if ( lista.includes( $( '#billing_country' ).val() ) == true ) {
            ValidaEORI();
        } else if ( $( '#error_eori' ).length ) {
            $( '#error_eori' ).remove();
        }
    } );
    
	//Valida el EORI
	function ValidaEORI() {
        var datos = {
            'action'			: 'apg_nif_valida_EORI',
            'billing_nif'		: $( '#billing_nif' ).val(),
            'billing_country'	: $( '#billing_country' ).val(),
        };
        $.ajax( {
            type: "POST",
            url: apg_nif_eori_ajax.url,
            data: datos,
            success: function( response ) {
                console.log( "WC - APG NIF/CIF/NIE Field - EORI: " + response );
                if ( response == 0 && $( '#error_eori' ).length == 0 ) {
                    $( '#billing_nif_field' ).append( '<div id="error_eori"><strong>' + apg_nif_eori_ajax.error + '</strong></div>' );
                } else if ( response != 0 && $( '#error_eori' ).length ) {
                    $( '#error_eori' ).remove();
                }
                $( 'body' ).trigger( 'update_checkout' );
            },
        } );
	}
} );