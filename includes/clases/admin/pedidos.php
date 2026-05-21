<?php
/**
 * Campos NIF/CIF/NIE en el Panel de Administración de Pedidos de WooCommerce.
 *
 * - Permite buscar pedidos por NIF (facturación y envío).
 * - Añade el campo NIF (y reordena teléfono/email) en los metaboxes de
 *   dirección de facturación y envío al editar un pedido.
 * - Completa los datos de NIF en pedidos creados manualmente vía AJAX
 *   (compatibilidad con versiones antiguas y nuevas de WooCommerce).
 * - Inyecta una pequeña hoja de estilos para la maquetación de campos.
 *
 * @package WC_APG_NIFCIFNIE_Field
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

/**
 * Añade y gestiona campos NIF en la administración de Pedidos.
 */
class APG_Campo_NIF_en_Admin_Pedidos {

	/**
	 * Inicializa los hooks relacionados con la edición/listado de pedidos.
	 *
	 * Hooks:
	 * - `woocommerce_shop_order_search_fields` para incluir claves de metadatos en la búsqueda.
	 * - `woocommerce_admin_billing_fields` para añadir/ordenar campos en la dirección de facturación.
	 * - `woocommerce_admin_shipping_fields` para añadir/ordenar campos en la dirección de envío.
	 * - `woocommerce_found_customer_details` (WC < 2.7) para cargar NIF vía AJAX.
	 * - `woocommerce_ajax_get_customer_details` (WC >= 2.7) para cargar NIF vía AJAX.
	 * - `admin_enqueue_scripts` para inyectar CSS en la edición de pedidos (clásico y HPOS).
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'apg_nif_anade_campo_nif_busqueda' ) );
		add_filter( 'woocommerce_admin_billing_fields', array( $this, 'apg_nif_anade_campo_nif_editar_direccion_pedido' ), 10, 3 );
		add_filter( 'woocommerce_admin_shipping_fields', array( $this, 'apg_nif_anade_campo_nif_editar_direccion_pedido' ), 10, 3 );
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			add_filter( 'woocommerce_found_customer_details', array( $this, 'apg_nif_ajax' ) );
		} else {
			add_filter( 'woocommerce_ajax_get_customer_details', array( $this, 'apg_dame_nif_ajax' ), 10, 2 );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'apg_nif_carga_hoja_de_estilo_editar_direccion_pedido' ) );
		add_action( 'admin_post_apg_nif_limpieza_duplicados', array( $this, 'apg_nif_ejecuta_limpieza_duplicados' ) );
	}

	/**
	 * Añade los metadatos NIF a los campos de búsqueda de pedidos.
	 *
	 * Hook: `woocommerce_shop_order_search_fields`.
	 *
	 * @param string[] $search_fields Lista de meta-keys por las que buscar pedidos.
	 * @return string[] Lista con `billing_nif` y `shipping_nif` añadidos.
	 */
	public function apg_nif_anade_campo_nif_busqueda( $search_fields ) {
		$search_fields[] = 'billing_nif';
		$search_fields[] = 'shipping_nif';

		return $search_fields;
	}

