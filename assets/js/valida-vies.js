jQuery( function( $ ) {
	//Valida al inicio
	ValidaVIES();

	//Valida al cambiar
	$( '#billing_nif,#billing_country' ).on( 'change', function() {
		ValidaVIES();
	} );

	//Valida el VIES
	function ValidaVIES() {
        if ( !$("#billing_nif").val() || !$("#billing_country").val() ) {
            return null;
        }
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
                console.log( "Respuesta VIES:" );
                console.log( response );
                if (response.success && response.data.resultado === false) { //No es válido
                    var texto = apg_nif_ajax.error;
                } else if (response.success && response.data.resultado === 44) { //Error de VIES
                    var texto = apg_nif_ajax.max;
                }
                if ($("#error_vies").length) { //Quita el error
                    $("#error_vies").remove();
                } 
                if ( typeof texto !== 'undefined' ) { //Muestra el error
                    $( '#billing_nif_field' ).append( '<div id="error_vies"><strong>' + texto + '</strong></div>' );
                }
      
                $( 'body' ).trigger( 'update_checkout' );
            },
        } );
	}
} );