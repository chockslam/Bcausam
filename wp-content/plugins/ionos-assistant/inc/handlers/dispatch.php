<?php
/** Do not allow direct access! */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

/**
 * Class Ionos_Assistant_Handler_Dispatch
 * Computes and shows to the corresponding view of the Assistant in the WP Admin
 */
class Ionos_Assistant_Handler_Dispatch {

	/** WP Admin page ID for the Assistant */
	const ASSISTANT_PAGE_ID = 'ionos-assistant';

	/**
	 * Configure somes details in the WP front-end pages
	 */
	public static function wp_init() {

		// Add an Assistant link in the front-end admin bar
		add_action( 'admin_bar_menu', array( 'Ionos_Assistant_Handler_Dispatch', 'add_admin_top_bar_wizard_menu' ), 50 );
	}

	/**
	 * Start and configure the Assistant in the WP admin area
	 */
	public static function admin_init() {

		// Load Assistant single page
		add_action( 'admin_head', array( 'Ionos_Assistant_Handler_Dispatch', 'load_assistant_page' ) );

		// Configure Customizer as last step
		add_action( 'init', array( 'Ionos_Assistant_Handler_Dispatch', 'configure_customizer' ) );
		add_action( 'customize_controls_print_footer_scripts', array(
			'Ionos_Assistant_Handler_Dispatch',
			'add_customizer_thickbox'
		) );

		// Configure AJAX hook for the themes loading
		add_action( 'wp_ajax_ajaxload', array( 'Ionos_Assistant_Handler_Dispatch', 'load_recommended_themes' ) );

		// Configure AJAX hook for the theme preview
		add_action( 'wp_ajax_ajaxpreview', array( 'Ionos_Assistant_Handler_Dispatch', 'load_theme_preview' ) );

		// Configure AJAX hook for the plugins & themes installation
		add_action( 'wp_ajax_ajaxinstall', array( 'Ionos_Assistant_Handler_Dispatch', 'install_plugins_and_themes' ) );

		// Add Assistant scripts
		add_action( 'admin_enqueue_scripts', array( 'Ionos_Assistant_Handler_Dispatch', 'enqueue_assistant_scripts' ) );

		// Add styles and fonts for the new Assistant design
		add_action( 'admin_enqueue_scripts', array( 'Ionos_Assistant_Handler_Dispatch', 'enqueue_assistant_styles' ) );

		// Create and configure the wizard page in the admin area
		add_action( 'admin_menu', array( 'Ionos_Assistant_Handler_Dispatch', 'add_admin_menu_wizard_page' ), 5 );
	}

	/**
	 * Check if we are in the Assistant context
	 *
	 * @return boolean
	 */
	public static function is_assistant_admin_page() {
		return ( isset( $_GET['page'] ) && ( $_GET['page'] === self::ASSISTANT_PAGE_ID ) );
	}

	/**
	 * Check if we are in the Customizer Step after the Assistant
	 * (in the Assistant context, identified by the "message" URL param)
	 *
	 * @param string $with_msg
	 *
	 * @return boolean
	 */
	public static function is_customizer_page( $with_msg = null ) {
		global $wp_customize;

		$is_customizer_page = $wp_customize instanceof WP_Customize_Manager
		                      && $wp_customize->is_preview();

		if ( $with_msg ) {
			return $is_customizer_page
			       && isset( $_GET['message'] )
			       && $_GET['message'] == esc_attr( $with_msg );
		} else {
			return $is_customizer_page;
		}
	}

	/**
	 * Create and configure the Assistant page in the admin area
	 * WP Hook https://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu
	 */
	public static function add_admin_menu_wizard_page() {
		global $menu;

		$pos = 50;
		$posp1 = $pos + 1;

		while ( isset( $menu[ $pos ] ) || isset( $menu[ $posp1 ] ) ) {
			$pos ++;
			$posp1 = $pos + 1;

			/** check that there is no menu at our level neither at ourlevel+1 because that will make us disappear in some case */
			if ( ! isset( $menu[ $pos ] ) && isset( $menu[ $posp1 ] ) ) {
				$pos = $pos + 2;
			}
		}

		add_menu_page(
			__( 'Assistant', 'ionos-assistant' ),
			__( 'Assistant', 'ionos-assistant' ),
			'manage_options',
			self::ASSISTANT_PAGE_ID,
			function () {
			},
			'dashicons-universal-access-alt',
			$pos
		);

	}