	/**
	 * Añade el campo NIF y reordena campos visibles en la edición de dirección
	 * (facturación o envío) dentro del pedido en el panel de administración.
	 *
	 * Hooks:
	 * - `woocommerce_admin_billing_fields`
	 * - `woocommerce_admin_shipping_fields`
	 *
	 * @global array<string,mixed> $apg_nif_settings Ajustes del plugin (p.ej. etiqueta del campo).
	 *
	 * @param array<string,array<string,mixed>> $campos  Conjunto de campos actuales del metabox (clave => definición).
	 * @param WC_Order|mixed|null               $order   Pedido actual o null si no aplica.
	 * @param string                            $context Contexto de renderizado: normalmente 'view' o 'edit'.
	 *
	 * @return array<string,array<string,mixed>> Conjunto de campos reordenado (con NIF añadido si procede).
	 */
	public function apg_nif_anade_campo_nif_editar_direccion_pedido( $campos, $order = null, $context = 'view' ) {
		global $apg_nif_settings;

		$etiqueta     = isset( $apg_nif_settings['etiqueta'] ) && $apg_nif_settings['etiqueta'] ? sanitize_text_field( $apg_nif_settings['etiqueta'] ) : esc_attr__( 'NIF/CIF/NIE', 'wc-apg-nifcifnie-field' );
		$es_envio     = ( 'woocommerce_admin_shipping_fields' === current_filter() );
		// El id lleva guion bajo para que el JS de WooCommerce (meta-boxes-order.js)
		// rellene el campo al seleccionar un cliente: busca `:input#_billing_nif`.
		// El update_callback se encarga de guardar en la clave canónica sin guion bajo.
		$campos['nif'] = array(
			'id'              => $es_envio ? '_shipping_nif' : '_billing_nif',
			'label'           => $etiqueta,
			'show'            => false,
			'update_callback' => array( $this, 'apg_nif_guarda_campo_nif_pedido' ),
		);

		if ( $order instanceof WC_Order ) {

			// Checkout Blocks guarda el NIF sin guion bajo; mantenemos fallback para pedidos antiguos.
			$valor_nif = $order->get_meta( $es_envio ? 'shipping_nif' : 'billing_nif', true );
			if ( '' === $valor_nif ) {
				$valor_nif = $order->get_meta( $es_envio ? '_shipping_nif' : '_billing_nif', true );
			}

			$customer_id = $order->get_customer_id();
			if ( $customer_id && '' === $valor_nif ) {
				$customer  = new WC_Customer( $customer_id );
				// En el cliente la meta key no lleva guion bajo.
				$valor_nif = $customer->get_meta( $es_envio ? 'shipping_nif' : 'billing_nif', true );
			}

			if ( '' !== $valor_nif ) {
				$campos['nif']['value'] = $valor_nif;
			}
		}

		$campos['phone'] = array(
			'label' => esc_attr__( 'Phone', 'wc-apg-nifcifnie-field' ),
			'show'  => true,
		);
		$campos['email'] = array(
			'label' => esc_attr__( 'Email address', 'wc-apg-nifcifnie-field' ),
			'show'  => true,
		);

		// Orden recomendado de campos.
		$orden_de_campos = array(
			'first_name',
			'last_name',
			'company',
			'nif',
			'email',
			'phone',
			'address_1',
			'address_2',
			'postcode',
			'city',
			'state',
			'country',
		);

		$campos_ordenados = array();

		foreach ( $orden_de_campos as $campo ) {
			if ( isset( $campos[ $campo ] ) ) {
				$campos_ordenados[ $campo ] = $campos[ $campo ];
			}
		}

		// Asegura que no se pierda ningún campo no contemplado en el orden anterior.
		foreach ( $campos as $campo => $datos ) {
			if ( ! isset( $campos_ordenados[ $campo ] ) && ( ! isset( $datos['label'] ) || $datos['label'] !== $etiqueta ) ) {
				$campos_ordenados[ $campo ] = $datos;
			}
		}

		return $campos_ordenados;
	}

	/**
	 * Guarda el NIF del pedido usando la clave de meta canónica (sin guion bajo).
	 *
	 * El campo se renderiza con el id `_billing_nif` / `_shipping_nif` para que el
	 * JS de WooCommerce lo rellene al seleccionar un cliente, pero el meta debe
	 * guardarse como `billing_nif` / `shipping_nif` (igual que el Bloque de
	 * Finalizar compra). Se invoca desde el guardado del metabox de pedido.
	 *
	 * @param string   $key   Id del campo en el formulario (p.ej. `_billing_nif`).
	 * @param string   $value Valor saneado del NIF.
	 * @param WC_Order $order Pedido en el que se guarda el meta.
	 * @return void
	 */
	public function apg_nif_guarda_campo_nif_pedido( $key, $value, $order ) {
		$clave = ltrim( $key, '_' ); // `_billing_nif` => `billing_nif`.
		// Elimina la clave heredada con guion bajo (la que crea el checkout clásico)
		// para no dejar dos metadatos distintos al editar un pedido antiguo.
		$order->delete_meta_data( '_' . $clave );
		$order->update_meta_data( $clave, $value );
	}

