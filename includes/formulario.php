<?php
/**
 * Página de ajustes del plugin WC – APG NIF/CIF/NIE Field.
 *
 * Renderiza el formulario de opciones en el admin de WooCommerce
 * (etiquetas, placeholders, prioridad, obligatoriedad y validaciones
 * NIF/VIES/EORI), además de los scripts para mostrar/ocultar campos.
 *
 * @package WC_APG_NIFCIFNIE_Field
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

/**
 * Variables globales del plugin.
 *
 * @global array<string,string> $apg_nif           Metadatos estáticos del plugin (URLs, nombre, etc.).
 * @global array<string,mixed>  $apg_nif_settings  Opciones guardadas del plugin (puede estar vacío).
 */
global $apg_nif, $apg_nif_settings;

/**
 * Muestra notificaciones de la API de Settings.
 *
 * @see https://developer.wordpress.org/reference/functions/settings_errors/
 */
settings_errors();

/**
 * Control de tabulación para los campos del formulario.
 *
 * @var int $tab
 */
$tab = 1;
?>
<div class="wrap woocommerce">
	<h2>
		<?php esc_html_e( 'APG NIF/CIF/NIE field Options.', 'wc-apg-nifcifnie-field' ); ?>
	</h2>
	<h3><a href="<?php echo esc_url( $apg_nif['plugin_url'] ); ?>" title="Art Project Group"><?php echo esc_attr( $apg_nif['plugin'] ); ?></a></h3>
	<p>
		<?php esc_html_e( 'Add to WooCommerce a NIF/CIF/NIE field, validate the field before submit and let to the admin configure the billing and shipping forms.', 'wc-apg-nifcifnie-field' ); ?>
	</p>
	<?php include 'cuadro-informacion.php'; ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'apg_nif_settings_group' ); ?>
        <?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin image does not require attachment ID ?>
		<div class="cabecera"> <a href="<?php echo esc_url( $apg_nif['plugin_url'] ); ?>" title="<?php echo esc_attr( $apg_nif['plugin'] ); ?>" target="_blank"><img src="<?php echo esc_url( plugins_url( 'assets/images/cabecera.jpg', DIRECCION_apg_nif ) ); ?>" class="imagen" alt="<?php echo esc_attr( $apg_nif['plugin'] ); ?>" /></a> </div>
		<table class="form-table apg-table">
			<tr valign="top" class="campo">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[etiqueta]">
						<?php esc_html_e( 'Field label', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own field label.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[etiqueta]" name="apg_nif_settings[etiqueta]" type="text" value="<?php echo ( isset( $apg_nif_settings['etiqueta'] ) && ! empty( $apg_nif_settings['etiqueta'] ) ? esc_attr( $apg_nif_settings['etiqueta'] ) : 'NIF/CIF/NIE' ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="NIF/CIF/NIE" /></td>
			</tr>
			<tr valign="top" class="campo">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[placeholder]">
						<?php esc_html_e( 'Field placeholder', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own field placeholder.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[placeholder]" name="apg_nif_settings[placeholder]" type="text" value="<?php echo ( isset( $apg_nif_settings['placeholder'] ) && ! empty( $apg_nif_settings['placeholder'] ) ? esc_attr( $apg_nif_settings['placeholder'] ) : esc_attr__( 'NIF/CIF/NIE number', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[error]">
						<?php esc_html_e( 'Error message', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own error message.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[error]" name="apg_nif_settings[error]" type="text" value="<?php echo ( isset( $apg_nif_settings['error'] ) && ! empty( $apg_nif_settings['error'] ) ? esc_attr( $apg_nif_settings['error'] ) : esc_attr__( 'Please enter a valid NIF/CIF/NIE.', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="<?php esc_attr_e( 'Please enter a valid NIF/CIF/NIE.', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc"> <label for="apg_nif_settings[prioridad]">
						<?php esc_html_e( 'Field priority', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Enter the field priority.', 'wc-apg-nifcifnie-field' ); ?>"></span> </label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[prioridad]" name="apg_nif_settings[prioridad]" type="number" value="<?php echo ( isset( $apg_nif_settings['prioridad'] ) && ! empty( $apg_nif_settings['prioridad'] ) ? esc_attr( $apg_nif_settings['prioridad'] ) : 31 ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="31" />
					<p class="description"><?php
						esc_html_e( 'Your current values are:', 'wc-apg-nifcifnie-field' );
						echo '<ol>';
						$campos = WC()->countries->get_address_fields( WC()->countries->get_base_country(), 'billing_' );
						foreach ( $campos as $campo ) {
							$etiqueta  = ( isset( $campo['label'] ) ) ? $campo['label'] : esc_html__( 'No label exists', 'wc-apg-nifcifnie-field' );
							$prioridad = ( isset( $campo['priority'] ) ) ? $campo['priority'] : esc_html__( 'No priority', 'wc-apg-nifcifnie-field' );
							echo '<li>' . esc_html( $etiqueta ) . ': ' . esc_html( $prioridad ) . '.</li>';
						}
						echo '</ol>';
                        ?></p></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[requerido]">
						<?php esc_html_e( 'Require billing field?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you need to require the field.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[requerido]" name="apg_nif_settings[requerido]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings['requerido'] ) ? $apg_nif_settings['requerido'] : '', 1 ); ?> tabindex="<?php echo esc_html( $tab ); $tab++; ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[requerido_envio]">
						<?php esc_html_e( 'Require shipping field?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you need to require the field.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[requerido_envio]" name="apg_nif_settings[requerido_envio]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings['requerido_envio'] ) ? $apg_nif_settings['requerido_envio'] : '', 1 ); ?> tabindex="<?php echo esc_html( $tab ); $tab++; ?>" /></td>
			</tr>
			<tr valign="top" id="requerido">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion]">
						<?php esc_html_e( 'Validate field?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you want to validate the field before submit.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[validacion]" name="apg_nif_settings[validacion]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings['validacion'] ) ? $apg_nif_settings['validacion'] : '', 1 ); ?> tabindex="<?php echo esc_html( $tab ); $tab++; ?>" /></td>
			</tr>
			<?php if ( class_exists( 'SoapClient' ) ) : ?>
			<tr valign="top" id="vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion_vies]">
						<?php esc_html_e( 'Allow VIES VAT number?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you want to allow and validate VIES VAT number.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input class="muestra_vies" id="apg_nif_settings[validacion_vies]" name="apg_nif_settings[validacion_vies]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings['validacion_vies'] ) ? $apg_nif_settings['validacion_vies'] : '', 1 ); ?> tabindex="<?php echo esc_html( $tab ); $tab++; ?>" /></td>
			</tr>
			<tr valign="top" class="vies campo_vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[etiqueta_vies]">
						<?php esc_html_e( 'VIES VAT number field label', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own VIES VAT number field label.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[etiqueta_vies]" name="apg_nif_settings[etiqueta_vies]" type="text" value="<?php echo ( isset( $apg_nif_settings['etiqueta_vies'] ) && ! empty( $apg_nif_settings['etiqueta_vies'] ) ? esc_attr( $apg_nif_settings['etiqueta_vies'] ) : 'NIF/CIF/NIE/VAT number' ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE/VAT number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" class="vies campo_vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[placeholder_vies]">
						<?php esc_html_e( 'VIES VAT number field placeholder', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own VIES VAT number field placeholder.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[placeholder_vies]" name="apg_nif_settings[placeholder_vies]" type="text" value="<?php echo ( isset( $apg_nif_settings['placeholder_vies'] ) && ! empty( $apg_nif_settings['placeholder_vies'] ) ? esc_attr( $apg_nif_settings['placeholder_vies'] ) : esc_attr__( 'NIF/CIF/NIE/VAT number', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE/VAT number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" class="vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[error_vies]">
						<?php esc_html_e( 'VIES VAT number error message', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own VIES VAT number error message.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[error_vies]" name="apg_nif_settings[error_vies]" type="text" value="<?php echo ( isset( $apg_nif_settings['error_vies'] ) && ! empty( $apg_nif_settings['error_vies'] ) ? esc_attr( $apg_nif_settings['error_vies'] ) : esc_attr__( 'Please enter a valid VIES VAT number.', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="<?php esc_attr_e( 'Please enter a valid VIES VAT number.', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" class="vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[error_vies_max]">
						<?php esc_html_e( 'VIES VAT number request error message', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own VIES VAT number request error message.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[error_vies_max]" name="apg_nif_settings[error_vies_max]" type="text" value="<?php echo ( isset( $apg_nif_settings['error_vies_max'] ) && ! empty( $apg_nif_settings['error_vies_max'] ) ? esc_attr( $apg_nif_settings['error_vies_max'] ) : esc_attr__( 'Error: maximum number of concurrent requests exceeded.', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="<?php esc_attr_e( 'Please enter a valid VIES VAT number.', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" id="eori">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion_eori]">
						<?php esc_html_e( 'Allow EORI number?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you want to allow and validate EORI number.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input class="muestra_eori" id="apg_nif_settings[validacion_eori]" name="apg_nif_settings[validacion_eori]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings['validacion_eori'] ) ? $apg_nif_settings['validacion_eori'] : '', 1 ); ?> tabindex="<?php echo esc_html( $tab ); $tab++; ?>" /></td>
			</tr>
			<?php
			if ( ! function_exists( 'apg_nif_amplia_paises' ) ) {
				/**
				 * Amplía el listado de países de la UE aceptados por WooCommerce.
				 *
				 * Añade Reino Unido (GB), Noruega (NO), Suiza (CH) y Tailandia (TH)
				 * al array que devuelve el filtro `woocommerce_european_union_countries`.
				 *
				 * @param array<int,string> $countries Códigos de país de la UE.
				 * @param string            $type      Contexto del filtro (no usado).
				 * @return array<int,string> Lista extendida de códigos de país.
				 */
				function apg_nif_amplia_paises( $countries, $type ) {
					array_push( $countries, 'GB', 'NO', 'CH', 'TH' );

					return $countries;
				}
			}
			add_filter( 'woocommerce_european_union_countries', 'apg_nif_amplia_paises', 10, 2 );

			// Variables para el multiselect de países (EORI).
			/** @var array<int,string> $seleccion Países previamente seleccionados. */
			$seleccion = isset( $apg_nif_settings['eori_paises'] ) ? (array) $apg_nif_settings['eori_paises'] : array();

			/** @var \WC_Countries $countries Objeto de países. */
			$countries = new WC_Countries();

			/** @var array<int,string> $europa Códigos de países de la UE. */
			$europa = $countries->get_european_union_countries();

			/** @var array<string,string> $countries Listado completo de países (código => nombre). */
			$countries = WC()->countries->countries;

			asort( $countries );
			?>
			<tr valign="top" class="eori">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[eori_paises]">
						<?php esc_html_e( 'Countries to validate EORI number', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Select the list of countries where the EORI number must be validated.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp">
					<select multiple="multiple" name="apg_nif_settings[eori_paises][]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose countries / regions&hellip;', 'wc-apg-nifcifnie-field' ); ?>" aria-label="<?php esc_attr_e( 'Country / Region', 'wc-apg-nifcifnie-field' ); ?>" class="wc-enhanced-select">
						<?php
						if ( ! empty( $countries ) ) {
							foreach ( $countries as $key => $val ) {
								if ( in_array( $key, $europa, true ) ) {
									echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( wc_selected( $key, $seleccion ) ) . '>' . esc_html( $val ) . '</option>';
								}
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr valign="top" class="eori">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[etiqueta_eori]">
						<?php esc_html_e( 'EORI number field label', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own EORI number field label.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[etiqueta_eori]" name="apg_nif_settings[etiqueta_eori]" type="text" value="<?php echo ( isset( $apg_nif_settings['etiqueta_eori'] ) && ! empty( $apg_nif_settings['etiqueta_eori'] ) ? esc_attr( $apg_nif_settings['etiqueta_eori'] ) : 'NIF/CIF/NIE/EORI number' ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE/EORI number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" class="eori">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[placeholder_eori]">
						<?php esc_html_e( 'EORI number field placeholder', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own EORI number field placeholder.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[placeholder_eori]" name="apg_nif_settings[placeholder_eori]" type="text" value="<?php echo ( isset( $apg_nif_settings['placeholder_eori'] ) && ! empty( $apg_nif_settings['placeholder_eori'] ) ? esc_attr( $apg_nif_settings['placeholder_eori'] ) : esc_attr__( 'NIF/CIF/NIE/EORI number', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE/EORI number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" class="eori">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[error_eori]">
						<?php esc_html_e( 'EORI number error message', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own EORI number error message.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[error_eori]" name="apg_nif_settings[error_eori]" type="text" value="<?php echo ( isset( $apg_nif_settings['error_eori'] ) && ! empty( $apg_nif_settings['error_eori'] ) ? esc_attr( $apg_nif_settings['error_eori'] ) : esc_attr__( 'Please enter a valid EORI number.', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo esc_html( $tab ); $tab++; ?>" placeholder="<?php esc_attr_e( 'Please enter a valid EORI number.', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>			
            <?php endif; ?>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
<style>
/* Oculta los campos */
.vies, .eori {
    display: none;
}
</style>
<script>
jQuery( document ).ready( function( $ ) {
    // Muestra u oculta los campos VIES/EORI según los checkboxes.
    ( function( $ ) {
        $.fn.comprueba_campos = function() {
            // Muestra u oculta los campos nativos.
            if ( $( ".muestra_vies" ).is( ":checked" ) || $( ".muestra_eori" ).is( ":checked" ) ) {
                $( ".campo" ).hide();
            } else {
                $( ".campo" ).show();
            }
			
            // Muestra u oculta los campos VIES.
            if ( $( ".muestra_eori" ).is( ":checked" ) ) {
                $( ".campo_vies" ).hide();
            } else if ( $( ".muestra_vies" ).is( ":checked" ) ) {
                $( ".campo_vies" ).show();
            }
        }; 
    } )( jQuery );
    
    /* VIES */
    if ( $( ".muestra_vies" ).is( ":checked" ) ) { // Muestra los campos.
        $( '.vies' ).toggle().comprueba_campos();
    }
    $( ".muestra_vies" ).change( function() { // Cambia la visualización según valor.
        $( '.vies' ).toggle().comprueba_campos();
    });
    /* EORI */
    if ( $( ".muestra_eori" ).is( ":checked" ) ) { // Muestra los campos.
        $( '.eori' ).toggle().comprueba_campos();
    }
    $( ".muestra_eori" ).change( function() { // Cambia la visualización según valor.
        $( '.eori' ).toggle().comprueba_campos();
    });              
});
</script>
