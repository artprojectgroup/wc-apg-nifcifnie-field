/**
 * Validates customer identification numbers during classic checkout.
 */
jQuery(function ($) {
    const EU_VIES_COUNTRIES = ['AT','BE','BG','HR','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK','XI'];
    const esUE = (c) => EU_VIES_COUNTRIES.includes((c || '').toUpperCase());

    $('#billing_nif, #billing_country, #shipping_nif, #shipping_country').on('change', function () {
        validarTodo('billing');
        validarTodo('shipping');
    });

    $('#ship-to-different-address-checkbox').on('change', function () {
        if (!$(this).is(':checked')) {
            $('#shipping_country').val('');
        } else {
            const campoEnvio = $('#shipping_country');
            if (!campoEnvio.val()) {
                campoEnvio.val(apg_nif_ajax.pais_base).trigger('change');
            }
        }
        $('body').trigger('update_checkout');
    });

    function validarTodo(tipo) {
        const campoNIF = $(`#${tipo}_nif`);
        const campoPais = $(`#${tipo}_country`);
        const campoEnvio = $(`#shipping_country`);
        const wrapper = $(`#${tipo}_nif_field`);

        if (!campoNIF.length || !campoPais.length) return;

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

        const nif = campoNIF.val().toUpperCase().replace(/[^A-Z0-9-]/g, '');
        campoNIF.val(nif);
        // Si el campo está vacío, no validar ni mostrar errores. Dejamos que WooCommerce
        // se encargue de mostrar el mensaje de "campo obligatorio" si procede.
        if (!nif) {
            $(`#error_vies_${tipo}, #error_eori_${tipo}, #error_vat_${tipo}`).remove();
            $('body').trigger('update_checkout');
            return;
        }
        
        const datos = {
            action: action,
            billing_nif: nif,
            billing_country: campoPais.val(),
            shipping_country: campoEnvio.val(),
            nonce: apg_nif_ajax.nonce,
        };

        $.ajax({
            type: 'POST',
            url: apg_nif_ajax.url,
            data: datos,
            success: function (response) {
                if (window.debug) {
                    console.log(`WC - APG NIF/CIF/NIE Field (${tipo}):`);
                    console.log(response);
                }

                let texto = '';
                let errorID = '';

                if (response.success) {
                    const paisCliente = campoPais.val().toUpperCase();
                    const paisTienda = apg_nif_ajax.pais_base.toUpperCase();
                    const requiereVIES = esUE(paisCliente) && esUE(paisTienda) && paisCliente !== paisTienda;
                    const res = response.data || {};

                    if (action === "apg_nif_valida_VAT") {
                        if (!res.vat_valido) {
                            texto   = apg_nif_ajax.vat_error;
                            errorID = `error_vat_${tipo}`;
                        }
                    } else {
                        if (res.usar_eori && res.valido_eori === false && paisCliente !== paisTienda) {
                            texto   = apg_nif_ajax.eori_error;
                            errorID = `error_eori_${tipo}`;
                        } else if (requiereVIES && res.valido_vies === false) {
                            texto   = res.valido_vies === 44 ? apg_nif_ajax.max_error : apg_nif_ajax.vies_error;
                            errorID = `error_vies_${tipo}`;
                        } else if (!res.vat_valido) {
                            texto   = apg_nif_ajax.vat_error;
                            errorID = `error_vat_${tipo}`;
                        }
                    }                    

                    if (texto && errorID) {
                        wrapper.removeClass('woocommerce-validated')
                               .addClass('woocommerce-invalid woocommerce-invalid-required-field');

                        if (!$('#' + errorID).length) {
                            wrapper.append(
                                '<p id="' + errorID + '" class="checkout-inline-error-message">' +
                                    texto +
                                '</p>'
                            );
                        }
                    }
                }

                if (response.success && (!texto || !errorID)) {
                    wrapper.removeClass('woocommerce-invalid woocommerce-invalid-required-field')
                           .addClass('woocommerce-validated');
                }

                $('body').trigger('update_checkout');
            }
        });
    }
    // Cuando WooCommerce muestra errores al enviar el pedido, vuelve a pintar el mensaje
    // debajo del campo NIF, ya que antes elimina todos los .checkout-inline-error-message.
    $(document.body).on('checkout_error', function () {
        const billingNif  = ($('#billing_nif').val() || '').trim();
        const shippingNif = ($('#shipping_nif').val() || '').trim();

        if (billingNif) {
            validarTodo('billing');
        }
        if (shippingNif) {
            validarTodo('shipping');
        }
    });
});
