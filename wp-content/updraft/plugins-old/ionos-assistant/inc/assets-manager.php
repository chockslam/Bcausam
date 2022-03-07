<?php

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

class Ionos_Assistant_Assets_Manager {

	/**
	 * @var string
	 */
	protected $site_type = '';

	/**
	 * @var Ionos_Assistant_Cache_Manager
	 */
	protected $cache_manager;

	/**
	 * @var Ionos_Assistant_Assets_Adapter
	 */
	protected $assets_adapter;

	/**
	 * Ionos_Assistant_Assets_Manager constructor.
	 *
	 * @param string $site_type
	 */
	public function __construct( $site_type = null ) {
		include_once( Ionos_Assistant::get_inc_dir_path() . 'installer.php' );
		include_once( Ionos_Assistant::get_inc_dir_path() . 'assets-adapter.php' );

		$this->site_type = $site_type;
		$this->cache_manager = new Ionos_Assistant_Cache_Manager();
		$this->assets_adapter = new Ionos_Assistant_Assets_Adapter();
	}

	/**
	 * Activate some options in WordPress core depending on the use case
	 */
	public function setup_options() {

		$site_type_filter = new Ionos_Assistant_Sitetype_Filter();
		$site_type_config = $site_type_filter->get_sitetype( $this->site_type );

		// Use case specifies if we have a static page as homepage or a list of the last posts
		if ( isset( $site_type_config[ 'homepage' ] ) && $site_type_config[ 'homepage' ] === 'static' ) {

			// Creates a page if no homepage has been set yet
			if ( get_option( 'show_on_front' ) !== 'page' ) {
				$home_page = $this->create_assistant_home_page();
				
				if ( $home_page ) {
					update_option( 'page_on_front', $home_page );
					update_option( 'show_on_front', 'page' );
				}
			}
		
		} else {
			update_option( 'show_on_front', 'posts' );
		}
	}
	
	/**
	 * Install and activate recommended plugins for the current site type
	 *
	 * @param array $selected_plugin_slugs
	 */
	public function setup_plugins( $selected_plugin_slugs ) {

		// Get plugins from the cache or load their data directly
		if ( $this->site_type ) {
			$plugins = $this->cache_manager->load_cache( 'plugin', $this->site_type );

		} else {
			$plugins = array();

			foreach ( $selected_plugin_slugs as $plugin_slug ) {
				$plugins[ $plugin_slug ] = $this->cache_manager->get_data_from_api( 'plugin', $plugin_slug );
			}
		}

		// Download and install missing plugins
		$this->install_missing_plugins( $selected_plugin_slugs, $plugins );

		// Activate the previously installed/updated plugins
		$this->activate_plugins( $selected_plugin_slugs );
	}

	/**
	 * Install and activate given plugin
	 * 
	 * @param string $plugin_slug
	 * @return boolean
	 */
	public function setup_single_plugin( $plugin_slug ) {

		$site_type_filter = new Ionos_Assistant_Sitetype_Filter();
		$installed = false;

		// Check if the plugin is already installed
		$installed_plugins = get_plugins();

		foreach ( $installed_plugins as $plugin_path => $wp_plugin_data ) {
			$parts = explode( '/', $plugin_path );
			if ( $parts[ 0 ] == $plugin_slug ) {
				$installed = true;
			}
		}

		// Install desired plugin
		if ( ! $installed ) {
			
			// Get metadata from the cache
			if ( $this->site_type ) {
				$plugins = $this->cache_manager->load_cache( 'plugin', $this->site_type );
			} else {
				$plugins = array();
			}
			
			// Load plugin data if it can't be found in the cache
			if ( ! is_array( $plugins ) || ! array_key_exists( $plugin_slug, $plugins ) ) {
				$plugin_data = array_merge(
					$this->cache_manager->get_data_from_api( 'plugin', $plugin_slug ),
					$site_type_filter->get_plugin_config( $plugin_slug )
				);
			} else {
				$plugin_data = $plugins[ $plugin_slug ];
			}

			$installed = Ionos_Assistant_Installer::install_plugin( $plugin_data );
		}
		
		// Activate plugin once installed
		if ( $installed ) {

			// Post actions after installation
			do_action( 'ionos_assistant_plugin_post_install_' . $plugin_slug );

			// Activation
			$this->activate_plugins( array( $plugin_slug ) );
			return true;
		}
		return false;
	}

