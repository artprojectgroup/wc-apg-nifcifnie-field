<?php global $apg_nif; ?>

<div class="wrap woocommerce">
	<h2>
		<?php _e( 'APG NIF/CIF/NIE field Options.', 'wc-apg-nifcifnie-field' ); ?>
	</h2>
	<?php 
	settings_errors(); 
	$tab				= 1;
	$apg_nif_settings	= get_option( 'apg_nif_settings' );
	?>
	<h3><a href="<?php echo $apg_nif['plugin_url']; ?>" title="Art Project Group"><?php echo $apg_nif['plugin']; ?></a></h3>
	<p>
		<?php _e( 'Add to WooCommerce a NIF/CIF/NIE field, validate the field before submit and let to the admin configure the billing and shipping forms.', 'wc-apg-nifcifnie-field' ); ?>
	</p>
	<?php include( 'cuadro-informacion.php' ); ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'apg_nif_settings_group' ); ?>
		<div class="cabecera"> <a href="<?php echo $apg_nif['plugin_url']; ?>" title="<?php echo $apg_nif['plugin']; ?>" target="_blank"><img src="<?php echo plugins_url( 'assets/images/cabecera.jpg', DIRECCION_apg_nif ); ?>" class="imagen" alt="<?php echo $apg_nif['plugin']; ?>" /></a> </div>
		<table class="form-table apg-table">
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[requerido]">
						<?php _e( 'Require billing field?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php _e( 'Check if you need to require the field.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[requerido]" name="apg_nif_settings[requerido]" type="checkbox" value="1" <?php echo ( isset( $apg_nif_settings[ 'requerido'] ) && $apg_nif_settings[ 'requerido'] == "1" ? 'checked="checked"' : '' ); ?> tabindex="
					<?php echo $tab++; ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[requerido_envio]">
						<?php _e( 'Require shipping field?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php _e( 'Check if you need to require the field.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[requerido_envio]" name="apg_nif_settings[requerido_envio]" type="checkbox" value="1" <?php echo ( isset( $apg_nif_settings[ 'requerido_envio'] ) && $apg_nif_settings[ 'requerido_envio'] == "1" ? 'checked="checked"' : '' ); ?> tabindex="
					<?php echo $tab++; ?>" /></td>
			</tr>
			<tr valign="top" id="requerido">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion]">
						<?php _e( 'Validate field?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php _e( 'Check if you want to validate the field before submit.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[validacion]" name="apg_nif_settings[validacion]" type="checkbox" value="1" <?php echo ( isset( $apg_nif_settings[ 'validacion'] ) && $apg_nif_settings[ 'validacion'] == "1" ? 'checked="checked"' : '' ); ?> tabindex="
					<?php echo $tab++; ?>" /></td>
			</tr>
			<?php if ( class_exists( 'Soapclient' ) ) : ?>
			<tr valign="top" id="vies">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion_vies]">
						<?php _e( 'Allow VIES VAT number?', 'wc-apg-nifcifnie-field' ); ?>
						<span class="woocommerce-help-tip" data-tip="<?php _e( 'Check if you want to allow and validate VIES VAT number.', 'wc-apg-nifcifnie-field' ); ?>"></span>
					</label>
				</th>
				<td class="forminp"><input id="apg_nif_settings[validacion_vies]" name="apg_nif_settings[validacion_vies]" type="checkbox" value="1" <?php echo ( isset( $apg_nif_settings[ 'validacion_vies'] ) && $apg_nif_settings[ 'validacion_vies'] == "1" ? 'checked="checked"' : '' ); ?> tabindex="
					<?php echo $tab++; ?>" /></td>
			</tr>
			<?php endif; ?>
		</table>
		<p class="submit">
			<input class="button-primary" type="submit" value="<?php _e( 'Save Changes', 'wc-apg-nifcifnie-field' ); ?>" name="submit" id="submit" tabindex="<?php echo $tab++; ?>"/>
		</p>
	</form>
</div>