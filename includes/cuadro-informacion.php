<div class="informacion">
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'If you enjoyed and find helpful this plugin, please make a donation:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="<?php echo esc_url( $apg_nif[ 'donacion' ] ); ?>" target="_blank" title="<?php esc_attr_e( 'Make a donation by ', 'wc-apg-nifcifnie-field' ); ?>APG"><span class="genericon genericon-cart"></span></a> </p>
		</div>
		<div class="columna">
			<p>Art Project Group:</p>
			<p><a href="http://www.artprojectgroup.es" title="Art Project Group" target="_blank"><strong class="artprojectgroup">APG</strong></a> </p>
		</div>
	</div>
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'Follow us:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="https://www.facebook.com/artprojectgroup" title="<?php esc_attr_e( 'Follow us on ', 'wc-apg-nifcifnie-field' ); ?>Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="<?php esc_attr_e( 'Follow us on ', 'wc-apg-nifcifnie-field' ); ?>Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="<?php esc_attr_e( 'Follow us on ', 'wc-apg-nifcifnie-field' ); ?>LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a> </p>
		</div>
		<div class="columna">
			<p>
				<?php esc_html_e( 'More plugins:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="http://profiles.wordpress.org/artprojectgroup/" title="<?php esc_attr_e( 'More plugins on ', 'wc-apg-nifcifnie-field' ); ?>WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a> </p>
		</div>
	</div>
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'Contact with us:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="mailto:info@artprojectgroup.es" title="<?php esc_attr_e( 'Contact with us by ', 'wc-apg-nifcifnie-field' ); ?>e-mail"><span class="genericon genericon-mail"></span></a></a> </p>
		</div>
		<div class="columna">
			<p>
				<?php esc_html_e( 'Documentation and Support:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="<?php echo esc_url( $apg_nif[ 'plugin_url' ] ); ?>" title="<?php echo esc_attr( $apg_nif[ 'plugin' ] ); ?>"><span class="genericon genericon-book"></span></a> <a href="<?php echo esc_url( $apg_nif[ 'soporte' ] ); ?>" title="<?php esc_attr_e( 'Support', 'wc-apg-nifcifnie-field' ); ?>"><span class="genericon genericon-cog"></span></a> </p>
		</div>
	</div>
	<div class="fila final">
		<div class="columna">
			<p>
				<?php
                // translators: %s is the plugin name (e.g., WC – APG Campo NIF/CIF/NIE)
				echo esc_html( sprintf( __( 'Please, rate %s:', 'wc-apg-nifcifnie-field' ), $apg_nif[ 'plugin' ] ) );
				?>
			</p>
			<?php echo wp_kses_post( apg_nif_plugin( $apg_nif[ 'plugin_uri' ] ) ); ?> </div>
		<div class="columna final"></div>
	</div>
</div>
