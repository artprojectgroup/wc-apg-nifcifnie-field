/**
 * Validates customer identification numbers in WooCommerce checkout blocks.
 */
jQuery(document).ready(function ($) {
    const SELECTOR_NIF = "#billing-apg-nif, #shipping-apg-nif";
    // Helper: establece el valor usando el setter nativo (React-friendly)
    function setNativeValue(el, value) {
        const proto = Object.getPrototypeOf(el);
        const desc = Object.getOwnPropertyDescriptor(proto, 'value');
        if (desc && desc.set) {
            desc.set.call(el, value);
        } else {
            el.value = value;
        }
    }
    const sanitizeNif = (valor = "") => (
        (valor || "")
            .normalize("NFKD")
            // quita zero-width, NBSP y similares
            .replace(/[\u200B-\u200D\uFEFF\u00A0]/g, "")
            // normaliza guiones raros a guion ASCII
            .replace(/[\u2010-\u2015\u2212\u2043\u00AD\u2011]/g, "-")
            // deja solo A-Z, 0-9 y guion ASCII
            .replace(/[^a-zA-Z0-9-]/g, "")
            .toUpperCase()
    );

    // Normaliza los valores iniciales.
    $(SELECTOR_NIF).each(function () {
        const limpio = sanitizeNif($(this).val());
        if (limpio !== $(this).val()) {
            setNativeValue(this, limpio);
        }
        // Notificar a React de un posible cambio inicial
        try {
            this.dispatchEvent(new Event('input', { bubbles: true }));
            this.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (e) {}
    });

    // Forzar sólo letras y números (mayúsculas) en escritura
    $(document).on("input", SELECTOR_NIF, function () {
        const original = $(this).val() || "";
        const limpio = sanitizeNif(original);
        if (limpio !== original) {
            setNativeValue(this, limpio);
            // Avisar a React/WC Blocks de que el valor cambió por saneado
            try {
                this.dispatchEvent(new Event('input', { bubbles: true }));
                this.dispatchEvent(new Event('change', { bubbles: true }));
            } catch (e) {}
            $(this).trigger("input");
            $(this).trigger("change");
        }
    });

    // Forzar sólo letras y números (mayúsculas) al pegar (manejo manual del portapapeles)
    $(document).on("paste", SELECTOR_NIF, function (e) {
        const ev = e.originalEvent || e;
        const campo = this;
        const clip = ev.clipboardData || window.clipboardData;
        const pasted = clip ? (clip.getData('text') || '') : '';
        e.preventDefault();

        const valActual = $(campo).val() || '';
        const start = typeof campo.selectionStart === 'number' ? campo.selectionStart : valActual.length;
        const end   = typeof campo.selectionEnd === 'number'   ? campo.selectionEnd   : start;

        const antes = valActual.slice(0, start);
        const despues = valActual.slice(end);
        const nuevoValor = sanitizeNif(antes + pasted + despues);
        setNativeValue(campo, nuevoValor);

        // Avisar a React/WC Blocks y demás listeners
        try {
            campo.dispatchEvent(new Event('input', { bubbles: true }));
            campo.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (err) {}
        $(campo).trigger('input');
        $(campo).trigger('change');

        // Reposiciona el caret al final del segmento pegado ya saneado
        requestAnimationFrame(() => {
            try {
                const pos = sanitizeNif(antes + pasted).length; // longitud tras saneado
                campo.setSelectionRange(pos, pos);
            } catch (_) {}
        });
    });

    // Sanea también en blur (por si algún re-render externo intenta restaurar el valor previo)
    $(document).on('blur', SELECTOR_NIF, function(){
        const limpio = sanitizeNif($(this).val() || '');
        setNativeValue(this, limpio);
        try {
            this.dispatchEvent(new Event('input', { bubbles: true }));
            this.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (e) {}
        $(this).trigger('input');
        $(this).trigger('change');
    });

    const { extensionCartUpdate, validation } = wc.blocksCheckout || {};
    const EU_VIES_COUNTRIES = ['AT','BE','BG','HR','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK','XI']; // 'XI' = Irlanda del Norte
    const esUE = (c) => EU_VIES_COUNTRIES.includes((c || '').toUpperCase());

    // Estado de validación para evitar bucles/reentradas
    const estado = {
        billing: { inFlight: false, last: null },
        shipping: { inFlight: false, last: null },
    };

    // Compara si los datos a validar son iguales a los últimos validados
    function iguales(a, b) {
        if (!a || !b) return false;
        return a.nif === b.nif && a.country === b.country && a.shipCountry === b.shipCountry && a.action === b.action;
    }

    // Debounce + helpers
    const timers = { billing: null, shipping: null };
    function getPayload(formulario) {
        const campoNIF = $("#" + formulario + "-apg-nif");
        const campoPais = $("#" + formulario + "-country");
        const campoEnvio = $("#shipping-country");
        let action = "apg_nif_valida_VAT";
        switch (apg_nif_ajax.validacion) {
            case "eori": action = "apg_nif_valida_EORI"; break;
            case "vies": action = "apg_nif_valida_VIES"; break;
        }
        return {
            nif: (campoNIF.val() || "").trim(),
            country: (campoPais.val() || "").trim(),
            shipCountry: (campoEnvio.val() || "").trim(),
            action
        };
    }
    function scheduleValidate(formulario) {
        if (timers[formulario]) clearTimeout(timers[formulario]);
        timers[formulario] = setTimeout(function(){ validarNIFyMostrarErrores(formulario); }, 200);
    }
    // Mutea el observer de billing mientras tocamos el DOM del wrapper
    let muteBillingObserver = 0;

    function validarNIFyMostrarErrores(formulario) {
        const campoNIF = $("#" + formulario + "-apg-nif");
        const campoPais = $("#" + formulario + "-country");
        if (!campoNIF.length || !campoPais.length || !campoNIF.val() || !campoPais.val()) return;

        const payload = getPayload(formulario);

        // Evitar reentrada y duplicados (misma data)
        if (estado[formulario].inFlight) return;
        if (iguales(payload, estado[formulario].last)) return;

        estado[formulario].inFlight = true;
        estado[formulario].last = payload;

        const datos = {
            action: payload.action,
            billing_nif: payload.nif,
            billing_country: payload.country,
            shipping_country: payload.shipCountry,
            nonce: apg_nif_ajax.nonce,
        };

        $(".wp-block-woocommerce-checkout-totals-block").block({ message: null, overlayCSS: { background: "#fff", opacity: 0.6 } });

        $.ajax({
            type: "POST",
            url: apg_nif_ajax.url,
            data: datos,
            success: function (response) {
                estado[formulario].inFlight = false;
                const wrapper = $("#" + formulario + " .wc-block-components-address-form__apg-nif");
                const errorID = "error_nif";
                if (window.debug) {
                    console.log("WC - APG NIF/CIF/NIE Field (" + formulario + "):");
                    console.log(response);
                }

                if (formulario === 'billing') muteBillingObserver++;
                try {
                    $("#" + errorID).remove();
                    wrapper.attr("aria-invalid", "false").closest(".wc-block-components-text-input").removeClass("has-error");
                } finally {
                    if (formulario === 'billing') muteBillingObserver--;
                }

                if (datos.action === "apg_nif_valida_VAT") {
                    if (!response.data?.vat_valido) {
                        if (formulario === 'billing') muteBillingObserver++;
                        try {
                            wrapper.append(`<div id="${errorID}"><strong>${apg_nif_ajax.vat_error}</strong></div>`);
                            wrapper.attr("aria-invalid", "true").closest(".wc-block-components-text-input").addClass("has-error");
                        } finally {
                            if (formulario === 'billing') muteBillingObserver--;
                        }
                        validation?.addError?.({ field: formulario + "-apg-nif", message: apg_nif_ajax.vat_error });
                    } else {
                        validation?.removeError?.(formulario + "-apg-nif");
                    }

                    $(".wp-block-woocommerce-checkout-totals-block").unblock();
                    return;
                }

                if (response.success) {
                    const campoPais = $("#" + formulario + "-country");
                    const paisCliente = campoPais.val().toUpperCase();
                    const paisTienda = apg_nif_ajax.pais_base.toUpperCase();
                    const requiereVIES = esUE(paisCliente) && esUE(paisTienda) && paisCliente !== paisTienda;
                    const res = response.data;
                    let texto = "";
                    let hay_error = false;

                    if (res.usar_eori && res.valido_eori === false && paisCliente !== paisTienda) {
                        texto = apg_nif_ajax.eori_error;
                        hay_error = true;
                    } else if (requiereVIES && res.valido_vies === false) {
                        texto = res.valido_vies === 44 ? apg_nif_ajax.max_error : apg_nif_ajax.vies_error;
                        hay_error = true;
                    } else if (!res.vat_valido) {
                        texto = apg_nif_ajax.vat_error;
                        hay_error = true;
                    }

                    if (hay_error) {
                        if (formulario === 'billing') muteBillingObserver++;
                        try {
                            wrapper.append(`<div id="${errorID}"><strong>${texto}</strong></div>`);
                            wrapper.attr("aria-invalid", "true").closest(".wc-block-components-text-input").addClass("has-error");
                            validation?.addError?.({ field: formulario + "-apg-nif", message: texto });
                        } finally {
                            if (formulario === 'billing') muteBillingObserver--;
                        }
                    } else {
                        if (formulario === 'billing') muteBillingObserver++;
                        try {
                            $("#" + errorID).remove();
                            wrapper.attr("aria-invalid", "false").closest(".wc-block-components-text-input").removeClass("has-error");
                            validation?.removeError?.(formulario + "-apg-nif");

                            const event = new CustomEvent('checkout-blocks-validation-reset', {
                                detail: { field: formulario + "-apg-nif" }
                            });
                            document.dispatchEvent(event);
                            document.querySelector('.wc-block-components-notice-banner__dismiss')?.click();
                        } finally {
                            if (formulario === 'billing') muteBillingObserver--;
                        }
                    }

                    const validoVIES = requiereVIES && (res.valido_vies === true || res.valido_vies === '1');
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
                        if (window.debug) {
                            console.log('VAT exception:', data);
                        }

                        // Solo actualiza totales si la petición ha tenido éxito
                        if (data.success) {
                            const p = extensionCartUpdate ? extensionCartUpdate({ namespace: "apg_nif_valida_vies" }) : Promise.resolve();
                            p.finally(() => {
                                if (window.wp?.data) {
                                    window.wp.data.dispatch("wc/store/cart").invalidateResolution("getCartTotals");
                                    window.wp.data.dispatch("wc/store/cart").invalidateResolution("getShippingRates");
                                }
                                $(".wp-block-woocommerce-checkout-totals-block").unblock();
                            });
                        } else {
                            $(".wp-block-woocommerce-checkout-totals-block").unblock();
                        }
                    })
                    .catch(() => {
                        // En caso de error de red/JSON, desbloqueamos para no dejar el checkout bloqueado
                        $(".wp-block-woocommerce-checkout-totals-block").unblock();
                    });
                } else {
                    // Si la respuesta no es success, desbloquear igualmente
                    $(".wp-block-woocommerce-checkout-totals-block").unblock();
                }
            },
            error: function () {
                estado[formulario].inFlight = false;
                // Si falla la petición AJAX, desbloqueamos el checkout
                $(".wp-block-woocommerce-checkout-totals-block").unblock();
            },
            complete: function () {
                estado[formulario].inFlight = false;
                // Fallback por si algún flujo no hizo unblock
                $(".wp-block-woocommerce-checkout-totals-block").unblock();
            }
        });
    }

    // Eventos para detectar cambio en campos de billing y shipping
    $(document).on("change", "#billing-apg-nif, #billing-country", function () {
        scheduleValidate("billing");
    });

    $(document).on("change", "#shipping-apg-nif, #shipping-country", function () {
        scheduleValidate("shipping");
    });

    // Valida al hacer clic en editar dirección
    $(document).on("click", ".wc-block-components-address-card__edit", function () {
        const form = $(this).attr("aria-controls");
        scheduleValidate(form);
    });

    // Valida cuando se inserten/modifiquen nodos dentro de #billing-fields (MutationObserver en lugar de DOMNodeInserted)
    (function () {
        const billingFields = document.getElementById("billing-fields");
        if (!billingFields || !window.MutationObserver) return;

        const onMutate = () => {
            scheduleValidate("billing");
        };

        const observer = new MutationObserver(function (mutations) {
            if (muteBillingObserver > 0) return;
            // Evitar ráfagas: si hay muchos cambios en un mismo tick, consolidamos con rAF
            let scheduled = false;
            for (const m of mutations) {
                if (!scheduled && (m.addedNodes.length || m.type === 'childList')) {
                    scheduled = true;
                    requestAnimationFrame(onMutate);
                }
            }
        });

        observer.observe(billingFields, { childList: true, subtree: true });
    })();

    // Valida al cargar si la dirección es la misma (cuando aparece el checkbox)
    let checkInterval = setInterval(function () {
        const $checkbox = $(".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input");
        if ($checkbox && $checkbox.length) {
            scheduleValidate("shipping");
            clearInterval(checkInterval);
        }
    }, 500);

    // Valida al cambiar el checkbox de "usar misma dirección"
    $(document).on("change", ".wc-block-checkout__use-address-for-billing .wc-block-components-checkbox__input", function () {
        scheduleValidate("billing");
        scheduleValidate("shipping");
    });
});