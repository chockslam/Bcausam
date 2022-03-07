	<?php
	/*
	 * WARNING: DO NOT REMOVE THIS TAG!
	 * We use the admin_head() hook to locate the template where we want, and that imply manually closing the <head> tag
	 */
	?>
		<meta name="viewport" content="width=device-width initial-scale=1.0" />
	</head>

	<?php
		$action = isset( $_GET[ 'setup_action' ] ) ? sanitize_text_field( $_GET[ 'setup_action' ] ) : 'choose_appearance';
	    $current_site_type = isset( $_GET[ 'setup_type' ] ) ? sanitize_text_field( $_GET[ 'setup_type' ] ) : '';
	    $current_theme = isset( $_GET[ 'setup_theme' ] ) ? sanitize_text_field( $_GET[ 'setup_theme' ] ) : '';
	?>

	<body class="assistant-page">
		<?php Ionos_Assistant_View::load_template( 'parts/header' ); ?>

		<section class="assistant-card-container wp-core-ui">
			<div class="assistant-card animate">
				<div class="card-bg"></div>
				<div class="card-bg card-weave-medium"></div>
				<div class="card-bg card-weave-light"></div>

				<?php if (Ionos_Assistant_Config::get( 'php8_warning', 'features' ) && PHP_MAJOR_VERSION === 8): ?>
					<div class="card-step active" id="card-warning">
						<?php Ionos_Assistant_View::load_template( 'assistant-warning-step' ); ?>
					</div>
				<?php else: ?>
					<div class="card-step<?php echo ( $action === 'greeting' ) ? ' active' : '' ?>" id="card-greeting">
						<?php Ionos_Assistant_View::load_template( 'assistant-greeting-step' ); ?>
					</div>

					<div class="card-step<?php echo ( $action === 'choose_appearance' ) ? ' active' : '' ?>" id="card-design">
						<?php Ionos_Assistant_View::load_template( 'assistant-design-step', array(
							'site_types'        => $site_types,
	                        'current_site_type' => $current_site_type
						) ); ?>
					</div>

					<div class="card-step<?php echo ( $action === 'preview' ) ? ' active' : '' ?>" id="card-preview">
						<?php Ionos_Assistant_View::load_template( 'assistant-design-preview' ); ?>
					</div>

					<div class="card-step<?php echo ( $action === 'install' ) ? ' active' : '' ?>" id="card-install">
						<?php Ionos_Assistant_View::load_template( 'assistant-install-step' ); ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<?php
			do_action( 'admin_footer', '' );
			do_action( 'admin_print_footer_scripts' );
		?>
	</body>
</html>