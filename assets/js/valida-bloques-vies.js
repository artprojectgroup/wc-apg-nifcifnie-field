jQuery(document).ready(function ($) {
    const {
        extensionCartUpdate,
        validation
    } = wc.blocksCheckout || {};

    //Valida al pulsar en editar
    $(document).on("click", ".wc-block-components-address-card__edit", function (event) {
        if ($(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked") || (!$(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked") && $(this).attr("aria-controls") == "billing")) {
            ValidaVIES_Bloques($(this).attr("aria-controls"));
        }
    });

    //Valida al inicio si el formulario de facturación está cargado
    let checkInterval = setInterval(function () {
        const checkbox = $(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input");
        if (checkbox.length) {
            if (checkbox.prop("checked")) {
                ValidaVIES_Bloques('shipping');
            }
            clearInterval(checkInterval);
        }
    }, 500);

    //Valida al cargarse el formulario de facturación de nuevo
    $("body").on('DOMNodeInserted', '#billing-fields', function () {
        ValidaCampos_VIES();
    });

    //Valida al actualizar el formulario de envío, si el de facturación no está activo
    $(document).on("change", "#shipping-apg-nif,#shipping-country", function () {
        if ($(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked")) {
            ValidaVIES_Bloques($(this).closest(".wc-block-components-address-form").attr("id"));
        }
    });

    //Valida al pulsar el checkbox de misma dirección
    $(document).on("change", ".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input", function () {
        if ($(this).prop("checked")) {
            ValidaVIES_Bloques('shipping');
        }
    });

    //Valida el VIES
    function ValidaVIES_Bloques(formulario) {
        if (!$("#" + formulario + "-apg-nif").val() || !$("#" + formulario + "-country").val()) {
            return null;
        }

        var datos = {
            "action": "apg_nif_valida_VIES",
            "billing_nif": $("#" + formulario + "-apg-nif").val(),
            "billing_country": $("#" + formulario + "-country").val(),
        };
        console.log("Validando VIES: ", datos);
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
            url: apg_nif_ajax.url,
            data: datos,
            success: function (response) {
                console.log("Respuesta VIES:");
                console.log(response);
                if (response.success && response.data.usar_eori === false && response.data.valido_vies === false) { //No es válido
                    var texto = apg_nif_ajax.error;
                } else if (response.success && response.data.valido_vies === 44) { //Error de VIES
                    var texto = apg_nif_ajax.max;
                }
                if ($("#error_vies").length) { //Quita el error
                    $("#error_vies").remove();
                }
                if (typeof texto !== 'undefined') { //Muestra el error
                    $("#" + formulario + " .wc-block-components-address-form__apg-nif").append('<div id="error_vies"><strong>' + texto + "</strong></div>");
                }

                //Actualiza el checkout para poner o quitar el IVA
                extensionCartUpdate({
                    namespace: 'apg_nif_valida_vies'
                }).finally(() => {
                    if (window.wp && window.wp.data) {
                        window.wp.data.dispatch("wc/store/cart").invalidateResolution("getCartTotals");
                    }
                    $(".wp-block-woocommerce-checkout-totals-block").unblock();
                });
            },
        });
    }

    //Valida al actualizar el formulario de facturación
    function ValidaCampos_VIES() {
        //$(document).on("change", "#billing-apg-nif,#billing-country,#shipping-apg-nif,#shipping-country", function() {
        $("#billing-apg-nif, #billing-country").off("change").on("change", function () {
            if (!$(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked") && $(this).closest(".wc-block-components-address-form").attr("id") == "billing") {
                ValidaVIES_Bloques($(this).closest(".wc-block-components-address-form").attr("id"));
            }
        });
    }
});