	/**
	 * Add an extra menu item in the top admin bar
	 * https://codex.wordpress.org/Class_Reference/WP_Admin_Bar/add_menu
	 */
	public static function add_admin_top_bar_wizard_menu() {
		global $wp_admin_bar;

		$title_element = sprintf(
			'<span class="ab-icon dashicons dashicons-universal-access-alt"></span>%s',
			__( 'Assistant', 'ionos-assistant' )
		);

		/** @var WP_Admin_Bar $wp_admin_bar */
		$wp_admin_bar->add_menu(
			array(
				'parent' => null,
				'id'     => self::ASSISTANT_PAGE_ID,
				'title'  => $title_element,
				'href'   => admin_url(
					add_query_arg( 'page', self::ASSISTANT_PAGE_ID, 'admin.php' )
				)
			)
		);
	}

	/**
	 * Handle status change of the wizard anywhere in the admin area (via GET parameters)
	 * WP Hook https://codex.wordpress.org/Plugin_API/Action_Reference/admin_init
	 */
	public static function handle_assistant_params() {

		/** reset the wizard (restart from the beginning) */
		if ( isset( $_GET['ionos-assistant-reset'] ) ) {
			delete_option( 'ionos_assistant_completed' );
			delete_option( 'ionos_assistant_sitetype' );
		}

		/** skip the wizard completely (the user won't be bother by it anymore) */
		if ( isset( $_GET['ionos-assistant-cancel'] ) ) {
			update_option( 'ionos_assistant_completed', true );
		}
	}

	/**
	 * Load the themes list for a given site type (AJAX)
	 */
	public static function load_recommended_themes() {

		if ( isset( $_POST['site_type'] ) ) {
			$cache_manager = new Ionos_Assistant_Cache_Manager();
			$site_type_filter = new Ionos_Assistant_Sitetype_Filter();

			$site_type = sanitize_text_field( $_POST['site_type'] );

			// Create cache file if not created yet
			if ( ! $cache_manager->has_cache( 'theme', $site_type ) ) {
				$theme_slugs = $site_type_filter->get_theme_slugs( $site_type );
				$cache_manager->fill_theme_cache( $site_type, $theme_slugs );
			}

			// Load theme data from cache
			$themes = $cache_manager->load_cache( 'theme', $site_type );

			// Flag the current active theme in the list for information
			$active_theme_slug = wp_get_theme()->get_stylesheet();

			if ( array_key_exists( $active_theme_slug, $themes ) ) {
				$themes[ $active_theme_slug ]['active'] = true;
			}

			Ionos_Assistant_View::load_template( 'parts/site-type-theme-list', array(
				'site_type' => $site_type,
				'themes'    => $themes
			) );
		}
		die;
	}

	/**
	 * Load the preview of a selected theme (AJAX)
	 */
	public static function load_theme_preview() {
		$max_word_count = 80;

		if ( isset( $_POST['theme'] ) && isset( $_POST['site_type'] ) ) {
			$cache_manager = new Ionos_Assistant_Cache_Manager();

			$theme_slug = sanitize_text_field( $_POST['theme'] );
			$site_type = sanitize_text_field( $_POST['site_type'] );

			if ( $cache_manager->has_cache( 'theme', $site_type ) ) {
				$themes = $cache_manager->load_cache( 'theme', $site_type );

				// Deliver a short/truncated version of the description if it's too long
				if ( str_word_count( $themes[ $theme_slug ]['description'], 0 ) > $max_word_count ) {
					$word_count = array_keys(
						str_word_count( $themes[ $theme_slug ]['description'], 2 )
					);
					$themes[ $theme_slug ]['short_description'] = trim( substr(
						$themes[ $theme_slug ]['description'],
						0,
						$word_count[ $max_word_count ]
					) ) . ' [...]';
				}

				// Flag the current active theme in the list for information
				$active_theme_slug = wp_get_theme()->get_stylesheet();

				if ( array_key_exists( $active_theme_slug, $themes ) ) {
					$themes[ $active_theme_slug ]['active'] = true;
				}

				Ionos_Assistant_View::load_template( 'parts/theme-preview', array(
					'theme'     => $themes[ $theme_slug ],
					'site_type' => $site_type
				) );
			}
		}
		die;
	}

