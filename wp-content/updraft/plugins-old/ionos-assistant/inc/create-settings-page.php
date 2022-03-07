<?php
// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Ionos_Create_Settings_Page {


	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_settings_page() {
		$brand_name = Ionos_Assistant_Config::get( 'name', 'branding', null, 'Assistant' );

		add_options_page(
			$brand_name . ' Settings Page',
			$brand_name,
			'manage_options',
			'ionos-settings-page',
			array( $this, 'page_content' )
		);
	}

	public function page_content() {
		Ionos_Assistant_View::load_template(
			'settings-page'
		);
	}

	public function register_settings() {
		$option_group_id = 'ionos_assistant_settings_plugin_options';

		register_setting(
			$option_group_id,
			'ionos_assistant_login_redesign',
			array(
				'default' => Ionos_Assistant_Config::get( 'login_redesign', 'features' )
			)
		);
		add_settings_section(
			'ionos_assistant_design_settings',
			'',
			'',
			'ionos_assistant_settings_plugin'
		);
		add_settings_field(
			'ionos_assistant_login_redesign', __( 'Login design', 'ionos-assistant' ),
			array(
				$this,
				'login_redesign'
			),
			'ionos_assistant_settings_plugin',
			'ionos_assistant_design_settings'
		);
		// Allow other plugins to register some more options with the branding
		do_action(
			'ionos_assistant_register_settings',
			$option_group_id,
			Ionos_Assistant_Config::get( 'branding' )
		);
	}

	public function login_redesign() {
		$option = get_option( 'ionos_assistant_login_redesign' );
		echo "<label id='ionos_assistant_login_redesign_option' for='ionos_assistant_login_redesign'>";
		echo "<input id='ionos_assistant_login_redesign' name='ionos_assistant_login_redesign' type='checkbox' value='1' " . checked( '1', $option, false ) . " />";
		echo "<span>" . sprintf( __( 'Use %s design for login', 'ionos-assistant' ), Ionos_Assistant_Config::get( 'name', 'branding', null, 'Assistant' ) ) . "</span>";
		echo "<p class='description'>" . sprintf( __( 'When activated this setting will theme the login page at %s with %s design', 'ionos-assistant' ), get_admin_url(), Ionos_Assistant_Config::get( 'name', 'branding', null, 'Assistant' ) ) . "</p></label>";
	}
}

new Ionos_Create_Settings_Page();