	/**
	 * (Compatibilidad WC < 2.7) Carga el NIF del usuario vía AJAX
	 * al crear/editar un pedido manualmente en el panel de administración.
	 *
	 * Hook: `woocommerce_found_customer_details`.
	 *
	 * @param array<string,mixed> $datos_cliente Datos de cliente devueltos por la llamada AJAX.
	 * @return array<string,mixed> Datos con `billing_nif`/`shipping_nif` añadidos cuando corresponda.
	 */
	public function apg_nif_ajax( $datos_cliente ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'.
		if ( isset( $_POST['user_id'], $_POST['type'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'.
			$cliente = absint( wp_unslash( $_POST['user_id'] ) );
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'.
			$formulario = sanitize_text_field( wp_unslash( $_POST['type'] ) );

			if ( $cliente && in_array( $formulario, array( 'billing', 'shipping' ), true ) ) {
				$datos_cliente[ $formulario . '_nif' ] = get_user_meta( $cliente, $formulario . '_nif', true );
			}
		}

		return $datos_cliente;
	}

	/**
	 * (WC >= 2.7) Añade el NIF del cliente a la respuesta AJAX
	 * al seleccionar un usuario al crear/editar un pedido.
	 *
	 * Hook: `woocommerce_ajax_get_customer_details`.
	 *
	 * @param array<string,mixed> $datos_cliente Datos de cliente a devolver (estructurados por 'billing' y 'shipping').
	 * @param \WC_Customer        $cliente       Objeto de cliente seleccionado.
	 * @return array<string,mixed> Datos con `nif` en secciones de 'billing' y 'shipping'.
	 */
	public function apg_dame_nif_ajax( $datos_cliente, $cliente ) {
		$datos_cliente['billing']['nif']  = $cliente->get_meta( 'billing_nif', true );
		$datos_cliente['shipping']['nif'] = $cliente->get_meta( 'shipping_nif', true );

		return $datos_cliente;
	}

	/**
	 * Encola CSS inline para maquetar los campos en la pantalla de edición de pedidos
	 * (compatible con la UI clásica y HPOS).
	 *
	 * Hook: `admin_enqueue_scripts`.
	 *
	 * @param string $hook Sufijo del hook de la pantalla actual en el admin.
	 * @return void
	 */
	public function apg_nif_carga_hoja_de_estilo_editar_direccion_pedido( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return;
		}

		$is_classic_order_edit = in_array( $screen->base, array( 'post', 'post-new' ), true ) && 'shop_order' === $screen->post_type;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only access to 'action' for UI/view logic; value is sanitized and not used to change state or process form data.
		$is_hpos_order_edit = 'woocommerce_page_wc-orders' === $screen->id && isset( $_GET['action'] ) && 'edit' === sanitize_key( wp_unslash( $_GET['action'] ) );
		if ( ! ( $is_classic_order_edit || $is_hpos_order_edit ) ) {
			return;
		}

		$css = '/* Cuando el inline style es "display:block", usa grid */
				#order_data .order_data_column .edit_address[style*="display:block"],
				#order_data .order_data_column .edit_address[style*="display: block"] {
				  display: grid !important;
				  grid-template-columns: 1fr 1fr;
				  column-gap: 4%;
				}

				/* Alinear el input al fondo del <p> (label arriba, control abajo) */
				#order_data .order_data_column .edit_address[style*="display:block"] .form-field,
				#order_data .order_data_column .edit_address[style*="display: block"] .form-field {
				  display: flex;
				  flex-direction: column;
				  justify-content: flex-end;
				  margin: 9px 0 0;
				  padding: 0;
				  box-sizing: border-box;
				  width: 100%;
				}

