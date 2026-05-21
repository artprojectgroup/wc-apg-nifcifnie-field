<?php
/**
 * Script de limpieza/normalización de metadatos de NIF.
 *
 * Por cada pedido (en postmeta y en HPOS):
 * - Migra la clave heredada `_billing_nif` / `_shipping_nif` a la canónica
 *   `billing_nif` / `shipping_nif` cuando esta no existe.
 * - Elimina la clave heredada cuando ya existe la canónica.
 * - Elimina filas canónicas duplicadas, conservando una.
 *
 * Toda la lógica vive en apg_nif_normaliza_meta_duplicados() (incluida con el
 * plugin) para que este script y el botón del panel hagan exactamente lo mismo.
 *
 * USO CON WP-CLI (recomendado):
 *   wp eval-file wp-content/plugins/wc-apg-nifcifnie-field/includes/admin/limpieza-meta-duplicados.php
 *
 * USO COMO SNIPPET (una sola vez):
 *   Añadir en functions.php del tema hijo o en un plugin MU:
 *
 *   add_action( 'admin_init', function () {
 *       if ( current_user_can( 'manage_woocommerce' ) && isset( $_GET['apg_nif_cleanup'] ) ) {
 *           require_once WP_CONTENT_DIR . '/plugins/wc-apg-nifcifnie-field/includes/admin/limpieza-meta-duplicados.php';
 *           exit;
 *       }
 *   } );
 *
 *   Luego acceder a: /wp-admin/?apg_nif_cleanup=1
 *   (eliminar el snippet cuando termine)
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	echo "WooCommerce no está activo.\n";
	exit;
}

if ( ! function_exists( 'apg_nif_normaliza_meta_duplicados' ) ) {
	echo "El plugin WC - APG NIF/CIF/NIE Field no está activo.\n";
	exit;
}

echo "Iniciando normalización de metadatos de NIF...\n\n";

$resultado = apg_nif_normaliza_meta_duplicados();

echo sprintf(
	"\nFinalizado. Pedidos afectados: %d. Filas de metadatos normalizadas: %d.\n",
	absint( $resultado['pedidos'] ),
	absint( $resultado['filas'] )
);
