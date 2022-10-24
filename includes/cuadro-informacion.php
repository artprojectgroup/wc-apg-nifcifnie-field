<div class="informacion">
	<div class="fila">
		<div class="columna">
			<p>
				<?php _e( 'If you enjoyed and find helpful this plugin, please make a donation:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="<?php echo $apg_nif['donacion']; ?>" target="_blank" title="<?php _e( 'Make a donation by ', 'wc-apg-nifcifnie-field' ); ?>APG"><span class="genericon genericon-cart"></span></a>
			</p>
		</div>
		<div class="columna">
			<p>Art Project Group:</p>
			<p><a href="http://www.artprojectgroup.es" title="Art Project Group" target="_blank"><strong class="artprojectgroup">APG</strong></a>
			</p>
		</div>
	</div>
	<div class="fila">
		<div class="columna">
			<p>
				<?php _e( 'Follow us:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="https://www.facebook.com/artprojectgroup" title="<?php _e( 'Follow us on ', 'wc-apg-nifcifnie-field' ); ?>Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="<?php _e( 'Follow us on ', 'wc-apg-nifcifnie-field' ); ?>Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="<?php _e( 'Follow us on ', 'wc-apg-nifcifnie-field' ); ?>LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>
			</p>
		</div>
		<div class="columna">
			<p>
				<?php _e( 'More plugins:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="http://profiles.wordpress.org/artprojectgroup/" title="<?php _e( 'More plugins on ', 'wc-apg-nifcifnie-field' ); ?>WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>
			</p>
		</div>
	</div>
	<div class="fila">
		<div class="columna">
			<p>
				<?php _e( 'Contact with us:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="mailto:info@artprojectgroup.es" title="<?php _e( 'Contact with us by ', 'wc-apg-nifcifnie-field' ); ?>e-mail"><span class="genericon genericon-mail"></span></a> <a href="skype:artprojectgroup" title="<?php _e( 'Contact with us by ', 'wc-apg-nifcifnie-field' ); ?>Skype"><span class="genericon genericon-skype"></span></a>
			</p>
		</div>
		<div class="columna">
			<p>
				<?php _e( 'Documentation and Support:', 'wc-apg-nifcifnie-field' ); ?>
			</p>
			<p><a href="<?php echo $apg_nif['plugin_url']; ?>" title="<?php echo $apg_nif['plugin']; ?>"><span class="genericon genericon-book"></span></a> <a href="<?php echo $apg_nif['soporte']; ?>" title="<?php _e( 'Support', 'wc-apg-nifcifnie-field' ); ?>"><span class="genericon genericon-cog"></span></a>
			</p>
		</div>
	</div>
	<div class="fila final">
		<div class="columna">
			<p>
				<?php echo sprintf( __( 'Please, rate %s:', 'wc-apg-nifcifnie-field' ), $apg_nif['plugin'] ); ?>
			</p>
			<?php echo apg_nif_plugin( $apg_nif['plugin_uri'] ); ?> </div>
		<div class="columna final"></div>
	</div>
</div>