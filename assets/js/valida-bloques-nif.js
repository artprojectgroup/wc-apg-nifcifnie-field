jQuery(document).ready(function ($) {
    const { extensionCartUpdate, validation } = wc.blocksCheckout || {};

    // Estado para evitar validaciones múltiples por cambio de focus o carga
    const yaValidado = {
        billing: false,
        shipping: false
    };

    function validarNIFyMostrarErrores(formulario) {
        if (yaValidado[formulario]) return;

        const campoNIF = $("#" + formulario + "-apg-nif");
        const campoPais = $("#" + formulario + "-country");
        const campoEnvio = $("#shipping-country");
        
        if (!campoNIF.length || !campoPais.length || !campoNIF.val() || !campoPais.val()) return;

        let action = "apg_nif_valida_VAT";
        switch (apg_nif_ajax.validacion) {
            case "eori": action = "apg_nif_valida_EORI"; break;
            case "vies": action = "apg_nif_valida_VIES"; break;
        }

        const datos = {
            action: action,
            billing_nif: campoNIF.val(),
            billing_country: campoPais.val(),
            shipping_country: campoEnvio.val(),
            nonce: apg_nif_ajax.nonce,
        };

        $(".wp-block-woocommerce-checkout-totals-block").block({ message: null, overlayCSS: { background: "#fff", opacity: 0.6 } });
        yaValidado[formulario] = true;

        $.ajax({
            type: "POST",
            url: apg_nif_ajax.url,
            data: datos,
            success: function (response) {
                const wrapper = $("#" + formulario + " .wc-block-components-address-form__apg-nif");
                const errorID = "error_nif";
                console.log("WC - APG NIF/CIF/NIE Field (" + formulario + "):");
                console.log(response);

                $("#" + errorID).remove();
                wrapper.attr("aria-invalid", "false").closest(".wc-block-components-text-input").removeClass("has-error");

                if (action === "apg_nif_valida_VAT") {
                    if (!response.data?.vat_valido) {
                        wrapper.append(`<div id="${errorID}"><strong>${apg_nif_ajax.vat_error}</strong></div>`);
                        wrapper.attr("aria-invalid", "true").closest(".wc-block-components-text-input").addClass("has-error");
                        validation?.addError?.({ field: formulario + "-apg-nif", message: apg_nif_ajax.vat_error });
                    } else {
                        validation?.removeError?.(formulario + "-apg-nif");
                    }

                    $(".wp-block-woocommerce-checkout-totals-block").unblock();
                    return;
                }

                if (response.success) {
                    const paisCliente = campoPais.val().toUpperCase();
                    const paisTienda = apg_nif_ajax.pais_base.toUpperCase();
                    const res = response.data;
                    let texto = "";
                    let hay_error = false;

                    if (res.usar_eori && res.valido_eori === false && paisCliente !== paisTienda) {
                        texto = apg_nif_ajax.eori_error;
                        hay_error = true;
                    } else if (res.valido_vies === false && paisCliente !== paisTienda) {
                        texto = res.valido_vies === 44 ? apg_nif_ajax.max_error : apg_nif_ajax.vies_error;
                        hay_error = true;
                    } else if (!res.vat_valido) {
                        texto = apg_nif_ajax.vat_error;
                        hay_error = true;
                    }

                    if (hay_error) {
                        wrapper.append(`<div id="${errorID}"><strong>${texto}</strong></div>`);
                        wrapper.attr("aria-invalid", "true").closest(".wc-block-components-text-input").addClass("has-error");
                        validation?.addError?.({ field: formulario + "-apg-nif", message: texto });
                    } else {
                        $("#" + errorID).remove();
                        wrapper.attr("aria-invalid", "false").closest(".wc-block-components-text-input").removeClass("has-error");
                        validation?.removeError?.(formulario + "-apg-nif");

                        const event = new CustomEvent('checkout-blocks-validation-reset', {
                            detail: { field: formulario + "-apg-nif" }
                        });
                        document.dispatchEvent(event);
                        document.querySelector('.wc-block-components-notice-banner__dismiss')?.click();
                    }
                    
                    const validoVIES = res.valido_vies === true || res.valido_vies === '1';
                    const exento = (validoVIES && res.es_exento) ? '1' : '0';
        
                    fetch('/?wc-ajax=apg_nif_quita_iva_bloques', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'exento=' + exento,
                    })
                    .then(r => r.json())
                    .then(data => {
                        console.log('VAT exception:', data);

                        // Solo actualiza totales si la petición ha tenido éxito
                        if (data.success) {
                            extensionCartUpdate?.({ namespace: "apg_nif_valida_vies" }).finally(() => {
                                if (window.wp?.data) {
                                    window.wp.data.dispatch("wc/store/cart").invalidateResolution("getCartTotals");
                                    window.wp.data.dispatch("wc/store/cart").invalidateResolution("getShippingRates");
                                }
                                $(".wp-block-woocommerce-checkout-totals-block").unblock();
                            });
                        } else {
                            $(".wp-block-woocommerce-checkout-totals-block").unblock();
                        }
                    });
                }
            },
        });
    }

    // Eventos para detectar cambio en campos de billing y shipping
    $(document).on("change", "#billing-apg-nif, #billing-country", function () {
        yaValidado.billing = false;
        validarNIFyMostrarErrores("billing");
    });

    $(document).on("change", "#shipping-apg-nif, #shipping-country", function () {
        yaValidado.shipping = false;
        validarNIFyMostrarErrores("shipping");
    });

    // Valida al hacer clic en editar dirección
    $(document).on("click", ".wc-block-components-address-card__edit", function () {
        const form = $(this).attr("aria-controls");
        yaValidado.billing = false;
        yaValidado.shipping = false;
        validarNIFyMostrarErrores(form);
    });

    // Valida al insertar el bloque de facturación
    $("body").on("DOMNodeInserted", "#billing-fields", function () {
        yaValidado.billing = false;
        validarNIFyMostrarErrores("billing");
    });

    // Valida al cargar si la dirección es la misma
    let checkInterval = setInterval(function () {
        const checkbox = $(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input");
        if (checkbox.length) {
            yaValidado.shipping = false;
            validarNIFyMostrarErrores("shipping");
            clearInterval(checkInterval);
        }
    }, 500);

    // Valida al cambiar el checkbox de "usar misma dirección"
    $(document).on("change", ".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input", function () {
        yaValidado.billing = false;
        yaValidado.shipping = false;
        validarNIFyMostrarErrores("billing");
        validarNIFyMostrarErrores("shipping");
    });
});