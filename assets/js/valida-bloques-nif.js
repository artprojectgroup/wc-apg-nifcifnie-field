jQuery(document).ready(function ($) {
    const {
        extensionCartUpdate,
        validation
    } = wc.blocksCheckout || {};

    function validarNIFyMostrarErrores(formulario) {
        const campoNIF = $("#" + formulario + "-apg-nif");
        const campoPais = $("#" + formulario + "-country");

        if (!campoNIF.length || !campoPais.length || !campoNIF.val() || !campoPais.val()) return;

        const datos = {
            action: "apg_nif_valida_VIES",
            billing_nif: campoNIF.val(),
            billing_country: campoPais.val(),
        };

        $(".wp-block-woocommerce-checkout-totals-block").block({
            message: null,
            overlayCSS: {
                background: "#fff",
                opacity: 0.6,
            },
        });

        $.ajax({
            type: "POST",
            url: apg_nif_ajax.url,
            data: datos,
            success: function (response) {
                const wrapper = $("#" + formulario + " .wc-block-components-address-form__apg-nif");
                const errorID = "error_nif";
                console.log("WC - APG NIF/CIF/NIE Field:");
                console.log(response);

                // Limpia errores previos
                $("#" + errorID).remove();
                wrapper.attr("aria-invalid", "false").closest(".wc-block-components-text-input").removeClass("has-error");

                if (response.success) {
                    const res = response.data;
                    let texto = "";
                    let hay_error = false;

                    if (res.usar_eori && res.valido_eori === false) {
                        texto = apg_nif_eori_ajax.error;
                        hay_error = true;
                    } else if (!res.usar_eori && !res.valido_vies && !res.vat_valido) {
                        texto = res.valido_vies === 44 ? apg_nif_ajax.max : apg_nif_ajax.error;
                        hay_error = true;
                    }

                    if (hay_error) {
                        if (!$("#" + errorID).length) {
                            wrapper.append('<div id="' + errorID + '"><strong>' + texto + "</strong></div>");
                        }

                        wrapper.attr("aria-invalid", "true").closest(".wc-block-components-text-input").addClass("has-error");

                        if (typeof validation !== "undefined" && typeof validation.addError === "function") {
                            validation.addError({
                                field: formulario + "-apg-nif",
                                message: texto,
                            });
                        }
                    } else {
                        // Elimina mensaje si existe
                        $("#" + errorID).remove();

                        wrapper.attr("aria-invalid", "false").closest(".wc-block-components-text-input").removeClass("has-error");

                        if (typeof validation !== "undefined" && typeof validation.removeError === "function") {
                            validation.removeError(formulario + "-apg-nif");
                        }
                    }
                }

                if (typeof extensionCartUpdate === "function") {
                    extensionCartUpdate({
                        namespace: "apg_nif_valida_vies"
                    }).finally(() => {
                        if (window.wp && window.wp.data) {
                            window.wp.data.dispatch("wc/store/cart").invalidateResolution("getCartTotals");
                            window.wp.data.dispatch("wc/store/cart").invalidateResolution("getShippingRates");
                        }
                        $(".wp-block-woocommerce-checkout-totals-block").unblock();
                    });
                } else {
                    $(".wp-block-woocommerce-checkout-totals-block").unblock();
                }
            },
        });
    }

    // Delegación segura al cargar campos dinámicos
    $(document).on("change", "#billing-apg-nif, #billing-country", function () {
        if (!$(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked")) {
            validarNIFyMostrarErrores("billing");
        }
    });

    $(document).on("change", "#shipping-apg-nif, #shipping-country", function () {
        if ($(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked")) {
            validarNIFyMostrarErrores("shipping");
        }
    });

    // Valida al hacer clic en editar dirección
    $(document).on("click", ".wc-block-components-address-card__edit", function () {
        const form = $(this).attr("aria-controls");
        const sameAddress = $(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").prop("checked");

        if ((form === "billing" && !sameAddress) || (form === "shipping" && sameAddress)) {
            validarNIFyMostrarErrores(form);
        }
    });

    // Valida al insertar el bloque de facturación
    $("body").on("DOMNodeInserted", "#billing-fields", function () {
        validarNIFyMostrarErrores("billing");
    });

    // Valida al iniciar si la dirección es la misma
    let checkInterval = setInterval(function () {
        const checkbox = $(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input");
        if (checkbox.length) {
            if (checkbox.prop("checked")) {
                validarNIFyMostrarErrores("shipping");
            }
            clearInterval(checkInterval);
        }
    }, 500);

    // Valida al marcar/desmarcar "usar misma dirección"
    $(document).on("change", ".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input", function () {
        if ($(this).prop("checked")) {
            validarNIFyMostrarErrores("shipping");
        }
    });
});
