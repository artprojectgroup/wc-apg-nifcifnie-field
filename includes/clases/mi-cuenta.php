<?php
/**
 * Extensiones para el área "Mi cuenta" de WooCommerce.
 *
 * - Muestra NIF/email/teléfono en la vista de direcciones del cliente.
 * - Ajusta los campos del formulario de edición de direcciones (no duplica NIF).
 * - Oculta el campo NIF duplicado cuando procede.
 * - Sincroniza los metadatos `xxx_nif` y `_wc_xxx/apg/nif` al guardar.
 *
 * @package WC_APG_NIFCIFNIE_Field
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en Mi Cuenta.
 */
class APG_Campo_NIF_en_Cuenta {

	/**
	 * Inicializa los hooks del área "Mi cuenta".
	 *
	 * Hooks:
	 * - `woocommerce_my_account_my_address_formatted_address` para añadir NIF/email/teléfono
	 *   al array de dirección mostrado en "Mi cuenta".
	 * - `woocommerce_address_to_edit` para marcar como no requerido el campo NIF duplicado.
	 * - `wp_enqueue_scripts` para ocultar el campo duplicado en el formulario de direcciones.
	 * - `woocommerce_customer_save_address` para sincronizar metadatos al guardar la dirección.
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array( $this, 'apg_nif_anade_campo_nif_editar_direccion' ), 10, 3 );
		add_filter( 'woocommerce_address_to_edit', array( $this, 'apg_nif_anade_campo_nif_formulario_direccion' ), 99, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'apg_nif_oculta_campo_nif_duplicado' ) );
		add_action( 'woocommerce_customer_save_address', array( $this, 'apg_nif_guardar_nif_en_mi_cuenta' ), 10, 4 );
	}

	/**
	 * Añade NIF, email y teléfono al array de dirección mostrado en "Mi cuenta".
	 *
	 * Hook: `woocommerce_my_account_my_address_formatted_address`.
	 *
	 * @param array<string,mixed> $campos     Dirección ya formateada (clave => valor).
	 * @param int                 $cliente    ID del usuario/cliente.
	 * @param string              $formulario Contexto: 'billing' o 'shipping'.
	 * @return array<string,mixed> Dirección con claves `nif`, `email` y `phone` (y ordenada si aplica).
	 */
	public function apg_nif_anade_campo_nif_editar_direccion( $campos, $cliente, $formulario ) {
		if ( ! has_action( 'woocommerce_my_account_after_my_address' ) ) {
			$campos['nif']   = get_user_meta( $cliente, $formulario . '_nif', true );
			$campos['email'] = get_user_meta( $cliente, $formulario . '_email', true );
			$campos['phone'] = get_user_meta( $cliente, $formulario . '_phone', true );

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

			foreach ( $campos as $campo => $datos ) {
				if ( ! isset( $campos_ordenados[ $campo ] ) ) {
					$campos_ordenados[ $campo ] = $datos;
				}
			}

			return $campos_ordenados;
		}

		return $campos;
	}

	/**
	 * Marca como no requerido el campo NIF duplicado en el formulario de direcciones.
	 *
	 * Hook: `woocommerce_address_to_edit`.
	 *
	 * @param array<string,array<string,mixed>> $address      Array de campos de la dirección a editar.
	 * @param string                            $load_address Contexto: 'billing' o 'shipping'.
	 * @return array<string,array<string,mixed>> Campos con `_wc_{context}/apg/nif` no requerido.
	 */
	public function apg_nif_anade_campo_nif_formulario_direccion( $address, $load_address ) {
		$address['_wc_' . $load_address . '/apg/nif']['required'] = false;

		return $address;
	}

	/**
	 * Oculta el campo NIF duplicado en el formulario de "Mi cuenta" > Direcciones.
	 *
	 * Hook: `wp_enqueue_scripts`.
	 *
	 * @return void
	 */
	public function apg_nif_oculta_campo_nif_duplicado() {
		if ( is_account_page() && is_wc_endpoint_url( 'edit-address' ) ) {
			wp_register_style( 'apg-nif-hack', false, array(), VERSION_apg_nif );
			wp_enqueue_style( 'apg-nif-hack' );
			wp_add_inline_style( 'apg-nif-hack', '#apg\\/nif_field { display: none !important; }' );
		}
	}

	/**
	 * Sincroniza `{$address_type}_nif` y `_wc_{$address_type}/apg/nif` al guardar direcciones.
	 *
	 * Hook: `woocommerce_customer_save_address`.
	 *
	 * Esta acción puede invocarse con 2 o 4 argumentos según el origen:
	 * - (frontend, "Mi cuenta") `$user_id`, `$address_type`, `$address`, `$customer`
	 *   desde WC_Form_Handler::save_address(), que verifica `woocommerce-edit_address`.
	 * - (panel de administración) `$user_id`, `$address_type` desde
	 *   WC_Admin_Profile::save_customer_meta_fields().
	 *
	 * @param int                      $user_id      ID del usuario.
	 * @param string                   $address_type 'billing' o 'shipping'.
	 * @param array<string,mixed>|null $address      (Opcional) Datos del formulario de dirección.
	 * @param \WC_Customer|null        $customer     (Opcional) Objeto cliente cuando está disponible.
	 * @return void
	 */
	public function apg_nif_guardar_nif_en_mi_cuenta( $user_id, $address_type ) {
		$contador_argumentos = func_num_args();
		$argumentos          = func_get_args();

		// Verificación propia antes de tocar nada de $_POST: o el nonce del formulario de
		// direcciones de "Mi cuenta", o permiso explícito sobre la cuenta que se edita
		// (guardado desde el perfil de usuario en el panel de administración).
		$nonce = '';
		if ( isset( $_POST['woocommerce-edit-address-nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['woocommerce-edit-address-nonce'] ) );
		} elseif ( isset( $_POST['_wpnonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
		}

		$nonce_valido = '' !== $nonce && wp_verify_nonce( $nonce, 'woocommerce-edit_address' );

		if ( ! $nonce_valido && ! current_user_can( 'edit_user', (int) $user_id ) ) {
			return;
		}

		$campo_origen  = "{$address_type}_nif";
		$campo_destino = "_wc_{$address_type}/apg/nif";

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce `woocommerce-edit_address` (o capacidad edit_user sobre $user_id) verificado unas líneas más arriba, en esta misma función.
		if ( isset( $_POST[ $campo_origen ] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Ídem.
			$valor = sanitize_text_field( wp_unslash( $_POST[ $campo_origen ] ) );

			if ( 4 === $contador_argumentos && isset( $argumentos[3] ) && $argumentos[3] instanceof WC_Customer ) {
				// Tenemos el objeto WC_Customer (formulario de direcciones de "Mi cuenta").
				$customer = $argumentos[3];
				$customer->update_meta_data( $campo_origen, $valor );
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Ídem.
				if ( isset( $_POST[ $campo_destino ] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Ídem.
					$_POST[ $campo_destino ] = $valor;
					$customer->update_meta_data( $campo_destino, $valor );
				}
				$customer->save();
			} else {
				update_user_meta( $user_id, $campo_origen, $valor );
				update_user_meta( $user_id, $campo_destino, $valor );
			}
		}
	}
}

new APG_Campo_NIF_en_Cuenta();
