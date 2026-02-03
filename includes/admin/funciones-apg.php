<?php
/**
 * Utilidades de administración para el plugin
 * "WC - APG NIF/CIF/NIE Field".
 *
 * Contiene enlaces del listado de plugins, enlace de Ajustes,
 * recuperación de valoración desde WordPress.org y carga de estilos
 * en la administración.
 *
 * @package   WC_APG_NIFCIFNIE_Field
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

/**
 * Datos estáticos del plugin usados en la administración.
 *
 * @var array{
 *   plugin:string,
 *   plugin_uri:string,
 *   donacion:string,
 *   soporte:string,
 *   plugin_url:string,
 *   ajustes:string,
 *   puntuacion:string
 * }
 */
$apg_nif = [	
	'plugin' 		=> 'WC - APG NIF/CIF/NIE Field', 
	'plugin_uri' 	=> 'wc-apg-nifcifnie-field', 
	'donacion' 		=> 'https://artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'https://artprojectgroup.es/tienda/soporte-tecnico',
	'plugin_url' 	=> 'https://artprojectgroup.es/plugins-para-woocommerce/wc-apg-nifcifnie-field', 
	'ajustes' 		=> 'admin.php?page=wc-apg-nifcifnie-field', 
	'puntuacion' 	=> 'https://www.wordpress.org/support/view/plugin-reviews/wc-apg-nifcifnie-field'
];

/** @var mixed|null $envios_adicionales */
/** @var mixed|null $limpieza */
$envios_adicionales = $limpieza = NULL;

/**
 * Carga la configuración guardada del plugin.
 *
 * @var array<string,mixed>|false $apg_nif_settings
 */
$apg_nif_settings	= get_option( 'apg_nif_settings' );

/**
 * Añade enlaces personalizados (donación, soporte, redes, rating, etc.)
 * en la fila del plugin dentro de "Plugins" (admin).
 *
 * Hook: `plugin_row_meta`.
 *
 * @global array $apg_nif
 *
 * @param string[] $enlaces Lista existente de enlaces.
 * @param string   $archivo Ruta del archivo principal del plugin mostrado.
 * @return string[] Enlaces con los adicionales del plugin si aplica.
 */
