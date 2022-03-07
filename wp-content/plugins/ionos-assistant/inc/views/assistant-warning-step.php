<?php Ionos_Assistant_View::load_template( 'card/header-default' ); ?>

<div class="card-content warning">
	<div class="card-content-inner">
		<h2><?php _e( 'We are sorry, but you cannot go further.', 'ionos-assistant' ); ?></h2>
		<p>
			<?php
				echo sprintf(
					__(
						'It seems you are PHP 8. Unfortunately, the Assistant does not yet support PHP 8. ' .
						'To use it we recommend you to <a href="%s">switch to a lower version</a> (PHP 7.4) an wait for the update that we will provide soon. ' .
						'Thank you for your understanding!',
						'ionos-assistant'
					),
					Ionos_Assistant_Config::get( 'help_php_{market}', 'links', 'help_php_US' )
				);
			?>
		</p>
	</div>
</div>

<?php
	Ionos_Assistant_View::load_template( 'card/footer', array(
		'card_actions' => array(
			'left'  => array(),
			'right' => array(
				'back-to-wp' => array(
					'label' => __( 'Take me back', 'ionos-assistant' ),
					'class' => 'button button-primary',
					'href'  => admin_url()
				)
			)
		)
	) );
?>