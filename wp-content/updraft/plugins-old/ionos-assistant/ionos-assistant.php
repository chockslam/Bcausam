<?php
/*
Plugin Name:  IONOS Assistant
Plugin URI:   https://www.ionos.com
Description:  WordPress Setup Assistant
Version:      6.3.0
License:      GPLv2 or later
Author:       IONOS
Author URI:   https://www.ionos.com
Text Domain:  ionos-assistant
Domain Path:  /languages
*/

/*
Copyright 2020 IONOS by 1&1
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Online: http://www.gnu.org/licenses/gpl.txt
*/

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

class Ionos_Assistant {
	const VERSION = '6.3.0';

	public function __construct() {
		$this->load_global_files();

		Ionos_Assistant_Handler_Login::init(
			Ionos_Assistant_Config::feature( 'login_redesign' )
		);

		/** admin actions */
		if ( is_admin() ) {
			$this->load_admin_files();

			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Start and configure the Assistant in the admin area
			Ionos_Assistant_Handler_Dispatch::admin_init();

			// add checks on plugin activation
			register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );

			// register deactivation hook
			register_deactivation_hook( __FILE__, array( $this, 'deactivation_hook' ) );

		/** front-end actions */
		} else {
			$this->load_frontend_files();

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// add some Assistant elements on the front-end
			Ionos_Assistant_Handler_Dispatch::wp_init();
		}
	}

	public function load_global_files() {
		include_once 'inc/config.php';
		include_once 'inc/branding.php';
		include_once 'inc/view.php';
		include_once 'inc/handlers/login.php';
		include_once 'inc/handlers/dispatch.php';
		include_once 'inc/auto-updater.php';
	}

	public function load_admin_files() {
		include_once 'inc/modify-settings-page.php';
		include_once 'inc/modify-plugins-page.php';
		include_once 'inc/sitetype-filter.php';
		include_once 'inc/cache-manager.php';
		include_once 'inc/dashboard.php';
		include_once 'inc/create-settings-page.php';
	}

	public function load_frontend_files() {
		include_once 'inc/cron-manager.php';
	}

	public function deactivation_hook() {
		wp_clear_scheduled_hook( 'ionos_assistant_cron_update_deactivated_plugins' );
		wp_clear_scheduled_hook( 'ionos_assistant_cron_update_plugin_meta' );
		delete_option( 'ionos_assistant_completed' );
		delete_option( 'ionos_assistant_sitetype' );
	}

	public function load_textdomain() {
		if ( self::is_must_use_plugin_folder() ) {
			$language_loaded = load_muplugin_textdomain( 'ionos-assistant', basename( dirname( __FILE__ ) ) . '/languages' );
		} else {
			$language_loaded = load_plugin_textdomain( 'ionos-assistant', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		// Check whether language could be loaded properly. If not, use en_US as a fallback.
		if ( ! empty( $language_loaded ) || false === $language_loaded ) {
			if ( self::is_must_use_plugin_folder() ) {
				$plugin_dir = WPMU_PLUGIN_DIR;
			} else {
				$plugin_dir = WP_PLUGIN_DIR;
			}

			$domain = 'ionos-assistant';
			$path = trailingslashit( $plugin_dir . '/' . ltrim( dirname( plugin_basename( __FILE__ ) ) . '/languages/', '/' ) );
			$mofile = $domain . '-en_US.mo';

			load_textdomain( $domain, $path . $mofile );
		}
	}

	public function enqueue_scripts() {

		// Add the cookie script to control feature switches through JS
		wp_enqueue_script( 'ionos-assistant-wp-cookies', Ionos_Assistant::get_js_url( 'cookies.js' ) );
	}

	public static function get_site_type_label( $site_type ) {
		switch ( $site_type ) {
			case 'gallery':
				$site_type = _x( 'Gallery', 'website-types', 'ionos-assistant' );
				break;
			case 'blog':
				$site_type = _x( 'Blog', 'website-types', 'ionos-assistant' );
				break;
			case 'personal':
				$site_type = _x( 'Personal Website', 'website-types', 'ionos-assistant' );
				break;
			case 'business':
				$site_type = _x( 'Business Website', 'website-types', 'ionos-assistant' );
				break;
		}

		return $site_type;
	}

	public function activate_plugin() {
		// Check WordPress version
		if ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) ) {
			die( __( 'The Assistant could not be activated. To activate the plugin, you need WordPress 3.5 or higher.', 'ionos-assistant' ) );
		}
	}

	/**
	 * Return the contract's market value provided by the installation
	 *
	 * @return string
	 */
	public static function get_market() {

		$default_market = 'US';
		$supported_markets = array( 'DE', 'CA', 'GB', 'UK', 'US', 'ES', 'MX', 'FR', 'IT' );

		$market = ( string ) strtoupper( get_option( 'ionos_assistant_market', $default_market ) );

		if ( ! $market || ! in_array( $market, $supported_markets ) ) {
			$market = $default_market;
		}

		return $market;
	}

	public static function get_css_url( $file = '' ) {
		return plugins_url( 'css/' . $file, __FILE__ );
	}

	public static function get_js_url( $file = '' ) {
		return plugins_url( 'js/' . $file, __FILE__ );
	}

	public static function get_images_url( $image = '' ) {
		return plugins_url( 'images/' . $image, __FILE__ );
	}


	public static function get_plugin_file_path() {
		return __FILE__;
	}

	public static function get_plugin_dir_path() {
		return apply_filters( 'ionos-assistant-plugin-dir-path', plugin_dir_path( __FILE__ ) );
	}

	public static function get_inc_dir_path() {
		return plugin_dir_path( __FILE__ ) . 'inc/';
	}

	public static function get_views_dir_path() {
		return Ionos_Assistant_View::get_default_views_path();
	}

	public static function get_abspath() {
		return apply_filters( 'ionos-assistant-abspath', ABSPATH );
	}

	/**
	 * Checks if the fragment exists
	 *
	 * @param null $string
	 * @param null $fragment
	 *
	 * @return bool
	 */
	public static function is_url_query_fragment_in_url_string( $string = null, $fragment = null ) {
		$return_value = false;
		if ( ! is_null( $string ) && ! is_null( $fragment ) ) {
			parse_str( parse_url( $string, PHP_URL_QUERY ), $query_array );
			$return_value = array_key_exists( $fragment, $query_array );
		}

		return $return_value;
	}

	/**
	 * Check if the plugin is a "must used" use case or not, based on its location:
	 *
	 * * wp-content/plugins -> optional
	 * * wp-content/mu-plugins -> required
	 *
	 * @return boolean
	 */
	public static function is_must_use_plugin_folder() {
		$plugin_path = Ionos_Assistant::get_plugin_dir_path();

		if ( strpos( $plugin_path, 'mu-plugins' ) === false ) {
			return false;
		} else {
			return true;
		}
	}
}

new Ionos_Assistant();