	/**
	 * Install and activate a recommended theme for the current site type,
	 * chosen by the user
	 *
	 * @param string $theme_slug
	 */
	public function setup_theme( $theme_slug ) {

		if ( ! empty( $theme_slug ) ) {
			$installed_themes = wp_get_themes();

			// Get theme download info and install it
			if ( ! array_key_exists( $theme_slug, $installed_themes ) ) {

				if ( $this->site_type ) {
					$themes = $this->cache_manager->load_cache( 'theme', $this->site_type );
				} else {
					$themes[ $theme_slug ] = $this->cache_manager->get_data_from_api( 'theme', $theme_slug );
				}
				$installed = Ionos_Assistant_Installer::install_theme( $themes[ $theme_slug ] );

				// Post actions after installation
				if ( $installed ) {
					do_action( 'ionos_assistant_theme_post_install_' . $theme_slug );
				}
			}
			
			// Activate theme
			switch_theme( $theme_slug );
			do_action( 'ionos_assistant_theme_post_activate_' . $theme_slug );
		}
	}

	/**
	 * Install given set of plugins
	 *
	 * @param array $plugin_slugs
	 * @param array $plugin_data
	 */
	public function install_missing_plugins( $plugin_slugs, $plugin_data ) {
		
		$installed_plugins = get_plugins();
		$installed_plugin_slugs = array();

		foreach ( $installed_plugins as $plugin_path => $wp_plugin_data ) {
			$parts = explode( '/', $plugin_path );
			$installed_plugin_slugs[] = $parts[ 0 ];
		}

		foreach ( $plugin_slugs as $plugin_slug ) {

			if ( ! in_array( $plugin_slug, $installed_plugin_slugs ) ) {
				$installed = Ionos_Assistant_Installer::install_plugin( $plugin_data[ $plugin_slug ] );

				// Post actions after installation
				if ( $installed ) {
					do_action( 'ionos_assistant_plugin_post_install_' . $plugin_slug );
				}
			}
		}
	}

	/**
	 * Activate a given set of plugins
	 * 
	 * @param array $plugin_slugs
	 */
	public function activate_plugins( $plugin_slugs ) {
		
		// Get plugins installation paths
		$plugin_paths = Ionos_Assistant_Installer::get_plugin_installation_paths( $plugin_slugs );

		// Activate the previously installed plugins
		foreach ( $plugin_paths as $plugin_slug => $plugin_path ) {
			$plugin_base_path = plugin_basename( $plugin_path );
			
			try {

				// Plugin activation
				activate_plugin( $plugin_base_path );

				// Plugins state update
				$recent = ( array ) get_option( 'recently_activated' );
				unset( $recent[ $plugin_base_path ] );
				update_option( 'recently_activated', $recent );

				// Post actions after activation
				do_action( 'ionos_assistant_plugin_post_activate_' . $plugin_slug );
			}
			
			catch ( Exception $e ) {
				error_log( $e->getMessage() );
			}
		}
	}
	
	/**
	 * Generate a home page if the page does not exists
	 * Return false if the page has already been generated
	 *
	 * @return int | boolean
	 */
	function create_assistant_home_page() {
		
		$query = new WP_Query(
			array(
				'post_type'   => 'page',
				'post_status' => array( 'any', 'trash' ),
				'meta_query'  => array(
					array(
						'key'     => 'assistant_home_page',
						'value'   => 1,
						'compare' => '=',
					),
				),
			)
		);

		// Page already exists, regardless if it's been put in the trash or not
		if ( count( $query->posts ) > 0 ) {
			return false;
			
		// Page doesn't exist at all and must be created
		} else {
			$assistant_home_page = wp_insert_post(
				array(
					'post_content'   => Ionos_Assistant_View::get_template_content(
						'content/wp-default-homepage',
						array( 'url' => admin_url() )
					),
					'post_title'     => sprintf(
						__( "Welcome to %s", 'ionos-assistant' ),
						home_url()
					),
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'comment_status' => 'closed',
					'ping_status'    => 'open',
					'meta_input'     => array(
						'assistant_home_page' => 1
					)
				)
			);

			if ( $assistant_home_page instanceof WP_Error ) {
				return false;
			}
			return $assistant_home_page;
		}
	}
}
