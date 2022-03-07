<?php if ( ! empty( $site_type ) && ! empty( $theme ) && is_array( $theme ) ): ?>

	<div class="theme-preview">
		<div class="theme-screenshot">
			<img src="<?php echo esc_url( $theme['screenshot_url'] ); ?>" alt="<?php esc_html_e( $theme['name'] ); ?>">
		</div>

		<div class="theme-info">
			<div class="card-content">
				<form action="" method="post" class="assistant-install-form-preview">
					<?php wp_nonce_field( 'activate' ) ?>

					<input type="hidden" id="install-site-type" name="site_type" value="<?php echo $site_type; ?>" />
					<input type="hidden" id="install-theme" name="theme" value="<?php echo $theme['id']; ?>" />

					<h2><?php echo esc_html( $theme['name'] ); ?></h2>

					<?php if ( ! empty( $theme['active'] ) ): ?>
						<p class="theme-active"><?php echo __( 'Active theme' ); ?></p>
					<?php endif; ?>

					<p><strong>
						<?php echo sprintf(
							__( 'From <a href="%s" target="_blank" rel="external nofollow">WordPress.org</a>:', 'ionos-assistant' ),
							'https://wordpress.org/themes/' . $theme['slug'] . '/'
						) ?>
					</strong></p>

					<p>
						<?php echo esc_html(
							array_key_exists( 'short_description', $theme ) ? $theme['short_description'] : $theme['description']
						) ?>
					</p>
				</form>
			</div>

			<?php
				Ionos_Assistant_View::load_template( 'card/footer', array(
					'card_actions' => array(
						'left'  => array(),
						'right' => array(
							'install' => array(
								'label' => esc_html__( 'Choose this theme', 'ionos-assistant' ),
								'class' => 'button button-primary theme-btn'
							),
							'goto-design' => array(
								'label' => esc_html__( 'Back' ),
								'class' => 'button',
								'data'  => array(
									'site-type' => $site_type
								)
							)
						)
					)
				) );
			?>
		</div>
	</div>
<?php endif; ?>

