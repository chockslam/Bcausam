<div id="card-congrats-lightbox" style="display:none">
	<div class="assistant-card card-congrats">
 		<div class="card-bg"></div>
		<div class="card-bg card-weave-medium"></div>
		<div class="card-bg card-weave-light"></div>
		
		<div id="card-congrats" class="card-step active">
			<?php Ionos_Assistant_View::load_template( 'card/header-check' ); ?>
		
			<div class="card-content">
				<div class="card-content-inner">
					<h2><?php esc_html_e( 'setup_assistant_customizer_title', 'ionos-assistant' ) ?></h2>
					<p><?php _e( 'setup_assistant_customizer_desc1', 'ionos-assistant' ); ?></p>
					<p><?php _e( 'setup_assistant_customizer_desc2', 'ionos-assistant' ); ?></p>
				</div>
			</div>
			
			<?php
				Ionos_Assistant_View::load_template( 'card/footer', array(
					'card_actions' => array(
						'left'  => array(),
						'right' => array(
							'skip-congrats' => array(
								'label'   => esc_html__( 'setup_assistant_customizer_close_button', 'ionos-assistant' ),
								'class'   => 'button button-primary',
								'onclick' => 'tb_remove()'
							)
						)
					)
				) );
			?>
		</div>
	</div>
</div>