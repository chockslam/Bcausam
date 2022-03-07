<div class="wrap">
    <h1><?php printf( __( '%s - Settings', 'ionos-assistant' ), Ionos_Assistant_Config::get( 'name', 'branding', null, 'Assistant' ) ); ?></h1>

    <form method="post" action="options.php" novalidate="novalidate">

		<?php
		settings_fields( 'ionos_assistant_settings_plugin_options' );
		do_settings_sections('ionos_assistant_settings_plugin');
		submit_button();
		?>
    </form>

</div>