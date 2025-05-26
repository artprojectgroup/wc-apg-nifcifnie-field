jQuery(function ($) {
	// Comprueba la lista de países compatibles con EORI
	var listaEORI = apg_nif_eori_ajax.lista || [];

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

		const datos = {
            action: "apg_nif_valida_VIES",
			billing_nif: nif,
			billing_country: pais,
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

					let texto = '';
					let errorID = '';

					if (res.usar_eori && res.valido_eori === false) {
						texto = apg_nif_eori_ajax.error;
						errorID = 'error_eori';
					} else if (!res.usar_eori && !res.valido_vies && !res.vat_valido) {
						texto = res.valido_vies === 44 ? apg_nif_ajax.max : apg_nif_ajax.error;
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