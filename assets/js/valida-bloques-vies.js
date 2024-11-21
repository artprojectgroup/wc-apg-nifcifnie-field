jQuery(document).ready(function($){
    //Valida al pulsar en editar
    $(".wc-block-components-address-card__edit").on("click", function () {
        if ($(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked") || (!$(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked") && $(this).attr("aria-controls") == "billing")) {
            ValidaVIES_Bloques($(this).attr("aria-controls"));
        }
    });
    
    //Valida al inicio si el formulario de facturación está cargado
    if ($(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked")) {
        ValidaCampos_VIES();
    }

    //Valida al cargarse el formulario de facturación de nuevo
    $("body").on('DOMNodeInserted', '#billing-fields', function() {
        ValidaCampos_VIES();
    });

    //Valida al actualizar el formulario de envío, si el de facturación no está activo
    $("#shipping-apg-nif,#shipping-country").on("change", function () {
        if ($(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked")) {
            ValidaVIES_Bloques($(this).closest(".wc-block-components-address-form").attr("id"));
        }
    });  
    
    //Valida el VIES
    function ValidaVIES_Bloques(formulario) {
        if ( !$("#" + formulario + "-apg-nif").val() || !$("#" + formulario + "-country").val() ) {
            return null;
        }
    
        var datos = {
            "action": "apg_nif_valida_VIES",
            "billing_nif": $("#" + formulario + "-apg-nif").val(),
            "billing_country": $("#" + formulario + "-country").val(),
        };
        console.log(datos);
        const { extensionCartUpdate } = wc.blocksCheckout;
        $( '.wp-block-woocommerce-checkout-totals-block' ).block( {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        } );
        $.ajax({
            type: "POST",
            url: apg_nif_ajax.url,
            data: datos,
            success: function (response) {
                console.log("WC - APG NIF/CIF/NIE Field: " + response);
                if (response == 0 && $("#error_vies").length == 0) {
                    $("#" + formulario + " .wc-block-components-address-form__apg-nif").append('<div id="error_vies"><strong>' + apg_nif_ajax.error + "</strong></div>");
                } else if (response != 0 && $("#error_vies").length) {
                    $("#error_vies").remove();
                }
                //Actualiza el checkout para poner o quitar el IVA
                extensionCartUpdate( { 
                    namespace: 'apg_nif_valida_vies'
                } ).finally( () => {
                    $( '.wp-block-woocommerce-checkout-totals-block' ).unblock();
                } );
            },
        });
    }
    
    //Valida al actualizar el formulario de facturación
    function ValidaCampos_VIES() {
        //$(document).on("change", "#billing-apg-nif,#billing-country,#shipping-apg-nif,#shipping-country", function() {
        $("#billing-apg-nif,#billing-country").on("change", function () {
            if (!$(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input").is(":checked") && $(this).closest(".wc-block-components-address-form").attr("id") == "billing") {
                ValidaVIES_Bloques($(this).closest(".wc-block-components-address-form").attr("id"));
            }
        });  
    }
});