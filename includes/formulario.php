<?php
global $apg_nif, $apg_nif_settings;

settings_errors(); 
$tab    = 1;
?>
<div class="wrap woocommerce">
	<h2>
		<?php esc_attr_e( 'APG NIF/CIF/NIE field Options.', 'wc-apg-nifcifnie-field' ); ?>
	</h2>
	<h3><a href="<?php echo $apg_nif[ 'plugin_url' ]; ?>" title="Art Project Group"><?php echo $apg_nif[ 'plugin' ]; ?></a></h3>
	<p>
		<?php esc_attr_e( 'Add to WooCommerce a NIF/CIF/NIE field, validate the field before submit and let to the admin configure the billing and shipping forms.', 'wc-apg-nifcifnie-field' ); ?>
	</p>
	<?php include( 'cuadro-informacion.php' ); ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'apg_nif_settings_group' ); ?>
		<div class="cabecera"> <a href="<?php echo $apg_nif[ 'plugin_url' ]; ?>" title="<?php echo $apg_nif[ 'plugin' ]; ?>" target="_blank"><img src="<?php echo plugins_url( 'assets/images/cabecera.jpg', DIRECCION_apg_nif ); ?>" class="imagen" alt="<?php echo $apg_nif[ 'plugin' ]; ?>" /></a> </div>
		<table class="form-table apg-table">
			<tr valign="top" class="campo">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[etiqueta]">
						<?php esc_attr_e( 'Field label', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own field label.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[etiqueta]" name="apg_nif_settings[etiqueta]" type="text" value="<?php echo ( isset( $apg_nif_settings[ 'etiqueta' ] ) && ! empty( $apg_nif_settings[ 'etiqueta' ] ) ? esc_attr( $apg_nif_settings[ 'etiqueta' ] ) : 'NIF/CIF/NIE' ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="NIF/CIF/NIE" /></td>
			</tr>
			<tr valign="top" class="campo">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[placeholder]">
						<?php esc_attr_e( 'Field placeholder', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own field placeholder.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[placeholder]" name="apg_nif_settings[placeholder]" type="text" value="<?php echo ( isset( $apg_nif_settings[ 'placeholder' ] ) && ! empty( $apg_nif_settings[ 'placeholder' ] ) ? esc_attr( $apg_nif_settings[ 'placeholder' ] ) : __( 'NIF/CIF/NIE number', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[error]">
						<?php esc_attr_e( 'Error message', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own error message.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[error]" name="apg_nif_settings[error]" type="text" value="<?php echo ( isset( $apg_nif_settings[ 'error' ] ) && ! empty( $apg_nif_settings[ 'error' ] ) ? esc_attr( $apg_nif_settings[ 'error' ] ) : __( 'Please enter a valid NIF/CIF/NIE.', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="<?php esc_attr_e( 'Please enter a valid NIF/CIF/NIE.', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc"> <label for="apg_nif_settings[prioridad]">
						<?php esc_attr_e( 'Field priority', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Enter the field priority.', 'wc-apg-nifcifnie-field' ); ?>"></span> </label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[prioridad]" name="apg_nif_settings[prioridad]" type="number" value="<?php echo ( isset( $apg_nif_settings[ 'prioridad' ] ) && ! empty( $apg_nif_settings[ 'prioridad' ] ) ? esc_attr( $apg_nif_settings[ 'prioridad' ] ) : 31 ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="31" />
					<p class="description"><?php
                        esc_attr_e( 'Your current values are:', 'wc-apg-nifcifnie-field' );
                        echo "<ol>";
                        $campos = WC()->countries->get_address_fields( WC()->countries->get_base_country(), 'billing_' );
                        foreach ( $campos as $campo ) {
                            echo "<li>{$campo[ 'label' ]}: {$campo[ 'priority' ]}.</li>";
                        }
                        echo "</ol>";
                        ?></p></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[requerido]">
						<?php esc_attr_e( 'Require billing field?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you need to require the field.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[requerido]" name="apg_nif_settings[requerido]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings[ 'requerido' ] ) ? $apg_nif_settings[ 'requerido' ] : '', 1 ); ?> tabindex="<?php echo $tab++; ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[requerido_envio]">
						<?php esc_attr_e( 'Require shipping field?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you need to require the field.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[requerido_envio]" name="apg_nif_settings[requerido_envio]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings[ 'requerido_envio' ] ) ? $apg_nif_settings[ 'requerido_envio' ] : '', 1 ); ?> tabindex="<?php echo $tab++; ?>" /></td>
			</tr>
			<tr valign="top" id="requerido">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion]">
						<?php esc_attr_e( 'Validate field?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you want to validate the field before submit.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[validacion]" name="apg_nif_settings[validacion]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings[ 'validacion' ] ) ? $apg_nif_settings[ 'validacion' ] : '', 1 ); ?> tabindex="<?php echo $tab++; ?>" /></td>
			</tr>
			<?php if ( class_exists( 'Soapclient' ) ) : ?>
			<tr valign="top" id="vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion_vies]">
						<?php esc_attr_e( 'Allow VIES VAT number?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you want to allow and validate VIES VAT number.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input class="muestra_vies" id="apg_nif_settings[validacion_vies]" name="apg_nif_settings[validacion_vies]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings[ 'validacion_vies' ] ) ? $apg_nif_settings[ 'validacion_vies' ] : '', 1 ); ?> tabindex="<?php echo $tab++; ?>" /></td>
			</tr>
			<tr valign="top" class="vies campo_vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[etiqueta_vies]">
						<?php esc_attr_e( 'VIES VAT number field label', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own VIES VAT number field label.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[etiqueta_vies]" name="apg_nif_settings[etiqueta_vies]" type="text" value="<?php echo ( isset( $apg_nif_settings[ 'etiqueta_vies' ] ) && ! empty( $apg_nif_settings[ 'etiqueta_vies' ] ) ? esc_attr( $apg_nif_settings[ 'etiqueta_vies' ] ) : 'NIF/CIF/NIE/VAT number' ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE/VAT number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" class="vies campo_vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[placeholder_vies]">
						<?php esc_attr_e( 'VIES VAT number field placeholder', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own VIES VAT number field placeholder.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[placeholder_vies]" name="apg_nif_settings[placeholder_vies]" type="text" value="<?php echo ( isset( $apg_nif_settings[ 'placeholder_vies' ] ) && ! empty( $apg_nif_settings[ 'placeholder_vies' ] ) ? esc_attr( $apg_nif_settings[ 'placeholder_vies' ] ) : __( 'NIF/CIF/NIE/VAT number', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE/VAT number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" class="vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[error_vies]">
						<?php esc_attr_e( 'VIES VAT number error message', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own VIES VAT number error message.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[error_vies]" name="apg_nif_settings[error_vies]" type="text" value="<?php echo ( isset( $apg_nif_settings[ 'error_vies' ] ) && ! empty( $apg_nif_settings[ 'error_vies' ] ) ? esc_attr( $apg_nif_settings[ 'error_vies' ] ) : __( 'Please enter a valid VIES VAT number.', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="<?php esc_attr_e( 'Please enter a valid VIES VAT number.', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" id="eori">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion_eori]">
						<?php esc_attr_e( 'Allow EORI number?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you want to allow and validate EORI number.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input class="muestra_eori" id="apg_nif_settings[validacion_eori]" name="apg_nif_settings[validacion_eori]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings[ 'validacion_eori' ] ) ? $apg_nif_settings[ 'validacion_eori' ] : '', 1 ); ?> tabindex="<?php echo $tab++; ?>" /></td>
			</tr>
            <?php
            //Amplía la lista de países de la Unión Europa con Reino Unido, Noruega y Suiza
            function apg_nif_amplia_paises( $countries, $type ) { 
                array_push( $countries, 'GB', 'NO', 'CH', 'TH' );

                return $countries;
            }
            add_filter( 'woocommerce_european_union_countries', 'apg_nif_amplia_paises', 10, 2 );
            
            //Variables
            $seleccion  = isset( $apg_nif_settings[ 'eori_paises' ] ) ? (array) $apg_nif_settings[ 'eori_paises' ] : []; //Países seleccionados previamente
            $countries  = new WC_Countries();
            $europa     = $countries->get_european_union_countries(); //Países de la Unión Europea
            $countries  = WC()->countries->countries; //Listado completo de países
            asort( $countries );            
            ?>
            <tr valign="top" class="eori">
                <th scope="row" class="titledesc">
					<label for="apg_nif_settings[eori_paises]">
						<?php esc_attr_e( 'Countries to validate EORI number', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Select the list of countries where the EORI number must be validated.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
                </th>
                <td class="forminp">
                    <select multiple="multiple" name="apg_nif_settings[eori_paises][]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose countries / regions&hellip;', 'woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Country / Region', 'woocommerce' ); ?>" class="wc-enhanced-select">
                        <?php
                        if ( ! empty( $countries ) ) {
                            foreach ( $countries as $key => $val ) {
                                if ( in_array( $key, $europa ) ) {
                                    echo '<option value="' . esc_attr( $key ) . '"' . wc_selected( $key, $seleccion ) . '>' . esc_html( $val ) . '</option>'; // WPCS: XSS ok.                                    
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
						<?php esc_attr_e( 'EORI number field label', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own EORI number field label.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[etiqueta_eori]" name="apg_nif_settings[etiqueta_eori]" type="text" value="<?php echo ( isset( $apg_nif_settings[ 'etiqueta_eori' ] ) && ! empty( $apg_nif_settings[ 'etiqueta_eori' ] ) ? esc_attr( $apg_nif_settings[ 'etiqueta_eori' ] ) : 'NIF/CIF/NIE/EORI number' ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE/EORI number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" class="eori">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[placeholder_eori]">
						<?php esc_attr_e( 'EORI number field placeholder', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own EORI number field placeholder.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[placeholder_eori]" name="apg_nif_settings[placeholder_eori]" type="text" value="<?php echo ( isset( $apg_nif_settings[ 'placeholder_eori' ] ) && ! empty( $apg_nif_settings[ 'placeholder_eori' ] ) ? esc_attr( $apg_nif_settings[ 'placeholder_eori' ] ) : __( 'NIF/CIF/NIE/EORI number', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="<?php esc_attr_e( 'NIF/CIF/NIE/EORI number', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>
			<tr valign="top" class="eori">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[error_eori]">
						<?php esc_attr_e( 'EORI number error message', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Type your own EORI number error message.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[error_eori]" name="apg_nif_settings[error_eori]" type="text" value="<?php echo ( isset( $apg_nif_settings[ 'error_eori' ] ) && ! empty( $apg_nif_settings[ 'error_eori' ] ) ? esc_attr( $apg_nif_settings[ 'error_eori' ] ) : __( 'Please enter a valid EORI number.', 'wc-apg-nifcifnie-field' ) ); ?>" tabindex="<?php echo $tab++; ?>" placeholder="<?php esc_attr_e( 'Please enter a valid EORI number.', 'wc-apg-nifcifnie-field' ); ?>" /></td>
			</tr>			
			<?php endif; ?>
            <tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[campos]">
						<?php esc_attr_e( 'Remove extra fields', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Check if you need to remove phone and email fields from the address.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[campos]" name="apg_nif_settings[campos]" type="checkbox" value="1" <?php checked( isset( $apg_nif_settings[ 'campos' ] ) ? $apg_nif_settings[ 'campos' ] : '', 1 ); ?> tabindex="<?php echo $tab++; ?>" /></td>
			</tr>
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
    //Muestra u oculta los campos
    ( function( $ ) {
        $.fn.comprueba_campos = function() {
            //Muestra u oculta ls campos nativos
            if ( $( ".muestra_vies" ).is( ":checked" ) || $( ".muestra_eori" ).is( ":checked" ) ) {
                $( ".campo" ).hide();
            } else {
                $( ".campo" ).show();
            }
            //Muestra u oculta ls campos VIES
            if ( $( ".muestra_eori" ).is( ":checked" ) ) {
                $( ".campo_vies" ).hide();
            } else if ( $( ".muestra_vies" ).is( ":checked" ) ) {
                $( ".campo_vies" ).show();
            }
        }; 
    } )( jQuery );
    
    /* VIES */
    if ( $( ".muestra_vies" ).is( ":checked" ) ) { //Muestra los campos
        $( '.vies' ).toggle().comprueba_campos();
    }
    $( ".muestra_vies" ).change( function() { //Cambia la visualización según valor
        $( '.vies' ).toggle().comprueba_campos();
    });
    /* EORI */
    if ( $( ".muestra_eori" ).is( ":checked" ) ) { //Muestra los campos
        $( '.eori' ).toggle().comprueba_campos();
    }
    $( ".muestra_eori" ).change( function() { //Cambia la visualización según valor
        $( '.eori' ).toggle().comprueba_campos();
    });              
});
</script>