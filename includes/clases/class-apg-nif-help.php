<?php
/**
 * Ayuda contextual de la página de ajustes: la pestaña «Ayuda» nativa de WordPress, con un panel
 * por cada bloque de la pantalla y una entrada por cada campo. La versión completa vive en la web
 * y se enlaza desde el lateral.
 *
 * @package WC_APG_NIFCIFNIE_Field
 */

defined( 'ABSPATH' ) || exit;

/**
 * Añade los paneles de ayuda a la pantalla de ajustes.
 */
class APG_NIF_Help {

	/**
	 * Dirección de la documentación completa, en español.
	 */
	const DOCS_URL = 'https://artprojectgroup.es/documentacion/wc-apg-campo-nif-cif-nie';

	/**
	 * Dirección de la documentación completa, en inglés.
	 */
	const DOCS_URL_EN = 'https://artprojectgroup.com/documentation/wc-apg-campo-nif-cif-nie';

	/**
	 * Sustituye la ayuda de la pantalla de ajustes por los paneles del plugin.
	 *
	 * La pantalla no tiene pestañas: es una página única con varios bloques, así que se añaden
	 * todos los paneles de una vez, uno por bloque y en el orden en que aparecen. Se engancha en
	 * load-{hook de la página de ajustes}.
	 *
	 * @return void
	 */
	public static function add_tabs() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		// La pantalla cuelga del menú de WooCommerce y WooCommerce ya ha añadido sus propios
		// paneles y su lateral (en current_screen, antes que este load-): sólo queda la ayuda de
		// este plugin.
		$screen->remove_help_tabs();

		foreach ( self::get_panels() as $id => $panel ) {
			$screen->add_help_tab(
				array(
					'id'      => 'apg_nif_help_' . $id,
					'title'   => $panel['title'],
					'content' => self::render( $panel['items'] ),
				)
			);
		}

