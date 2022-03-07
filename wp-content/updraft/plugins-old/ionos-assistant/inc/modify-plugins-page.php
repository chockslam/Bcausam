<?php
// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

class Ionos_Assistant_Modify_Plugins_Page {

	public function __construct() {

		// modify plugins help content
		add_action( 'admin_head', array( $this, 'modify_plugins_help_content' ) );

		// hide must-use plugins list
		add_filter( 'show_advanced_plugins', array( $this, 'hide_plugin_list' ), 10, 2 );
	}

	public function hide_plugin_list( $show, $type ) {
		if ( $type == 'mustuse' ) {
			$show = false;
		}

		return $show;
	}

	public function modify_plugins_help_content() {
		global $pagenow;
		
		if ( is_admin() && $pagenow == 'plugins.php' ) {
			?>
			<style type="text/css">
				#tab-panel-overview p:last-child {
					display: none;
				}
			</style>
			<?php
		}
	}
}

new Ionos_Assistant_Modify_Plugins_Page();
