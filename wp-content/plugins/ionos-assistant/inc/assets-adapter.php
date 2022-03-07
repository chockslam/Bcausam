<?php
/** Do not allow direct access! */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

/**
 * Class Ionos_Assistant_Assets_Adapter
 * Enhances the Assistant Interface according to which plugins have been installed
 */
class Ionos_Assistant_Assets_Adapter {

	/**
	 * Ionos_Assistant_Assets_Adapter constructor.
	 */
	public function __construct() {

		// Plugin Post-install action hooks
		add_action( 'ionos_assistant_plugin_post_install_woocommerce', array( $this, 'install_woocommerce' ) );

		// Plugin Post-activation action hooks
		add_action( 'ionos_assistant_plugin_post_activate_a3-lazy-load', array( $this, 'setup_a3_lazy_load' ) );
		add_action( 'ionos_assistant_plugin_post_activate_nextgen-gallery', array( $this, 'setup_nextgen_gallery' ) );
		add_action( 'ionos_assistant_plugin_post_activate_the-events-calendar', array( $this, 'setup_the_events_calendar' ) );
		add_action( 'ionos_assistant_plugin_post_activate_wpforms-lite', array( $this, 'setup_wpforms_lite' ) );

		// Theme Post-activation action hooks
		add_action( 'ionos_assistant_theme_post_activate_customizr', array( $this, 'setup_customizr' ) );
	}

	/**
	 * WooCommerce Plugin Installation
	 */
	public function install_woocommerce() {

		if ( ! function_exists( 'wc_get_screen_ids' ) ) {
			function wc_get_screen_ids() {
				return array();
			}
		}
	}

	/**
	 * a3 Lazy Load Plugin Setup
	 * - removes the automatic redirection
	 */
	public function setup_a3_lazy_load() {
		delete_option( 'a3_lazy_load_just_installed' );
	}

	/**
	 * NextGEN Gallery Plugin Setup
	 * - removes the automatic redirection
	 */
	public function setup_nextgen_gallery() {
		delete_option( 'fs_nextgen-gallery_activated' );
	}

	/**
	 * The Events Calendar Plugin Setup
	 * - removes the automatic redirection
	 */
	public function setup_the_events_calendar() {
		delete_transient( '_tribe_events_activation_redirect' );
	}

	/**
	 * Contact Form by WPForms
	 * - removes the automatic redirection
	 */
	public function setup_wpforms_lite() {
		delete_transient( 'wpforms_activation_redirect' );
		delete_option( 'wpforms_activation_redirect' );
	}

	/**
	 * Customizr Theme setup
	 * - activates the Demo Slider (like in the overview)
	 */
	public function setup_customizr() {
		$czr_theme_options = ( get_option( 'tc_theme_options', array() ) );

		if ( is_array( $czr_theme_options ) ) {
			$czr_theme_options[ 'tc_front_slider' ] = 'demo';
			update_option( 'tc_theme_options', $czr_theme_options );
		}
	}
}