				/* Los campos "anchos" (wide) ocupan las 2 columnas */
				#order_data .order_data_column .edit_address[style*="display:block"] .form-field-wide,
				#order_data .order_data_column .edit_address[style*="display: block"] .form-field-wide {
				  grid-column: 1 / -1;
				}

				/* Responsive */
				@media (max-width: 782px) {
				  #order_data .order_data_column .edit_address[style*="display:block"],
				  #order_data .order_data_column .edit_address[style*="display: block"] {
					grid-template-columns: 1fr;
					column-gap: 0;
				  }
				}';

		// Preferimos colgarnos del handle de WooCommerce si está encolado.
		if ( wp_style_is( 'woocommerce_admin_styles', 'enqueued' ) ) {
			wp_add_inline_style( 'woocommerce_admin_styles', $css );
		} else {
			wp_register_style( 'apg-nif-admin-inline', false, array(), VERSION_apg_nif, 'all' );
			wp_enqueue_style( 'apg-nif-admin-inline' );
			wp_add_inline_style( 'apg-nif-admin-inline', $css );
		}
	}
	/**
	 * Procesa la limpieza masiva de metadatos de NIF desde la página de ajustes.
	 *
	 * Delega en {@see apg_nif_normaliza_meta_duplicados()}, que unifica las claves
	 * heredadas con la canónica y elimina filas duplicadas en postmeta y HPOS.
	 * Redirige a la página de ajustes con el resultado guardado en un transient.
	 *
	 * Hook: `admin_post_apg_nif_limpieza_duplicados`.
	 *
	 * @return void
	 */
	public function apg_nif_ejecuta_limpieza_duplicados() {
		check_admin_referer( 'apg_nif_limpieza_duplicados' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'wc-apg-nifcifnie-field' ) );
		}

		$resultado = apg_nif_normaliza_meta_duplicados();

		set_transient(
			'apg_nif_limpieza_resultado_' . get_current_user_id(),
			array(
				'pedidos' => (int) $resultado['pedidos'],
				'filas'   => (int) $resultado['filas'],
			),
			60
		);

		wp_safe_redirect(
			add_query_arg(
				array( 'page' => 'wc-apg-nifcifnie-field' ),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

}

new APG_Campo_NIF_en_Admin_Pedidos();

/**
 * Devuelve las tablas de metadatos de pedido a normalizar, con sus columnas.
 *
 * Incluye siempre `postmeta` (almacén clásico/heredado) y, si existe físicamente,
 * la tabla HPOS `wc_orders_meta`. Operar sobre ambas evita que queden duplicados
 * huérfanos en la tabla que no es la autoritativa (p.ej. postmeta obsoleto en una
 * tienda ya migrada a HPOS sin sincronización).
 *
 * @global \wpdb $wpdb
 * @return array<int,array{tabla:string,id:string,pk:string}> Cada entrada con la
 *         tabla, la columna de id de pedido y la columna de clave primaria.
 */
function apg_nif_tablas_meta_pedido() {
	global $wpdb;

	$tablas = array(
		array(
			'tabla' => $wpdb->postmeta,
			'id'    => 'post_id',
			'pk'    => 'meta_id',
		),
	);

	if ( ! empty( $wpdb->wc_orders_meta ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$existe = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->wc_orders_meta ) );
		// phpcs:enable
		if ( $existe ) {
			$tablas[] = array(
				'tabla' => $wpdb->wc_orders_meta,
				'id'    => 'order_id',
				'pk'    => 'id',
			);
		}
	}

	return $tablas;
}

/**
 * Comprueba si existe algún pedido con metadatos NIF que necesiten normalización.
 *
 * Detecta, en postmeta y en HPOS:
 * - Presencia de claves heredadas `_billing_nif` / `_shipping_nif` (se migrarán o
 *   eliminarán).
 * - Filas canónicas duplicadas (`billing_nif`×2 o `shipping_nif`×2).
 *
 * Usa la misma lógica que {@see apg_nif_normaliza_meta_duplicados()} para que el
 * botón de limpieza y la limpieza real siempre coincidan. El resultado se cachea
 * 1 hora.
 *
 * @global \wpdb $wpdb
 * @return bool true si hay al menos un pedido que necesite normalización.
 */
function apg_nif_hay_meta_duplicados() {
	$cached = get_transient( 'apg_nif_duplicados' );
	if ( false !== $cached ) {
		return '1' === $cached;
	}

	global $wpdb;
	$hay = false;

	// Los nombres de tabla y columna provienen de $wpdb y de literales internos
	// (nunca de entrada del usuario); los valores van con marcadores %s.
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
	foreach ( apg_nif_tablas_meta_pedido() as $t ) {
		$tabla = $t['tabla'];
		$id    = $t['id'];

		// Clave heredada presente (migración/eliminación pendiente).
		$heredada = $wpdb->get_var(
			"SELECT 1 FROM `{$tabla}` WHERE meta_key IN ( '_billing_nif', '_shipping_nif' ) LIMIT 1"
		);
		if ( $heredada ) {
			$hay = true;
			break;
		}

		// Filas canónicas duplicadas.
		$duplicada = $wpdb->get_var(
			"SELECT 1 FROM `{$tabla}` WHERE meta_key IN ( 'billing_nif', 'shipping_nif' ) GROUP BY `{$id}`, meta_key HAVING COUNT(*) > 1 LIMIT 1"
		);
		if ( $duplicada ) {
			$hay = true;
			break;
		}
	}
	// phpcs:enable

	set_transient( 'apg_nif_duplicados', $hay ? '1' : '0', HOUR_IN_SECONDS );
	return $hay;
}

