jQuery(function ($) {
    //Valida al inicio
    $('.wc-block-components-address-card__edit').on("click", function () {
        if ($('#checkbox-control-0').is(":checked") || (!$('#checkbox-control-0').is(":checked") && $(this).attr('aria-controls') == 'billing')) {
            ValidaVIES_Bloques($(this).attr('aria-controls'));
        }
    });

    //Valida al actualizar algún campo
    $('#billing-apg-nif,#billing-country,#shipping-apg-nif,#shipping-country').on('change', function () {
        if ($('#checkbox-control-0').is(":checked") || (!$('#checkbox-control-0').is(":checked") && $(this).closest('.wc-block-components-address-form').attr('id') == 'billing')) {
            ValidaVIES_Bloques($(this).closest('.wc-block-components-address-form').attr('id'));
        }
    });

    //Valida el VIES
    function ValidaVIES_Bloques(formulario) {
        var datos = {
            'action': 'apg_nif_valida_VIES',
            'billing_nif': $('#' + formulario + '-apg-nif').val(),
            'billing_country': $('#' + formulario + '-country').val(),
        };
        console.log(datos);
        $.ajax({
            type: "POST",
            url: apg_nif_ajax.url,
            data: datos,
            success: function (response) {
                console.log("WC - APG NIF/CIF/NIE Field: " + response);
                if (response == 0 && $('#error_vies').length == 0) {
                    $('#' + formulario + ' .wc-block-components-address-form__apg-nif').append('<div id="error_vies"><strong>' + apg_nif_ajax.error + '</strong></div>');
                } else if (response != 0 && $('#error_vies').length) {
                    $('#error_vies').remove();
                }
            },
        });
    }
});
