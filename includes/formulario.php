<?php global $apg_nif; ?>

<div class="wrap woocommerce">
	<h2>
		<?php _e( 'APG NIF/CIF/NIE field Options.', 'apg_nif' ); ?>
	</h2>
	<?php 
	settings_errors(); 
	$tab			= 1;
	$configuracion	= get_option( 'apg_nif_settings' );
	?>
	<h3><a href="<?php echo $apg_nif['plugin_url']; ?>" title="Art Project Group"><?php echo $apg_nif['plugin']; ?></a></h3>
	<p>
		<?php _e( 'Add to WooCommerce a NIF/CIF/NIE field, validate the field before submit and let to the admin configure the billing and shipping forms.', 'apg_nif' ); ?>
	</p>
	<?php include( 'cuadro-informacion.php' ); ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'apg_nif_settings_group' ); ?>
		<div class="cabecera"> <a href="<?php echo $apg_nif['plugin_url']; ?>" title="<?php echo $apg_nif['plugin']; ?>" target="_blank"><img src="<?php echo plugins_url( '../assets/images/cabecera.jpg', __FILE__ ); ?>" class="imagen" alt="<?php echo $apg_nif['plugin']; ?>" /></a> </div>
		<table class="form-table apg-table">
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[requerido]">
						<?php _e( 'Require billing field?', 'apg_nif' ); ?>
					</label>
					<span class="woocommerce-help-tip" data-tip="<?php _e( "Check if you need to require the field", 'apg_nif' ); ?>"></span> </th>
				<td class="forminp"><input id="apg_nif_settings[requerido]" name="apg_nif_settings[requerido]" type="checkbox" value="1" <?php echo ( isset( $configuracion['requerido'] ) && $configuracion['requerido']=="1" ? 'checked="checked"' : '' ); ?> tabindex="
					<?php echo $tab++; ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[requerido_envio]">
						<?php _e( 'Require shipping field?', 'apg_nif' ); ?>
					</label>
					<span class="woocommerce-help-tip" data-tip="<?php _e( "Check if you need to require the field", 'apg_nif' ); ?>"></span> </th>
				<td class="forminp"><input id="apg_nif_settings[requerido_envio]" name="apg_nif_settings[requerido_envio]" type="checkbox" value="1" <?php echo ( isset( $configuracion['requerido_envio'] ) && $configuracion['requerido_envio']=="1" ? 'checked="checked"' : '' ); ?> tabindex="
					<?php echo $tab++; ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion]">
						<?php _e( 'Validate field?', 'apg_nif' ); ?>
					</label>
					<span class="woocommerce-help-tip" data-tip="<?php _e( "Check if you want to validate the field before submit", 'apg_nif' ); ?>"></span> </th>
				<td class="forminp"><input id="apg_nif_settings[validacion]" name="apg_nif_settings[validacion]" type="checkbox" value="1" <?php echo ( isset( $configuracion['validacion'] ) && $configuracion['validacion']=="1" ? 'checked="checked"' : '' ); ?> tabindex="
					<?php echo $tab++; ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="apg_nif_settings[validacion_vies]">
						<?php _e( 'Allow VIES VAT number?', 'apg_nif' ); ?>
					</label>
					<span class="woocommerce-help-tip" data-tip="<?php _e( "Check if you want to allow and validate VIES VAT number.", 'apg_nif' ); ?>"></span> </th>
				<td class="forminp"><input id="apg_nif_settings[validacion_vies]" name="apg_nif_settings[validacion_vies]" type="checkbox" value="1" <?php echo ( isset( $configuracion['validacion_vies'] ) && $configuracion['validacion_vies']=="1" ? 'checked="checked"' : '' ); ?> tabindex="
					<?php echo $tab++; ?>" /></td>
			</tr>
		</table>
		<p class="submit">
			<input class="button-primary" type="submit" value="<?php _e( 'Save Changes', 'apg_nif' ); ?>" name="submit" id="submit" tabindex="<?php echo $tab++; ?>"/>
		</p>
	</form>
</div>