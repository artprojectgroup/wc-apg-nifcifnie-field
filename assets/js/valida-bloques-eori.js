jQuery(function ($) {
    //Comprueba la lista de países
    var lista = apg_nif_eori_ajax.lista;
    //Valida al inicio
    $('.wc-block-components-address-card__edit').on("click", function () {
        var formulario = $(this).attr('aria-controls');
        if ($('#checkbox-control-0').is(":checked") || (!$('#checkbox-control-0').is(":checked") && formulario == 'billing')) {
            if (lista.includes($('#' + formulario + '-country').val()) == true) {
                ValidaEORI_Bloques(formulario);
            }
        }
    });
    ValidaCampos_EORI();
    
    //Valida al cargarse el formulario de facturación
    $("body").on('DOMNodeInserted', '#billing-fields', function(e) {
        ValidaCampos_EORI();
    });

    //Valida el EORI
    function ValidaEORI_Bloques(formulario) {
        if ( !$("#" + formulario + "-apg-nif").val() || !$("#" + formulario + "-country").val() ) {
            return null;
        }
        var datos = {
            'action': 'apg_nif_valida_EORI',
            'billing_nif': $('#' + formulario + '-apg-nif').val(),
            'billing_country': $('#' + formulario + '-country').val(),
        };
        console.log(datos);
        $.ajax({
            type: "POST",
            url: apg_nif_eori_ajax.url,
            data: datos,
            success: function (response) {
                console.log("WC - APG NIF/CIF/NIE Field - EORI: " + response);
                if (response == 0 && $('#error_eori').length == 0) {
                    $('#' + formulario + ' .wc-block-components-address-form__apg-nif').append('<div id="error_eori"><strong>' + apg_nif_eori_ajax.error + '</strong></div>');
                } else if (response != 0 && $('#error_eori').length) {
                    $('#error_eori').remove();
                }
            },
        });
    }
    
    //Valida al actualizar algún campo
    function ValidaCampos_EORI() {
        $('#billing-apg-nif,#billing-country,#shipping-apg-nif,#shipping-country').on('change', function () {
            var formulario = $(this).closest('.wc-block-components-address-form').attr('id');
            if ($('#checkbox-control-0').is(":checked") || (!$('#checkbox-control-0').is(":checked") && formulario == 'billing')) {
                if (lista.includes($('#' + formulario + '-country').val()) == true) {
                    ValidaEORI_Bloques(formulario);
                } else if ($('#error_eori').length) {
                    $('#error_eori').remove();
                }
            }
        });
    }
});
