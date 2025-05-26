jQuery(document).ready(function ($) {
    const {
        extensionCartUpdate,
        validation
    } = wc.blocksCheckout || {};

    //Valida al pulsar en editar
    $(document).on("click", ".wc-block-components-address-card__edit", function (event) {
        if ($(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked") || (!$(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked") && $(this).attr("aria-controls") == "billing")) {
            ValidaEORI_Bloques($(this).attr("aria-controls"));
        }
    });

    //Valida al inicio si el formulario de facturación está cargado
    let checkInterval = setInterval(function () {
        const checkbox = $(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input");
        if (checkbox.length) {
            if (checkbox.prop("checked")) {
                ValidaEORI_Bloques('shipping');
            }
            clearInterval(checkInterval);
        }
    }, 500);

    //Valida al cargarse el formulario de facturación de nuevo
    $("body").on('DOMNodeInserted', '#billing-fields', function () {
        ValidaCampos_EORI();
    });

    //Valida al actualizar el formulario de envío, si el de facturación no está activo
    $(document).on("change", "#shipping-apg-nif,#shipping-country", function () {
        if ($(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked")) {
            ValidaEORI_Bloques($(this).closest(".wc-block-components-address-form").attr("id"));
        }
    });

    //Valida al pulsar el checkbox de misma dirección
    $(document).on("change", ".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input", function () {
        if ($(this).prop("checked")) {
            ValidaEORI_Bloques('shipping');
        }
    });

    //Valida el EORI
    function ValidaEORI_Bloques(formulario) {
        //Comprueba la lista de países
        var lista = apg_nif_eori_ajax.lista;
        if (!$("#" + formulario + "-apg-nif").val() || !$("#" + formulario + "-country").val() || lista.includes($("#" + formulario + "-country").val()) == false) {
            return null;
        }

        var datos = {
            "action": "apg_nif_valida_EORI",
            "billing_nif": $("#" + formulario + "-apg-nif").val(),
            "billing_country": $("#" + formulario + "-country").val(),
        };
        console.log("Validando EORI: ", datos);
        const {
            extensionCartUpdate
        } = wc.blocksCheckout;
        $('.wp-block-woocommerce-checkout-totals-block').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        $.ajax({
            type: "POST",
            url: apg_nif_eori_ajax.url,
            data: datos,
            success: function (response) {
                console.log("Respuesta EORI:");
                console.log(response);
                const campo = $("#" + formulario + " .wc-block-components-address-form__apg-nif");

                // Limpia errores anteriores
                $("#error_eori").remove();
                campo.attr("aria-invalid", "false").closest(".wc-block-components-text-input").removeClass("has-error");

                if (response.success && response.data.usar_eori === true && response.data.valido_eori === false) { //No es válido
                    alert ("Error");
                    const texto = apg_nif_eori_ajax.error;

                    campo.append('<div id="error_eori"><strong>' + texto + "</strong></div>");
                    campo.attr("aria-invalid", "true").closest(".wc-block-components-text-input").addClass("has-error");

                    // Bloquea el proceso de compra
                    if (validation && typeof validation.addError === "function") {
                        validation.addError({
                            field: formulario + "-apg-nif",
                            message: texto,
                        });
                    }
                }
            },
            complete: function () {
                $(".wp-block-woocommerce-checkout-totals-block").unblock();
            }
        });
    }

    //Valida al actualizar el formulario de facturación
    function ValidaCampos_EORI() {
        $(document).off("change", 'input[name$="_apg_nif"], select[name$="_country"]');

        $(document).on("change", 'input[name$="_apg_nif"], select[name$="_country"]', function () {
            const form = $(this).closest(".wc-block-components-address-form").attr("id");
            const sameAddress = $(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").prop("checked");

            if (form === "billing" && !sameAddress) {
                ValidaEORI_Bloques("billing");
            }
            if (form === "shipping" && sameAddress) {
                ValidaEORI_Bloques("shipping");
            }
        });
    }
});
