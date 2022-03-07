<?php Ionos_Assistant_View::load_template( 'card/header-default' ); ?>

<div class="card-content" style="padding-bottom: 0">
	<div class="card-content-inner">
		<h2><?php esc_html_e( 'setup_assistant_progress_title', 'ionos-assistant' ); ?></h2>
		<p><?php _e( 'setup_assistant_progress_desc', 'ionos-assistant' ); ?></p>
	</div>

	<div class="linear-progress-container">
		<div class="linear-progress">
			<div class="indeterminate"></div>
		</div>
	</div>
</div>