		$screen->set_help_sidebar(
			'<p><strong>' . esc_html__( 'More help', 'wc-apg-nifcifnie-field' ) . '</strong></p>' .
			'<p><a href="' . esc_url( self::documentacion() ) . '" target="_blank">' . esc_html__( 'Full documentation', 'wc-apg-nifcifnie-field' ) . '</a></p>'
		);
	}

	/**
	 * Dirección de la documentación en el idioma de quien lee la pantalla.
	 *
	 * @return string
	 */
	private static function documentacion() {
		return ( 0 === strpos( get_user_locale(), 'es' ) ) ? self::DOCS_URL : self::DOCS_URL_EN;
	}

	/**
	 * Pinta un panel: un párrafo de entrada y una lista de término => explicación.
	 *
	 * @param array $items Primer elemento: texto de entrada (cadena). Después, término => explicación.
	 * @return string
	 */
	private static function render( array $items ) {
		$html = '';
		$list = '';

		foreach ( $items as $term => $text ) {
			if ( is_int( $term ) ) {
				$html .= '<p>' . esc_html( $text ) . '</p>';
			} else {
				$list .= '<li><strong>' . esc_html( $term ) . '</strong>: ' . esc_html( $text ) . '</li>';
			}
		}

		return $html . ( '' !== $list ? '<ul>' . $list . '</ul>' : '' );
	}

	/**
	 * Paneles de la pantalla de ajustes, en el orden de sus bloques.
	 *
	 * @return array ID del panel => array( title, items ).
	 */
	private static function get_panels() {
		return array(
			'field'      => array(
				'title' => __( 'The field', 'wc-apg-nifcifnie-field' ),
				'items' => array(
					__( 'How the NIF/CIF/NIE field looks on the billing and shipping forms. Leave any of these boxes empty and the field falls back to the default value shown in grey. The first two boxes leave this screen as soon as you switch the VIES VAT number or the EORI number on: from then on the label of that number is the one in use, for every customer.', 'wc-apg-nifcifnie-field' ),
					__( 'Field label', 'wc-apg-nifcifnie-field' )       => __( 'the name the customer reads above the field. NIF/CIF/NIE by default.', 'wc-apg-nifcifnie-field' ),
					__( 'Field placeholder', 'wc-apg-nifcifnie-field' ) => __( 'the hint shown inside the empty field. NIF/CIF/NIE number by default.', 'wc-apg-nifcifnie-field' ),
					__( 'Error message', 'wc-apg-nifcifnie-field' )     => __( 'what the customer reads when the number is not valid. Please enter a valid NIF/CIF/NIE. by default.', 'wc-apg-nifcifnie-field' ),
					__( 'Field priority', 'wc-apg-nifcifnie-field' )    => __( 'where the field sits in the form: the lower the number, the higher it goes. It is 31 by default, which places it right after the company name. The screen lists the priorities your other fields are using, so you can pick a number that puts it where you want.', 'wc-apg-nifcifnie-field' ),
				),
			),

			'required'   => array(
				'title' => __( 'When it is required', 'wc-apg-nifcifnie-field' ),
				'items' => array(
					__( 'Which forms show the field and when the customer is forced to fill it in. Billing and shipping are set separately.', 'wc-apg-nifcifnie-field' ),
					__( 'Require billing field?', 'wc-apg-nifcifnie-field' )                 => __( 'the order cannot be placed without a number on the billing form.', 'wc-apg-nifcifnie-field' ),
					__( 'Require billing field from this amount', 'wc-apg-nifcifnie-field' ) => __( 'makes the billing field required only for orders of that amount or more, taxes included, even when the box above is unticked. Leave it empty to switch it off. It never affects the shipping form.', 'wc-apg-nifcifnie-field' ),
					__( 'Require shipping field?', 'wc-apg-nifcifnie-field' )                => __( 'the same, for the shipping form. It only has an effect when the field is shown there.', 'wc-apg-nifcifnie-field' ),
					__( 'Show shipping field?', 'wc-apg-nifcifnie-field' )                   => __( 'adds the field to the shipping form as well. Unticked, the field exists only on the billing form.', 'wc-apg-nifcifnie-field' ),
				),
			),

			'validation' => array(
				'title' => __( 'Validation', 'wc-apg-nifcifnie-field' ),
				'items' => array(
					__( 'Whether the number is checked before the order goes through, or simply stored as typed.', 'wc-apg-nifcifnie-field' ),
					__( 'Validate field?', 'wc-apg-nifcifnie-field' ) => __( 'checks that the number is a real NIF, CIF or NIE, letter included, and stops the order with the error message above if it is not. Unticked, anything the customer types is accepted.', 'wc-apg-nifcifnie-field' ),
				),
			),

			'vies'       => array(
				'title' => __( 'VIES VAT number', 'wc-apg-nifcifnie-field' ),
				'items' => array(
					__( 'Lets customers from the rest of the European Union enter their VAT number, which is checked live against the VIES register of the European Commission. It needs the SoapClient PHP class: without it the plugin warns you and keeps this switched off. With the EORI number switched on as well, the two boxes that set the label leave this screen, because the EORI ones take over.', 'wc-apg-nifcifnie-field' ),
					__( 'Allow VIES VAT number?', 'wc-apg-nifcifnie-field' )                  => __( 'switches the VAT number on. The four settings below only matter once it is ticked.', 'wc-apg-nifcifnie-field' ),
					__( 'VIES VAT number field label', 'wc-apg-nifcifnie-field' )             => __( 'the label the whole field takes, for every customer and not only those outside your country, as soon as the VIES VAT number is switched on. NIF/CIF/NIE/VAT number by default.', 'wc-apg-nifcifnie-field' ),
					__( 'VIES VAT number field placeholder', 'wc-apg-nifcifnie-field' )       => __( 'the hint inside that field, also NIF/CIF/NIE/VAT number by default.', 'wc-apg-nifcifnie-field' ),
					__( 'VIES VAT number error message', 'wc-apg-nifcifnie-field' )           => __( 'what the customer reads when VIES answers that the number does not exist. Please enter a valid VIES VAT number. by default.', 'wc-apg-nifcifnie-field' ),
					__( 'VIES VAT number request error message', 'wc-apg-nifcifnie-field' )   => __( 'what the customer reads when VIES cannot answer because the shop has made too many requests in a short time. This is not a wrong number: it is the register asking you to wait.', 'wc-apg-nifcifnie-field' ),
				),
			),

			'eori'       => array(
				'title' => __( 'EORI number', 'wc-apg-nifcifnie-field' ),
				'items' => array(
					__( 'The number customs uses to identify a business that imports or exports. Useful for shops that sell to companies outside the European Union.', 'wc-apg-nifcifnie-field' ),
					__( 'Allow EORI number?', 'wc-apg-nifcifnie-field' )              => __( 'switches the EORI number on. The four settings below only matter once it is ticked.', 'wc-apg-nifcifnie-field' ),
					__( 'Countries to validate EORI number', 'wc-apg-nifcifnie-field' ) => __( 'the countries whose customers are asked for an EORI number. Leave the list empty and no country is asked for it.', 'wc-apg-nifcifnie-field' ),
					__( 'EORI number field label', 'wc-apg-nifcifnie-field' )          => __( 'the label the whole field takes as soon as the EORI number is switched on, over the VIES one when both are on. NIF/CIF/NIE/EORI number by default.', 'wc-apg-nifcifnie-field' ),
					__( 'EORI number field placeholder', 'wc-apg-nifcifnie-field' )    => __( 'the hint inside that field, also NIF/CIF/NIE/EORI number by default.', 'wc-apg-nifcifnie-field' ),
					__( 'EORI number error message', 'wc-apg-nifcifnie-field' )        => __( 'what the customer reads when the EORI number is not valid. Please enter a valid EORI number. by default.', 'wc-apg-nifcifnie-field' ),
				),
			),

			'tools'      => array(
				'title' => __( 'Tools', 'wc-apg-nifcifnie-field' ),
				'items' => array(
					__( 'A one-off repair for shops that come from an older version of the plugin.', 'wc-apg-nifcifnie-field' ),
					__( 'Fix duplicate NIF metadata', 'wc-apg-nifcifnie-field' ) => __( 'removes the duplicated billing_nif and shipping_nif rows that older versions of the plugin left behind on orders placed through Checkout Blocks. You only need it if your shop took orders that way before updating, it can be run as many times as you like, and on a shop with many orders it takes a while.', 'wc-apg-nifcifnie-field' ),
				),
			),
		);
	}
}
