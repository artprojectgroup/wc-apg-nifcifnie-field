/**
 * Keeps the NIF/CIF/NIE label and required state in sync with the order amount,
 * both in the classic checkout and in the Checkout block.
 *
 * The rules always live on the server: this script only asks for the current state
 * and paints it, so the label never says "(optional)" on a field that is required.
 */
jQuery(function ($) {
    const cfg = window.apg_nif_obligatorio || {};
    const toBool = (valor) => valor === true || valor === 1 || valor === '1' || valor === 'true';

    const estado = {
        billing: toBool(cfg.requerido),
        shipping: toBool(cfg.requerido_envio),
    };

    /**
     * Checkout clásico: WooCommerce marca los campos con <abbr class="required"> o
     * <span class="optional">. Se clonan los del propio formulario para respetar el idioma.
     */
    function marcaClasico(campo, requerido) {
        const $fila = $('#' + campo + '_field');
        if (!$fila.length) {
            return;
        }

        const $etiqueta = $fila.find('label').first();
        if (!$etiqueta.length) {
            return;
        }

        const $input = $fila.find('input').first();
        const yaRequerido = $etiqueta.find('abbr.required').length > 0;

        if (requerido === yaRequerido) {
            return;
        }

        if (requerido) {
            $etiqueta.find('span.optional').remove();
            const $modelo = $('abbr.required').first();
            $etiqueta.append(' ').append($modelo.length ? $modelo.clone() : $('<abbr class="required">*</abbr>'));
            $fila.addClass('validate-required');
            $input.attr('required', 'required').attr('aria-required', 'true');
        } else {
            $etiqueta.find('abbr.required').remove();
            const $modelo = $('span.optional').first();
            if ($modelo.length) {
                $etiqueta.append(' ').append($modelo.clone());
            }
            $fila.removeClass('validate-required');
            $input.removeAttr('required').attr('aria-required', 'false');
        }
    }

    /**
     * Bloque de Finalizar compra: la etiqueta opcional la pinta React con el texto
     * registrado en `optionalLabel`. Se guarda para poder restaurarla.
     */
    function marcaBloques(formulario, requerido) {
        const campo = document.getElementById(formulario + '-apg-nif');
        if (!campo) {
            return;
        }

        const contenedor = campo.closest('.wc-block-components-text-input') || campo.parentElement;
        const etiqueta = contenedor ? contenedor.querySelector('label') : null;

        if (etiqueta && !etiqueta.dataset.apgNifOpcional) {
            etiqueta.dataset.apgNifOpcional = etiqueta.textContent;
        }

        if (requerido) {
            if (campo.getAttribute('required') !== 'required') {
                campo.setAttribute('required', 'required');
                campo.setAttribute('aria-required', 'true');
            }
            if (etiqueta && cfg.etiqueta && etiqueta.textContent !== cfg.etiqueta) {
                etiqueta.textContent = cfg.etiqueta;
            }
        } else {
            if (campo.hasAttribute('required')) {
                campo.removeAttribute('required');
                campo.setAttribute('aria-required', 'false');
            }
            const original = etiqueta ? etiqueta.dataset.apgNifOpcional : '';
            if (etiqueta && original && etiqueta.textContent !== original) {
                etiqueta.textContent = original;
            }
        }
    }

    /**
     * En Bloques, con "Usar la misma dirección para facturación" marcada sólo se muestra el
     * formulario de envío, cuya dirección es también la de facturación: ese campo debe
     * comportarse como el de facturación.
     */
    function envioEsFacturacion() {
        const casilla = document.querySelector('.wc-block-checkout__use-address-for-billing input');

        return !!(casilla && casilla.checked);
    }

    function aplica() {
        if (toBool(cfg.bloques)) {
            marcaBloques('billing', estado.billing);
            if (toBool(cfg.mostrar_envio)) {
                marcaBloques('shipping', envioEsFacturacion() ? (estado.billing || estado.shipping) : estado.shipping);
            }
        } else {
            marcaClasico('billing_nif', estado.billing);
            if (toBool(cfg.mostrar_envio)) {
                marcaClasico('shipping_nif', estado.shipping);
            }
        }
    }

    // Evita que los cambios propios vuelvan a disparar el observador.
    let observador = null;
    function aplicaSinObservar() {
        if (observador) {
            observador.disconnect();
        }
        aplica();
        if (observador) {
            observador.observe(document.body, { childList: true, subtree: true });
        }
    }

    let refrescando = false;
    function refresca() {
        if (!toBool(cfg.dinamico) || refrescando) {
            return;
        }

        refrescando = true;
        $.ajax({
            type: 'POST',
            url: cfg.url,
            data: {
                action: 'apg_nif_estado_obligatorio',
                nonce: cfg.nonce,
            },
            success: function (respuesta) {
                if (respuesta && respuesta.success && respuesta.data) {
                    estado.billing = toBool(respuesta.data.requerido);
                    estado.shipping = toBool(respuesta.data.requerido_envio);
                    aplicaSinObservar();
                }
            },
            complete: function () {
                refrescando = false;
            },
        });
    }

    aplica();

    if (toBool(cfg.bloques)) {
        // React vuelve a pintar los campos constantemente: hay que reaplicar el estado.
        if (window.MutationObserver) {
            let programado = false;
            observador = new MutationObserver(function () {
                if (programado) {
                    return;
                }
                programado = true;
                requestAnimationFrame(function () {
                    programado = false;
                    aplicaSinObservar();
                });
            });
            observador.observe(document.body, { childList: true, subtree: true });
        }

        // Y recalcula cuando cambia el total del pedido (cupones, gastos de envío…).
        if (window.wp?.data?.subscribe) {
            let ultimoTotal = null;
            window.wp.data.subscribe(function () {
                const carrito = window.wp.data.select('wc/store/cart');
                if (!carrito || !carrito.getCartTotals) {
                    return;
                }

                const totales = carrito.getCartTotals();
                const total = totales ? totales.total_price : null;
                if (total === null || total === undefined || total === ultimoTotal) {
                    return;
                }

                const primero = ultimoTotal === null;
                ultimoTotal = total;
                if (!primero) {
                    refresca();
                }
            });
        }
    } else {
        $(document.body).on('updated_checkout', function () {
            aplica();
            refresca();
        });
    }
});