	/**
	 * Install selected plugins and themes (AJAX)
	 */
	public static function install_plugins_and_themes() {

		$sitetype_transient = 'ionos_assistant_process_sitetype_user_' . get_current_user_id();
		$theme_transient = 'ionos_assistant_process_theme_user_' . get_current_user_id();

		if ( isset( $_POST['site_type'] ) && isset( $_POST['theme'] ) ) {
			$cache_manager = new Ionos_Assistant_Cache_Manager();

			/** Increase PHP limits to avoid timeouts and memory limits */
			@ini_set( 'error_reporting', 0 );
			@ini_set( 'memory_limit', '256M' );
			@set_time_limit( 300 );

			include_once( Ionos_Assistant::get_inc_dir_path() . 'assets-manager.php' );

			$site_type = sanitize_text_field( $_POST['site_type'] );
			$theme = sanitize_text_field( $_POST['theme'] );

			$assets_manager = new Ionos_Assistant_Assets_Manager( $site_type );
			$site_type_filter = new Ionos_Assistant_Sitetype_Filter();

			/** Check nonce */
			check_admin_referer( 'activate' );

			// Set up WordPress to the use case
			$assets_manager->setup_options();

			// Activate / install chosen theme
			$assets_manager->setup_theme( $theme );

			// Look for plugins to install
			$plugins = $site_type_filter->get_plugins( $site_type, 'recommended' );

			// Create cache file if not created yet
			if ( ! $cache_manager->has_cache( 'plugin', $site_type ) ) {
				$cache_manager->fill_plugin_cache( $site_type, $plugins );
			}

			// Activate / install recommended plugins with the chosen site type
			$assets_manager->setup_plugins( array_keys( $plugins ) );

			/** store website type in db */
			update_option( 'ionos_assistant_sitetype', $site_type );

			delete_transient( $sitetype_transient );
			delete_transient( $theme_transient );

			wp_send_json_success(
				array(
					'referer' => add_query_arg(
						array(
							'return'  => home_url(),
							'message' => 'congrats'
						),
						admin_url( 'customize.php' )
					)
				)
			);
		}
	}

	/**
	 * Register the CSS and fonts for the new Assistant design
	 * (used in the Assistant & Login)
	 *
	 * @param boolean $is_login_page
	 */
	public static function enqueue_assistant_styles( $is_login_page = false ) {

		// Remove WP standard CSS in the Assistant pages
		if ( self::is_assistant_admin_page() ) {
			wp_deregister_style( 'wp-admin' );
		}

		// Add the Assistant CSS in the Assistant pages & where the Assistant adds features
		if ( $is_login_page || self::is_assistant_admin_page() || self::is_customizer_page( 'congrats' ) ) {

			wp_enqueue_style( 'ionos-assistant', Ionos_Assistant::get_css_url( 'assistant.css' ), array( 'buttons' ) );
			wp_add_inline_style( 'ionos-assistant', Ionos_Assistant_Branding::get_color_styles() );
		}
	}

	/**
	 * Register JS scripts for the Assistant
	 */
	public static function enqueue_assistant_scripts() {

		if ( self::is_assistant_admin_page() || self::is_customizer_page( 'congrats' ) ) {

			// Add the assistant JS scripts for use case filter + installation
			wp_enqueue_script( 'ionos-assistant', Ionos_Assistant::get_js_url( 'assistant.js' ), array(
				'jquery',
				'wp-util'
			), false, true );

			// Configure the AJAX object for the assistant scripts
			wp_localize_script( 'ionos-assistant', 'ajax_assistant_object', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			) );
		}
	}

	/**
	 * Add WP lightbox library (thickbox) in the Customizer page
	 * when we come from the Assistant context
	 */
	public static function configure_customizer() {
		if ( self::is_customizer_page( 'congrats' ) ) {
			add_thickbox();
		}
	}

	/**
	 * Add lightbox content in the Customizer
	 * when the Assistant is completed for the first time
	 */
	public static function add_customizer_thickbox() {
		if ( get_option( 'ionos_assistant_completed', false ) == false ) {

			/// Sets flag for the assistant being completed
			update_option( 'ionos_assistant_completed', true );

			// Render lightbox HTML (the lightbox won't open if this content isn't there)
			Ionos_Assistant_View::load_template( 'customizer-congrats-step' );
		}
	}

	/**
	 * Show the single-page Assistant
	 * (Load specific view if a current action is given)
	 */
	public static function load_assistant_page() {

		// Handle status change of the wizard
		self::handle_assistant_params();

		// Only call our process in the Assistant Admin page!
		if ( self::is_assistant_admin_page() ) {

			$site_type_filter = new Ionos_Assistant_Sitetype_Filter();
			$site_types = $site_type_filter->get_sitetypes();

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die(
					sprintf( __( 'Sorry, you do not have permission to access the %s.', 'ionos-assistant' ) ),
					__( 'Assistant', 'ionos-assistant' )
				);
			}

			Ionos_Assistant_View::load_template( 'assistant', array(
				'site_types' => $site_types
			) );
			exit;
		}
	}
}
