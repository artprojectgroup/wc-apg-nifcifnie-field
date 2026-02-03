/* global wp */
/**
 * Localized strings passed from PHP via wp_localize_script in apg_nif_estilo.
 * These keys are translatable and used in the script:
 * - downloadWithNif: Label for the custom download button with NIF.
 * - errorGenerating: Error message shown when CSV generation fails.
 */
(function () {
	// Localized strings injected by PHP (see apg_nif_estilo in funciones-apg.php)
	const I18N = (window.APGNIF && window.APGNIF.i18n) ? window.APGNIF.i18n : {};
	const DOWNLOAD_WITH_NIF = I18N.downloadWithNif || 'Download (with NIF/CIF/NIE)';
	const ERROR_MSG = I18N.errorGenerating || 'Error generating CSV.';

	// ---- Helpers -------------------------------------------------------------

	// Solo actuar en la vista SPA de Customers: /wp-admin/admin.php?page=wc-admin&path=%2Fcustomers
	function isCustomersRoute() {
		try {
			const params = new URLSearchParams(window.location.search);
			const path = params.get('path') || '';
			return decodeURIComponent(path) === '/customers';
		} catch (e) {
			return false;
		}
	}

	// Estado del ciclo de inyección
	let __apgInjected = false;
	let __apgObserver = null;

	// ---- Inyección del botón -------------------------------------------------

	function waitForToolbarAndInject() {
		try {
			if (!isCustomersRoute()) return;

			// Selecciona todos los botones nativos de descarga de la tabla y usa el último (barra inferior)
			const candidates = Array.from(document.querySelectorAll('button.woocommerce-table__download-button'));
			const downloadBtn = candidates.length ? candidates[candidates.length - 1] : null;

			if (!downloadBtn) {
				// Reintenta hasta que la SPA pinte la barra inferior
				requestAnimationFrame(waitForToolbarAndInject);
				return;
			}

			// Evita duplicados; si existe, recolócalo justo después del nativo
			const existing = document.getElementById('apg-download-with-nif');
			if (existing) {
				if (existing.previousElementSibling !== downloadBtn) {
					downloadBtn.parentElement.insertBefore(existing, downloadBtn.nextSibling);
				}
				// Ya está inyectado; podemos desconectar el observer para no saturar
				if (!__apgInjected) {
					__apgInjected = true;
					if (__apgObserver) __apgObserver.disconnect();
				}
				return;
			}

			// Clona el botón nativo para heredar estilos/icono
			const customBtn = downloadBtn.cloneNode(true);
			customBtn.id = 'apg-download-with-nif';
			customBtn.removeAttribute('disabled');
			customBtn.setAttribute('aria-label', DOWNLOAD_WITH_NIF);
			customBtn.title = DOWNLOAD_WITH_NIF;

			// Cambia solo el texto del <span> de etiqueta
			const labelSpan = customBtn.querySelector('.woocommerce-table__download-button__label');
			if (labelSpan) {
				labelSpan.textContent = DOWNLOAD_WITH_NIF;
			} else {
				const span = document.createElement('span');
				span.className = 'woocommerce-table__download-button__label';
				span.textContent = DOWNLOAD_WITH_NIF;
				customBtn.appendChild(span);
			}

			customBtn.addEventListener('click', onDownloadWithNif);
			downloadBtn.parentElement.insertBefore(customBtn, downloadBtn.nextSibling);

			__apgInjected = true;
			if (__apgObserver) __apgObserver.disconnect();
		} catch (e) {
			// No rompas la SPA por un fallo de inyección
			/* eslint-disable no-console */
			console.error('APG – Error inyectando el botón Descargar (con NIF):', e);
		}
	}

	// ---- Handler del botón ---------------------------------------------------

	async function onDownloadWithNif() {
		try {
			// Guardas robustas
			if (!window.wp || !wp.apiFetch) {
				console.warn('APG – wp.apiFetch no disponible en esta vista; se aborta descarga.');
				alert(ERROR_MSG);
				return;
			}

			// Parámetros básicos del listado (ajusta si necesitas filtros/ordenación actuales)
			const perPage = 100;
			let page = 1;
			let totalPages = 1;
			let all = [];

			do {
				// Usamos apiFetch con parse:false para poder leer cabeceras (X-WP-TotalPages)
				const res = await wp.apiFetch({
					path: `/wc-analytics/reports/customers?per_page=${perPage}&page=${page}`,
					parse: false,
				});

				// Manejo robusto del Response
				let data = [];
				let header = null;

				if (res && typeof res.json === 'function') {
					data = await res.json();
					if (res.headers && typeof res.headers.get === 'function') {
						header = res.headers.get('X-WP-TotalPages');
					}
				} else if (Array.isArray(res)) {
					// En implementaciones antiguas, apiFetch podría parsear a JSON aunque pases parse:false
					data = res;
				}

				if (Array.isArray(data) && data.length) {
					all = all.concat(data);
				}

				const parsed = parseInt(header, 10);
				totalPages = Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
				page++;
			} while (page <= totalPages);

			// Construcción del CSV (incluye billing_nif, añadido desde PHP al endpoint)
			const cols = [
				'username',
				'date_registered',
				'email',
				'orders_count',
				'total_spent',
				'avg_order_value',
				'country',
				'city',
				'region',
				'postcode',
				'billing_nif', // <— campo personalizado expuesto por tu PHP
			];

			const headerLine = cols.join(',');
			const lines = all.map((row) =>
				cols
					.map((k) => {
						const v = row && row[k] != null ? String(row[k]) : '';
						const s = v.replace(/"/g, '""'); // CSV-safe
						return /[",\n]/.test(s) ? `"${s}"` : s;
					})
					.join(',')
			);

			const csv = [headerLine].concat(lines).join('\n');
			const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
			const url = URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			const fecha = new Date().toISOString().split('T')[0];
			a.download = `clientes_${fecha}_con_nif.csv`;
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
			URL.revokeObjectURL(url);
		} catch (e) {
			console.warn('APG NIF CSV error', e);
		}
	}

	// ---- Arranque ------------------------------------------------------------

	(function boot() {
		// Solo activamos en la ruta de Customers y si existe apiFetch
		if (!isCustomersRoute()) {
			// No bloquear otras pantallas wc-admin
			return;
		}

		// Crea un único observer y arranca la detección
		__apgObserver = new MutationObserver(() => {
			try {
				waitForToolbarAndInject();
			} catch (e) {
				console.warn('APG NIF CSV error', e);
			}
		});
		__apgObserver.observe(document.documentElement || document.body, { childList: true, subtree: true });

		// Intento inicial
		waitForToolbarAndInject();
	})();
})();
