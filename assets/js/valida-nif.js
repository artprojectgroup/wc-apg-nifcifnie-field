jQuery(function ($) {
    validarTodo('billing');
    validarTodo('shipping');

    $('#billing_nif, #billing_country, #shipping_nif, #shipping_country').on('change', function () {
        validarTodo('billing');
        validarTodo('shipping');
    });

    function validarTodo(tipo) {
        const campoNIF = $(`#${tipo}_nif`);
        const campoPais = $(`#${tipo}_country`);
        const wrapper = $(`#${tipo}_nif_field`);

        if (!campoNIF.length || !campoPais.length || !campoNIF.val() || !campoPais.val()) return;

        // Limpia errores previos
        $(`#error_vies_${tipo}, #error_eori_${tipo}, #error_vat_${tipo}`).remove();

        let action = "apg_nif_valida_VAT";
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
            billing_nif: campoNIF.val(), // mismo nombre esperado por el AJAX
            billing_country: campoPais.val(),
            nonce: apg_nif_ajax.nonce,
        };

        $.ajax({
            type: 'POST',
            url: apg_nif_ajax.url,
            data: datos,
            success: function (response) {
                console.log(`WC - APG NIF/CIF/NIE Field (${tipo}):`);
                console.log(response);

                if (response.success) {
                    const res = response.data;
                    let texto = '';
                    let errorID = '';

                    if (action === "apg_nif_valida_VAT" && !res.vat_valido) {
                        texto = apg_nif_ajax.vat_error;
                        errorID = `error_vat_${tipo}`;
                    } else if (res.usar_eori && res.valido_eori === false) {
                        texto = apg_nif_ajax.eori_error;
                        errorID = `error_eori_${tipo}`;
                    } else if (res.usar_vies && res.valido_vies === false) {
                        texto = res.valido_vies === 44 ? apg_nif_ajax.max_error : apg_nif_ajax.vies_error;
                        errorID = `error_vies_${tipo}`;
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