function apg_nif_enlaces( $enlaces, $archivo ) {
	global $apg_nif;

	if ( $archivo == DIRECCION_apg_nif ) {
		$plugin		= apg_nif_plugin( $apg_nif[ 'plugin_uri' ] );
		$enlaces[]	= '<a href="' . $apg_nif[ 'donacion' ] . '" target="_blank" title="' . esc_attr__( 'Make a donation by ', 'wc-apg-nifcifnie-field' ) . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[]	= '<a href="'. $apg_nif[ 'plugin_url' ] . '" target="_blank" title="' . $apg_nif[ 'plugin' ] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[]	= '<a href="https://www.facebook.com/artprojectgroup" title="' . esc_attr__( 'Follow us on ', 'wc-apg-nifcifnie-field' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . esc_attr__( 'Follow us on ', 'wc-apg-nifcifnie-field' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://es.linkedin.com/in/artprojectgroup" title="' . esc_attr__( 'Follow us on ', 'wc-apg-nifcifnie-field' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[]	= '<a href="https://profiles.wordpress.org/artprojectgroup/" title="' . esc_attr__( 'More plugins on ', 'wc-apg-nifcifnie-field' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[]	= '<a href="mailto:info@artprojectgroup.es" title="' . esc_attr__( 'Contact with us by ', 'wc-apg-nifcifnie-field' ) . 'e-mail"><span class="genericon genericon-mail"></span></a>';
		$enlaces[]	= apg_nif_plugin( $apg_nif[ 'plugin_uri' ] );
	}
	
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'apg_nif_enlaces', 10, 2 );

/**
 * Añade los enlaces "Ajustes" y "Soporte" en la fila de acciones del plugin.
 *
 * Hook: `plugin_action_links_{plugin_basename}`.
 *
 * @global array $apg_nif
 *
 * @param string[] $enlaces Enlaces actuales de acción del plugin.
 * @return string[] Enlaces actualizados con Ajustes y Soporte al principio.
 */
function apg_nif_enlace_de_ajustes( $enlaces ) { 
	global $apg_nif;

	$enlaces_de_ajustes = [
		'<a href="' . $apg_nif[ 'ajustes' ] . '" title="' . esc_attr__( 'Settings of ', 'wc-apg-nifcifnie-field' ) . $apg_nif[ 'plugin' ] .'">' . esc_attr__( 'Settings', 'wc-apg-nifcifnie-field' ) . '</a>', 
		'<a href="' . $apg_nif[ 'soporte' ] . '" title="' . esc_attr__( 'Support of ', 'wc-apg-nifcifnie-field' ) . $apg_nif[ 'plugin' ] .'">' . esc_attr__( 'Support', 'wc-apg-nifcifnie-field' ) . '</a>'
	];
	foreach ( $enlaces_de_ajustes as $enlace_de_ajustes ) {
		array_unshift( $enlaces, $enlace_de_ajustes );
	}
	
	return $enlaces; 
}

/**
 * Basename del plugin usado para construir el hook de acción.
 *
 * @var string
 */
$plugin = DIRECCION_apg_nif; 
add_filter( "plugin_action_links_$plugin", 'apg_nif_enlace_de_ajustes' );

/**
 * Recupera información del plugin desde la API de WordPress.org y
 * devuelve el HTML de las estrellas de valoración enlazadas.
 *
 * Usa un transient para cachear la respuesta 24h.
 *
 * @global array $apg_nif
 *
 * @param string $nombre Slug del plugin en WordPress.org.
 * @return string HTML con las estrellas de valoración (o texto alternativo si falla).
 */
function apg_nif_plugin( $nombre ) {
	global $apg_nif;

	$respuesta	= get_transient( 'apg_nif_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . $nombre  );
		set_transient( 'apg_nif_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( ! is_wp_error( $respuesta ) ) {
		$plugin = json_decode( wp_remote_retrieve_body( $respuesta ) );
	} else {
        // translators: %s is the plugin name (e.g., WC – APG Campo NIF/CIF/NIE)
        return '<a title="' . sprintf( esc_attr__( 'Please, rate %s:', 'wc-apg-nifcifnie-field' ), $apg_nif[ 'plugin' ] ) . '" href="' . $apg_nif[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . esc_attr__( 'Unknown rating', 'wc-apg-nifcifnie-field' ) . '</a>';
	}

    $rating = [
	   'rating'		=> ( isset( $plugin->rating ) ) ? $plugin->rating : 0,
	   'type'		=> 'percent',
	   'number'		=> ( isset( $plugin->num_ratings ) ) ? $plugin->num_ratings : 0,
	];
	ob_start();
	wp_star_rating( $rating );
	$estrellas = ob_get_contents();
	ob_end_clean();

    // translators: %s is the plugin name (e.g., WC – APG Campo NIF/CIF/NIE)
	return '<a title="' . sprintf( esc_attr__( 'Please, rate %s:', 'wc-apg-nifcifnie-field' ), $apg_nif[ 'plugin' ] ) . '" href="' . $apg_nif[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

/**
 * Registra/encola estilos y JS necesarios en el admin en las pantallas relevantes.
 *
 * Hook: `admin_enqueue_scripts`.
 *
 * @param string $hook Hook de pantalla actual en el admin (por ejemplo, 'woocommerce_page_wc-admin').
 * @return void
 */
function apg_nif_estilo( $hook ) {
    if ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
        $request_uri = sanitize_text_field( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) );
        if ( strpos( $request_uri, 'wc-apg-nifcifnie-field' ) !== false || strpos( $request_uri, 'plugins.php' ) !== false ) {
            // Carga/registro de la hoja de estilo del plugin con firma correcta (deps, ver, media)
            wp_register_style( 'apg_nif_hoja_de_estilo', plugins_url( 'assets/css/style.css', DIRECCION_apg_nif ), [], VERSION_apg_nif, 'all' );
            if ( ! wp_style_is( 'apg_nif_hoja_de_estilo', 'enqueued' ) ) {
                wp_enqueue_style( 'apg_nif_hoja_de_estilo' );
            }
        }
    }
    // Solo cargar en WooCommerce Admin
    if ( 'woocommerce_page_wc-admin' === $hook ) {
        wp_enqueue_script( 'wc-apg-clientes-nif', plugins_url( 'assets/js/clientes-nif.js', DIRECCION_apg_nif ), [ 'wp-hooks', 'wp-api-fetch', 'wc-admin-app' ], VERSION_apg_nif, true );

        // Textos que consumirá el JS (multilingüe)
        wp_localize_script( 'wc-apg-clientes-nif', 'APGNIF', [
            'i18n' => [
                'downloadWithNif' => __( 'Download (with NIF/CIF/NIE)', 'wc-apg-nifcifnie-field' ),
                'errorGenerating' => __( 'Error generating CSV.', 'wc-apg-nifcifnie-field' ),
            ],
        ] );
    }
}
add_action( 'admin_enqueue_scripts', 'apg_nif_estilo' );

/**
 * Añade el campo `billing_nif` a la respuesta del endpoint de clientes de WooCommerce Admin
 * (`/wc-analytics/reports/customers`).
 *
 * Hook: `woocommerce_rest_prepare_report_customers`.
 *
 * @param WP_REST_Response $response Respuesta REST a modificar.
 * @param array            $report   Datos del reporte del cliente.
 * @param WP_REST_Request  $request  Petición REST actual.
 * @return WP_REST_Response Respuesta modificada con el campo `billing_nif`.
 */
add_filter( 'woocommerce_rest_prepare_report_customers', function( $response, $report, $request ) {
    if ( ! isset( $response->data ) ) {
        return $response;
    }
    // ID de usuario asociado al registro del reporte
    $user_id = isset( $response->data['user_id'] ) ? (int) $response->data['user_id'] : 0;
    if ( $user_id ) {
        // Recupera el NIF del meta del usuario
        $nif = get_user_meta( $user_id, 'billing_nif', true );
        if ( '' !== $nif && null !== $nif ) {
            $response->data['billing_nif'] = $nif;
        } else {
            $response->data['billing_nif'] = '';
        }
    } else {
        $response->data['billing_nif'] = '';
    }
    return $response;
}, 10, 3 );
