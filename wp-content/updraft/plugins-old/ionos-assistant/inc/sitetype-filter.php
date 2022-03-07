<?php

/**
 * Class Ionos_Assistant_Sitetype_Filter
 * Retrieves Use Cases data from the sitetype-config.json
 */
class Ionos_Assistant_Sitetype_Filter {

	/**
	 * @var array
	 */
	private $config_paths = array();

	/**
	 * @var string
	 */
	private $market;

	/**
	 * @var boolean
	 */
	private $use_transient;
	
	/**
	 * Ionos_Assistant_Sitetype_Filter constructor.
	 *
	 * @param string  $config_paths
	 * @param string  $market
	 * @param boolean $use_transient
	 */
	function __construct( $config_paths = null, $market = null, $use_transient = true ) {

		if ( is_array( $config_paths ) ) {
			$this->config_paths = $config_paths;
		} else {
			$this->config_paths = array(
				'sitetypes' => Ionos_Assistant::get_plugin_dir_path() . 'config/sitetypes.json',
				'plugins'   => Ionos_Assistant::get_plugin_dir_path() . 'config/plugins.json'
			);
		}
		
		if (! empty( $market ) ) {
			$this->market = $market;
		} else {
			$this->market = Ionos_Assistant::get_market();
		}

		$this->use_transient = ( bool ) $use_transient;
	}

	/**
	 * Get the list of Use Cases,
	 * each one with an array of associated data if $with_data is set to true.
	 * Data includes Use Case's:
	 * - title,
	 * - description,
	 * - image path.
	 *
	 * @param  bool $with_data
	 * @return array | bool
	 */
	public function get_sitetypes( $with_data = true ) {
		$config = $this->get_config();

		if ( empty( $config ) || empty( $config[ 'sitetypes' ] ) ) {
			return false;
		}

		$sitetypes = array();
		$data_format = array(
			"headline"    => "",
			"description" => "",
			"image"       => ""
		);

		foreach ( $config[ 'sitetypes' ] as $key => $data ) {
			if ( $key !== 'any' ) {

				if ( $with_data ) {
					$sitetypes[$key] =  array_intersect_key(
						$data,
						$data_format
					);

				} else {
					$sitetypes[] = $key;
				}
			}
		}

		return $sitetypes;
	}

	/**
	 * Get all the data of a particular use case
	 * 
	 * @param string $site_type
	 * @return array | bool
	 */
	public function get_sitetype( $site_type ) {
		$config = $this->get_config();

		if ( empty( $config ) || empty( $config[ 'sitetypes' ] ) ) {
			return false;
		}

		if ( array_key_exists( $site_type, $config[ 'sitetypes' ] )
		     && is_array( $config[ 'sitetypes' ][ $site_type ] ) ) {
			
			return $config[ 'sitetypes' ][ $site_type ];
		}
		return false;
	}
	
	/**
	 * Get themes (as slugs) for a Use Case, among the list of selected themes
	 *
	 * @param  string $sitetype
	 * @return array
	 */
	public function get_theme_slugs( $sitetype ) {
		$config = $this->get_config();

		$theme_slugs = array();

		if ( ! empty( $config[ 'sitetypes' ][ $sitetype ][ 'themes' ] ) ) {
			foreach ( $config[ 'sitetypes' ][ $sitetype ][ 'themes' ] as $theme_slug ) {
				$theme_slugs[] = $theme_slug;
			}
		}
		return $theme_slugs;
	}

	/**
	 * Get the active theme's name
	 *
	 * @return string
	 */
	public function get_active_theme_name() {
		$theme_name = ucwords( str_replace( array( '-', '_' ), ' ', get_template() ) );

		return $theme_name;
	}

	/**
	 * Get plugins' config data for a given Use Case
	 *
	 * @param  string $sitetype
	 * @param  array  $include_categories
	 * @return array
	 */
	public function get_plugins( $sitetype, $include_categories = array() ) {
		$config = $this->get_config();

		if ( empty( $config[ 'plugins' ] ) || empty( $sitetype ) ) {
			return array();
		}

		$plugins = array();
		$include_categories = ( array ) $include_categories;

		foreach ( $config[ 'plugins' ] as $plugin_slug => $plugin_config ) {

			if ( ! array_key_exists( $plugin_slug, $plugins ) ) {

				$is_plugin_available_for_sitetype =
					( ! empty( $plugin_config[ 'category' ][ 'any' ] )
					  || ! empty( $plugin_config[ 'category' ][ $sitetype ] ) );

				$is_plugin_available_for_market =
					( ( $this->market == 'any' ) || 
					  ( $plugin_config[ 'markets' ] == 'any' 
					    || ( is_array( $plugin_config[ 'markets' ] ) 
					         && in_array( $this->market, $plugin_config[ 'markets' ] ) ) ) );

				$is_plugin_available_for_category =
					( $is_plugin_available_for_sitetype
					  && ( empty( $include_categories )
					     || ( ! empty( $plugin_config[ 'category' ][ 'any' ] )
					          && in_array( $plugin_config[ 'category' ][ 'any' ], $include_categories ) )
					     || ( ! empty( $plugin_config[ 'category' ][ $sitetype ] )
					          && in_array( $plugin_config[ 'category' ][ $sitetype ], $include_categories ) ) ) );

				if ( $is_plugin_available_for_category
				     && $is_plugin_available_for_market ) {

					$plugins[ $plugin_slug ] = $plugin_config;
				}
			}
		}

		return $plugins;
	}

	/**
	 * Get a plugin's config data
	 * 
	 * @param  string $plugin_slug
	 * @return array
	 */
	public function get_plugin_config( $plugin_slug ) {
		$config = $this->get_config();
		
		if ( ! empty( $config[ 'plugins' ] ) && array_key_exists( $plugin_slug, $config[ 'plugins' ] ) ) {
			return $config[ 'plugins' ][ $plugin_slug ];
		} else {
			return array();
		}
	}

	/**
	 * Retrieve the configuration (from the WP transient or directly from the JSON files)
	 * https://codex.wordpress.org/Transients_API
	 *
	 * @return mixed
	 */
	public function get_config() {
		
		if ( $this->use_transient ) {
			$config = get_transient( 'one_and_one_sitetype_config' );
		}

		if ( empty( $config ) || isset( $_GET['refresh_sitetype_config'] ) ) {
			$sitetypes = $this->load_json_data( $this->config_paths[ 'sitetypes' ] );
			$plugins = $this->load_json_data( $this->config_paths[ 'plugins' ] );

			if ( $sitetypes && $plugins ) {
				$config = array_merge(
					$sitetypes,
					$plugins
				);

				if ( $this->use_transient ) {
					set_transient( 'one_and_one_sitetype_config', $config, 300 );
				}

				return $config;
			}
			return false;
		}
		return $config;
	}

	/**
	 * Parse JSON from a file and return the data
	 * 
	 * @param string  $filename
	 * @param boolean $assoc
	 *
	 * @return boolean|mixed
	 */
	public function load_json_data( $filename, $assoc = true )
	{
		if ( is_file( $filename ) && is_readable( $filename ) ) {
			return json_decode(
				file_get_contents( $filename ),
				( bool ) $assoc
			);
		}

		return false;
	}
}
