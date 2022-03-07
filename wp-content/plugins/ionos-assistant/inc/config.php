<?php

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Ionos_Assistant_Config
 * Loads and parse the main configuration and handle the different settings
 */
class Ionos_Assistant_Config {

	/**
	 * Placeholders available in keys
	 * (ex. contract market)
	 */
	const CONFIG_KEY_PLACEHOLDER_MARKET = 'market';

	/**
	 * @var Ionos_Assistant_Config
	 */
	private static $instance;

	/**
	 * @var string[]
	 */
	private static $paths;

	/**
	 * @var array
	 */
	private $config = array();

	/**
	 * Ionos_Assistant_Config constructor
	 */
	private function __construct() {

		// Set up the paths for the cascading INI configurations
		if ( empty( self::$paths ) ) {
			self::set_paths();
		}

		// 1. INI configuration
		self::retrieve_ini_params();

		// 2. DB (WP options) configuration
		self::retrieve_db_params();

		// 3. Cookie configuration
		self::retrieve_cookie_params();
	}

	/**
	 * Retrieve the Singleton object
	 *
	 * @return Ionos_Assistant_Config
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Destructor (for testing purpose)
	 */
	public static function reset_instance() {
		self::$instance = null;
	}

	/**
	 * Singleton wrapper function to retrieve a specific parameter without much code
	 * Call: Ionos_Assistant_Config::get()
	 *
	 * @param  string $param
	 * @param  string $section
	 * @param  string $fallback_param
	 * @param  string $fallback_wording
	 * @return string
	 */
	public static function get( $param, $section = null, $fallback_param = null, $fallback_wording = null ) {
		return self::get_instance()->get_param( $param, $section, $fallback_param, $fallback_wording );
	}

	/**
	 * Singleton wrapper function to retrieve a section without much code
	 * Call: Ionos_Assistant_Config::section()
	 *
	 * @param  string $section
	 * @return array
	 */
	public static function section( $section ) {
		return self::get_instance()->get_section( $section );
	}

	/**
	 * Singleton wrapper function for feature
	 * Call: Ionos_Assistant_Config::get()
	 *
	 * @param  string $param
	 * @param  string $fallback_param
	 * @param  string $fallback_wording
	 * @return boolean
	 */
	public static function feature( $param, $fallback_param = null, $fallback_wording = null ) {
		return (bool) self::get_instance()->get_param( $param, 'features', $fallback_param, $fallback_wording );
	}

	/**
	 * Setup the config file(s) directory paths
	 *
	 * @param string[] $config_paths
	 */
	public static function set_paths( $config_paths = array() ) {

		if ( ! empty( $config_paths ) ) {
			self::$paths = (array) $config_paths;

		} else {
			self::$paths = array(
				1 => Ionos_Assistant::get_plugin_dir_path() . 'config/main.ini',
				2 => Ionos_Assistant::get_plugin_dir_path() . 'config/local.ini',
				3 => '/etc/wordpress/config.ini',
			);
		}
	}

	/**
	 * Retrieve a specific parameter
	 *
	 * @param  string $param
	 * @param  string $section
	 * @param  string $fallback_param
	 * @param  string $fallback_wording
	 * @return string
	 */
	public function get_param( $param, $section = null, $fallback_param = null, $fallback_wording = null ) {
		$key = $this->replace_placeholders( $param );
		$fallback_key = $this->replace_placeholders( $fallback_param );

		if ( get_option( 'ionos_assistant_' . $param ) !== false ) {
			return get_option( 'ionos_assistant_' . $param );
		}

		if ( ! empty( $section ) && array_key_exists( $section, $this->config ) ) {
			$config = $this->config[ $section ];
		} else {
			$config = $this->config;
		}

		if ( is_array( $config ) ) {

			if ( array_key_exists( $key, $config ) ) {
				return $config[ $key ];
			} elseif ( $fallback_key && array_key_exists( $fallback_key, $config ) ) {
				return $config[ $fallback_key ];
			}
		}

		if ( ! is_null( $fallback_wording ) ) {
			return $fallback_wording;
		}

		return null;
	}

	/**
	 * Retrieve a whole section from the configuration
	 *
	 * @param  string $section
	 * @return array
	 */
	public function get_section( $section ) {
		if ( ! empty( $section ) && array_key_exists( $section, $this->config ) && is_array( $this->config[ $section ] ) ) {
			return $this->config[ $section ];
		}
		return array();
	}

	/**
	 * Parse config params for placeholders having dynamical values
	 * (ex. contract market, languages...)
	 *
	 * @param  string $chain
	 * @return string
	 */
	private function replace_placeholders( $chain ) {
		$placeholder_open_tag = '{';
		$placeholder_close_tag = '}';

		// Contract market (CA, DE, ES, FR, IT, MX, UK, US)
		$market = Ionos_Assistant::get_market();
		$chain = str_replace(
			$placeholder_open_tag . self::CONFIG_KEY_PLACEHOLDER_MARKET . $placeholder_close_tag,
			$market,
			$chain
		);

		return $chain;
	}

	/**
	 * Parse the different configuration file(s),
	 * in the order that they have in the $paths array
	 */
	private function retrieve_ini_params() {

		foreach ( self::$paths as $file_path ) {
			if ( is_file( $file_path ) && is_readable( $file_path ) ) {

				$this->config = array_replace_recursive(
					$this->config,
					parse_ini_file( $file_path, true )
				);
			}
		}
	}

	/**
	 * Configuration can be overwritten by database entries
	 * Each database entry is a "WP option"
	 */
	private function retrieve_db_params() {

		foreach ( $this->config as $section => $params ) {
			foreach ( $params as $param => $value ) {

				$wp_option_key = 'ionos_assistant_config.' . $section . '.' . $param;
				$wp_option_value = get_option( $wp_option_key );

				if ( false !== $wp_option_value ) {

					// We make sure the option is passed as string just like in the INI files
					$this->config[ $section ][ $param ] = (string) $wp_option_value;
				}
			}
		}
	}

	/**
	 * Configuration can be overwritten by a cookie with single parameter change
	 * Each cookie name must be prefixed with "assistant-config-" to work
	 */
	private function retrieve_cookie_params() {

		if ( array_key_exists( 'features', $this->config ) && is_array( $this->config['features'] ) ) {
			foreach ( $this->config['features'] as $param => $value ) {

				if ( isset( $_COOKIE[ 'ionos-assistant-config-' . $param ] ) ) {
					$this->config['features'][ $param ] = filter_var( $_COOKIE[ 'ionos-assistant-config-' . $param ], FILTER_SANITIZE_STRING );
				}
			}
		}
	}
}
