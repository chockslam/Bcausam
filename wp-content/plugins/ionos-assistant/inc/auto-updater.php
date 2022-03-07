<?php
// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

class Ionos_Assistant_Auto_Updater {

	/**
	 * Ionos_Assistant_Auto_Updater constructor.
	 */
	public function __construct() {
		if ( Ionos_Assistant_Config::get( 'auto_updates', 'features' ) ) {
			add_action( 'init', array( $this, 'enable_all_assets_updates' ), 1 );

			add_filter( 'plugin_auto_update_setting_html', array( $this, 'modify_auto_update_plugin_setting' ), 10, 0 );

			add_filter( 'theme_auto_update_setting_template', array( $this, 'modify_auto_update_theme_setting' ), 10, 0 );
			add_filter( 'theme_auto_update_setting_html', array( $this, 'modify_auto_update_theme_setting' ), 10, 0 );
		}
	}

	/**
	 * Both inform the user of the enforced plugins auto-update process and prevent him to try to override it
	 * (which won't work anyway as the global enabling setup overrides the user-selected options)
	 */
	public function modify_auto_update_theme_setting() {
		return '<p><span class="ionos-auto-update">
					' . apply_filters( 'ionos_assistant_auto_update_hint_theme', __( 'Permanently active', 'ionos-assistant' ) ) . '
				</span></p>';
	}

	/**
	 * Both inform the user of the enforced themes auto-update process and prevent him to try to override it
	 * (which won't work anyway as the global enabling setup overrides the user-selected options)
	 */
	public function modify_auto_update_plugin_setting() {
		return '<p><span class="ionos-auto-update">
					' . apply_filters( 'ionos_assistant_auto_update_hint_plugin', __( 'Permanently active', 'ionos-assistant' ) ) . '
				</span></p>';
	}

	/**
	 * Enable WordPress to automatically update plugins and themes to newest version
	 * https://make.wordpress.org/core/2020/07/15/controlling-plugin-and-theme-auto-updates-ui-in-wordpress-5-5/)
	 */
	public function enable_all_assets_updates() {
		// Enable all plugin auto-updates
		add_filter( 'auto_update_plugin', '__return_true' );

		// Enable all theme auto-updates
		add_filter( 'auto_update_theme', '__return_true' );
	}
}

new Ionos_Assistant_Auto_Updater();