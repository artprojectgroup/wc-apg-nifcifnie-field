<?php
//Igual no deberías poder abrirme
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sobreescribe la plantilla email-addresses.php.
 */
class APG_Plantilla_correos {
	//Inicializa las plantillas de correos electrónicos
	public function __construct() {
		add_filter( 'wc_get_template_part', array( $this, 'apg_nif_sobrescribe_la_ruta_de_plantilla' ), 10, 3 );
		add_filter( 'woocommerce_locate_template', array( $this, 'apg_nif_sobrescribe_la_plantilla' ), 10, 3 );
	}
	
	//Previene que salga el teléfono y el correo electrónico doble en los correos electrónicos
	public function apg_nif_sobrescribe_la_ruta_de_plantilla( $plantilla, $slug, $nombre ) {
		$directorio_de_plantilla = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/woocommerce/';
		if ( $nombre ) {
			$ruta = $directorio_de_plantilla . "{$slug}-{$nombre}.php";
		} else {
			$ruta = $directorio_de_plantilla . "{$slug}.php";
		}
		
		return file_exists( $ruta ) ? $ruta : $plantilla;
	}

	public function apg_nif_sobrescribe_la_plantilla( $plantilla, $nombre_de_plantilla, $ruta_de_plantilla ) {
		$directorio_de_plantilla	= untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/woocommerce/';
		$ruta						= $directorio_de_plantilla . $nombre_de_plantilla;

		return file_exists( $ruta ) ? $ruta : $plantilla;
	}
}
new APG_Plantilla_correos();