jQuery(function ($) {
    // Valida ambos al inicio
    validarTodo();

    // Valida al cambiar NIF o país
    $('#billing_nif, #billing_country').on('change', function () {
        validarTodo();
    });

    function validarTodo() {
        const nif = $('#billing_nif').val();
        const pais = $('#billing_country').val();

        if (!nif || !pais) {
            return;
        }

        // Limpia errores anteriores
        $('#error_vies, #error_eori').remove();

        let action = "apg_nif_valida_VIES";
        switch (apg_nif_ajax.validacion) {
            case "eori":
                action = "apg_nif_valida_EORI";
                break;
            case "vies":
                action = "apg_nif_valida_VIES";
                break;
        }

        const datos = {
            action: action,
            billing_nif: nif,
            billing_country: pais,
            nonce: apg_nif_ajax.nonce,
        };


        $.ajax({
            type: 'POST',
            url: apg_nif_ajax.url,
            data: datos,
            success: function (response) {
                console.log("WC - APG NIF/CIF/NIE Field:");
                console.log(response);
                if (response.success) {
                    const res = response.data;
                    const wrapper = $('#billing_nif_field');
                    let errorID = '';
                    let texto = '';
                    
                    // Limpia errores previos
                    $('#error_vies, #error_eori, #error_vat').remove();
                    
                    if (action === "apg_nif_valida_VAT" && !res.vat_valido) {
                        texto = apg_nif_ajax.vat_error;
                        errorID = 'error_vat';
                    } else if (res.usar_eori && res.valido_eori === false) {
                        texto = apg_nif_ajax.eori_error;
                        errorID = 'error_eori';
                    } else if (res.usar_vies && res.valido_vies === false) {
                        texto = res.valido_vies === 44 ? apg_nif_ajax.max_error : apg_nif_ajax.vies_error;
                        errorID = 'error_vies';
                    }

                    if (texto && errorID && !$('#' + errorID).length) {
                        wrapper.append('<div id="' + errorID + '"><strong>' + texto + '</strong></div>');
                    }
                }

                $('body').trigger('update_checkout');
            }
        });
    }
});
