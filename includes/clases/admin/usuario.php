<?php
/**
 * Campos extra de NIF/CIF/NIE para Usuarios en WooCommerce (panel de administración).
 *
 * - Inserta los campos NIF en los metaboxes de facturación y envío del perfil de usuario.
 * - Añade NIF, teléfono y email a las columnas de direcciones del listado de usuarios.
 *
 * @package WC_APG_NIFCIFNIE_Field
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

/**
 * Añade/ordena campos NIF para usuarios en WooCommerce (panel de administración).
 */
class APG_Campo_NIF_en_Usuarios {
	
	/**
	 * Inicializa los hooks relacionados con usuarios.
	 *
	 * Hooks:
	 * - `woocommerce_customer_meta_fields` para añadir/ordenar campos en el perfil.
	 * - `woocommerce_user_column_billing_address` para mostrar datos extra en la columna de facturación.
	 * - `woocommerce_user_column_shipping_address` para mostrar datos extra en la columna de envío.
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'woocommerce_customer_meta_fields', [ $this, 'apg_nif_anade_campos_administracion_usuarios' ] );
		add_filter( 'woocommerce_user_column_billing_address', [ $this, 'apg_nif_anade_campo_nif_usuario_direccion_facturacion' ], 1, 2 );
		add_filter( 'woocommerce_user_column_shipping_address', [ $this, 'apg_nif_anade_campo_nif_usuario_direccion_envio' ], 1, 2 );
    }

	/**
	 * Añade los campos NIF (facturación y envío) y reordena el conjunto
	 * de campos en el perfil del usuario (panel de administración).
	 *
	 * Hook: `woocommerce_customer_meta_fields`.
	 *
	 * @global array<string,mixed> $apg_nif_settings Ajustes del plugin (p.ej. etiqueta).
	 *
	 * @param array<string,mixed> $campos Estructura de campos de WooCommerce para el perfil de usuario.
	 * @return array<string,mixed> Conjunto de campos actualizado/ordenado.
	 */
	public function apg_nif_anade_campos_administracion_usuarios( $campos ) {
		global $apg_nif_settings;

        $etiqueta                                               = isset( $apg_nif_settings['etiqueta'] ) && $apg_nif_settings['etiqueta'] ? sanitize_text_field( $apg_nif_settings['etiqueta'] ) : esc_attr__( 'NIF/CIF/NIE', 'wc-apg-nifcifnie-field' );
        $campos[ 'billing' ][ 'fields' ][ 'billing_nif' ]       = [ 
				'label'			=> $etiqueta,
				'description'	=> '',
		];
        $campos[ 'shipping' ][ 'fields' ][ 'shipping_nif' ]     = [ 
				'label'			=> $etiqueta,
				'description'	=> '',
		];
        $campos[ 'shipping' ][ 'fields' ][ 'shipping_email' ]   = [ 
				'label'			=> esc_attr__( 'Email address', 'wc-apg-nifcifnie-field' ),
				'description'	=> '',
		];
        $campos[ 'shipping' ][ 'fields' ][ 'shipping_phone' ]   = [ 
				'label'			=> esc_attr__( 'Phone', 'wc-apg-nifcifnie-field' ),
				'description'	=> '',
        ];

		// Orden recomendado de campos.
		$orden_de_campos  = [
			"first_name", 
			"last_name", 
			"company", 
			"nif", 
			"email",
			"phone",
			"address_1", 
			"address_2", 
			"postcode", 
			"city",
			"state",
			"country", 
		];
		
		// Rellena el nuevo listado manteniendo los títulos de secciones.
		$campos_ordenados[ 'billing' ][ 'title' ]     = $campos[ 'billing' ][ 'title' ];
		$campos_ordenados[ 'shipping' ][ 'title' ]    = $campos[ 'shipping' ][ 'title' ];
		foreach ( $orden_de_campos as $campo ) {
            $billing    = $campo === 'nif' ? 'billing_nif' : 'billing_' . $campo;
            $shipping   = $campo === 'nif' ? 'shipping_nif' : 'shipping_' . $campo;
            
            if ( isset( $campos[ 'billing' ][ 'fields' ][ $billing ] ) ) {
                $campos_ordenados[ 'billing' ][ 'fields' ][ $billing ]    = $campos[ 'billing' ][ 'fields' ][ $billing ];
            }
            
            if ( isset( $campos[ 'shipping' ][ 'fields' ][ $shipping ] ) ) {
                $campos_ordenados[ 'shipping' ][ 'fields' ][ $shipping ]  = $campos[ 'shipping' ][ 'fields' ][ $shipping ];
            }
		}
		
		// Asegura que no se pierda ningún campo no contemplado en el orden.
        foreach ( $campos[ 'billing' ][ 'fields' ] as $campo => $datos ) {
            if ( ! isset( $campos_ordenados[ 'billing' ][ 'fields' ][ $campo ] ) ) {
                $campos_ordenados[ 'billing' ][ 'fields' ][ $campo ] = $datos;
            }
        }
        foreach ( $campos[ 'shipping' ][ 'fields' ] as $campo => $datos ) {
            if ( ! isset( $campos_ordenados[ 'shipping' ][ 'fields' ][ $campo ] ) ) {
                $campos_ordenados[ 'shipping' ][ 'fields' ][ $campo ] = $datos;
            }
        }

        return $campos_ordenados;
	}
	
	/**
	 * Inserta NIF, teléfono y email en la columna "Dirección de facturación"
	 * del listado de usuarios en el panel de administración.
	 *
	 * Hook: `woocommerce_user_column_billing_address`.
	 *
	 * @param array<string,mixed> $campos  Datos existentes a mostrar en la columna.
	 * @param int                 $cliente ID del usuario.
	 * @return array<string,mixed> Datos de columna con NIF/phone/email añadidos.
	 */
	public function apg_nif_anade_campo_nif_usuario_direccion_facturacion( $campos, $cliente ) {
		$campos[ 'nif' ]      = get_user_meta( $cliente, 'billing_nif', true );
		$campos[ 'phone' ]    = get_user_meta( $cliente, 'billing_phone', true );
		$campos[ 'email' ]    = get_user_meta( $cliente, 'billing_email', true );

        return $campos;
	}
	 
	/**
	 * Inserta NIF, teléfono y email en la columna "Dirección de envío"
	 * del listado de usuarios en el panel de administración.
	 *
	 * Hook: `woocommerce_user_column_shipping_address`.
	 *
	 * @param array<string,mixed> $campos  Datos existentes a mostrar en la columna.
	 * @param int                 $cliente ID del usuario.
	 * @return array<string,mixed> Datos de columna con NIF/phone/email añadidos.
	 */
	public function apg_nif_anade_campo_nif_usuario_direccion_envio( $campos, $cliente ) {
		$campos[ 'nif' ]      = get_user_meta( $cliente, 'shipping_nif', true );
		$campos[ 'phone' ]    = get_user_meta( $cliente, 'shipping_phone', true );
		$campos[ 'email' ]    = get_user_meta( $cliente, 'shipping_email', true );
		
		return $campos;
	}
}

new APG_Campo_NIF_en_Usuarios();