/**
 * Normaliza los metadatos de NIF de todos los pedidos en postmeta y HPOS.
 *
 * Por cada tabla y cada grupo (billing/shipping) ejecuta, mediante SQL directo:
 *   1. Migra la clave heredada `_X_nif` a la canónica `X_nif` cuando esta no existe.
 *   2. Elimina la clave heredada cuando ya existe la canónica.
 *   3. Elimina filas canónicas duplicadas, conservando la de menor clave primaria.
 *
 * Tras modificar las tablas directamente, vacía las cachés de pedidos.
 *
 * @global \wpdb $wpdb
 * @return array{filas:int,pedidos:int} Filas modificadas y pedidos afectados.
 */
function apg_nif_normaliza_meta_duplicados() {
	global $wpdb;

	$pares = array(
		array(
			'canonica' => 'billing_nif',
			'heredada' => '_billing_nif',
		),
		array(
			'canonica' => 'shipping_nif',
			'heredada' => '_shipping_nif',
		),
	);

	$filas     = 0;
	$afectados = array();

	// Los nombres de tabla y columna provienen de $wpdb y de literales internos
	// (nunca de entrada del usuario); los valores van con marcadores %s.
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
	foreach ( apg_nif_tablas_meta_pedido() as $t ) {
		$tabla = $t['tabla'];
		$id    = $t['id'];
		$pk    = $t['pk'];

		// Recoge los pedidos con problemas (para el recuento) antes de modificar.
		$ids = $wpdb->get_col(
			"SELECT `{$id}` FROM `{$tabla}` WHERE meta_key IN ( '_billing_nif', '_shipping_nif' )
			 UNION
			 SELECT `{$id}` FROM `{$tabla}` WHERE meta_key IN ( 'billing_nif', 'shipping_nif' )
			 GROUP BY `{$id}`, meta_key HAVING COUNT(*) > 1"
		);
		foreach ( (array) $ids as $oid ) {
			$afectados[ (int) $oid ] = true;
		}

		foreach ( $pares as $par ) {
			$canonica = $par['canonica'];
			$heredada = $par['heredada'];

			// 1) Migra la clave heredada a canónica cuando no existe la canónica.
			$filas += (int) $wpdb->query(
				$wpdb->prepare(
					"UPDATE `{$tabla}` AS leg
					 LEFT JOIN `{$tabla}` AS can
					   ON can.`{$id}` = leg.`{$id}` AND can.meta_key = %s
					 SET leg.meta_key = %s
					 WHERE leg.meta_key = %s AND can.`{$pk}` IS NULL",
					$canonica,
					$canonica,
					$heredada
				)
			);

			// 2) Elimina la clave heredada cuando ya existe la canónica.
			$filas += (int) $wpdb->query(
				$wpdb->prepare(
					"DELETE leg FROM `{$tabla}` AS leg
					 JOIN `{$tabla}` AS can
					   ON can.`{$id}` = leg.`{$id}` AND can.meta_key = %s
					 WHERE leg.meta_key = %s",
					$canonica,
					$heredada
				)
			);

			// 3) Elimina filas canónicas duplicadas (conserva la de menor PK).
			$filas += (int) $wpdb->query(
				$wpdb->prepare(
					"DELETE dup FROM `{$tabla}` AS dup
					 JOIN `{$tabla}` AS keep
					   ON keep.`{$id}` = dup.`{$id}` AND keep.meta_key = dup.meta_key AND keep.`{$pk}` < dup.`{$pk}`
					 WHERE dup.meta_key = %s",
					$canonica
				)
			);
		}
	}
	// phpcs:enable

	// Tras la modificación directa de metadatos, invalida cachés relacionadas.
	delete_transient( 'apg_nif_duplicados' );
	if ( $filas > 0 ) {
		if ( function_exists( 'wc_delete_shop_order_transients' ) ) {
			wc_delete_shop_order_transients();
		}
		wp_cache_flush();
	}

	return array(
		'filas'   => $filas,
		'pedidos' => count( $afectados ),
	);
}