<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

/**
 * Sobreescribe plantillas originales de WooCommerce: email-addresses.php y order-details-customer.php
 */
//Previene que salga el teléfono y el correo electrónico doble en los correos electrónicos y en los detalles del cliente
class APG_Plantilla_correos {
	//Inicializa las plantillas
	public function __construct() {
		global $apg_nif_settings;
        
		if ( isset( $apg_nif_settings[ 'campos' ] ) && $apg_nif_settings[ 'campos' ] != "1" ) {	
            add_filter( 'wc_get_template_part', [ $this, 'apg_nif_sobrescribe_la_ruta_de_plantilla' ], 10, 3 );
            add_filter( 'woocommerce_locate_template', [ $this, 'apg_nif_sobrescribe_la_plantilla' ], 10, 3 );
        }
	}
	
    //Cambia la ruta nativa
	public function apg_nif_sobrescribe_la_ruta_de_plantilla( $plantilla, $slug, $nombre ) {
		$directorio_de_plantilla = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/woocommerce/';
		if ( $nombre ) {
			$ruta = $directorio_de_plantilla . "{$slug}-{$nombre}.php";
		} else {
			$ruta = $directorio_de_plantilla . "{$slug}.php";
		}
		
		return file_exists( $ruta ) ? $ruta : $plantilla;
	}
    
	//Cambia la plantilla nativa
	public function apg_nif_sobrescribe_la_plantilla( $plantilla, $nombre_de_plantilla, $ruta_de_plantilla ) {
		$directorio_de_plantilla	= untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/woocommerce/';
		$ruta						= $directorio_de_plantilla . $nombre_de_plantilla;

		return file_exists( $ruta ) ? $ruta : $plantilla;
	}
}
new APG_Plantilla_correos();