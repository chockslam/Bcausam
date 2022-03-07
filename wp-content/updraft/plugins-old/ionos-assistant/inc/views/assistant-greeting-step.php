<?php Ionos_Assistant_View::load_template( 'card/header-check' ); ?>

<div class="card-content">
	<div class="card-content-inner">
		<h2><?php
			if ( Ionos_Assistant_Branding::get_brand_name() ) {
				echo sprintf(
					esc_html__( 'setup_assistant_greeting_title_by', 'ionos-assistant' ),
					Ionos_Assistant_Branding::get_brand_name()
				);
			} else {
				esc_html_e( 'Welcome to WordPress!', 'ionos-assistant' );
			}
		?></h2>
		<p><?php _e( 'setup_assistant_greeting_description', 'ionos-assistant' ); ?></p>
	</div>
</div>

<?php
	Ionos_Assistant_View::load_template( 'card/footer', array(
		'card_actions' => array(
			'left'  => array(),
			'right' => array(
				'goto-design' => array(
					'label' => esc_html__( 'setup_assistant_greeting_ok', 'ionos-assistant' ),
					'class' => 'button button-primary'
				),
				'skip-greeting' => array(
					'label' => esc_html__( 'setup_assistant_greeting_cancel', 'ionos-assistant' ),
					'class' => 'button',
					'href'  => admin_url( 'index.php?ionos-assistant-cancel=1' )
				)
			)
		)
	) );
?>