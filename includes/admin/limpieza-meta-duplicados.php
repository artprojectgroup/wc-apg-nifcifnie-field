<?php
/**
 * Script de limpieza de metadatos duplicados de NIF.
 *
 * Elimina filas duplicadas de billing_nif/shipping_nif en wc_orders_meta
 * que pudieron crearse con versiones anteriores del plugin cuando el checkout
 * estaba basado en bloques.
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

$claves        = array( 'billing_nif', 'shipping_nif' );
$lote          = 50;
$pagina        = 1;
$total_pedidos = 0;
$total_limpios = 0;

echo "Iniciando limpieza de metadatos duplicados de NIF...\n\n";

do {
	$pedidos = wc_get_orders( array(
		'limit'  => $lote,
		'page'   => $pagina,
		'return' => 'ids',
		'status' => 'any',
	) );

	foreach ( $pedidos as $id ) {
		$order   = wc_get_order( $id );
		$limpiado = false;

		if ( ! $order instanceof WC_Order ) {
			continue;
		}

		foreach ( $claves as $key ) {
			$metas = array_values(
				array_filter(
					$order->get_meta_data(),
					function ( $m ) use ( $key ) {
						return $m->key === $key;
					}
				)
			);

			if ( count( $metas ) > 1 ) {
				$valor = $metas[0]->value;
				$order->delete_meta_data( $key );
				$order->add_meta_data( $key, $valor );
				$limpiado = true;

				echo sprintf(
					"  Pedido #%d: eliminados %d duplicados de '%s' (valor conservado: '%s')\n",
					absint( $id ),
					absint( count( $metas ) - 1 ),
					esc_html( $key ),
					esc_html( $valor )
				);
			}
		}

		if ( $limpiado ) {
			$order->save();
			$total_limpios++;
		}

		$total_pedidos++;
	}

	$pagina++;
} while ( count( $pedidos ) === $lote );

echo sprintf(
	"\nFinalizado. Pedidos revisados: %d. Pedidos con duplicados eliminados: %d.\n",
	absint( $total_pedidos ),
	absint( $total_limpios )
);
