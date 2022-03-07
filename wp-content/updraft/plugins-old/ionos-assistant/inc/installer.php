<?php
// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
include_once( ABSPATH . '/wp-admin/includes/class-wp-upgrader.php' );
include_once 'automatic-installer-skin.php';

class Ionos_Assistant_Installer {

	/**
	 * Install a plugin package
	 *
	 * @param  array $plugin
	 * @return boolean
	 */
	static public function install_plugin( $plugin ) {
		$upgrader = new Plugin_Upgrader( new Ionos_Automatic_Installer_Skin() );

		if ( array_key_exists( 'custom_link', $plugin ) ) {
			$download_link = $plugin[ 'custom_link' ];
		} else {
			$download_link = 'https://downloads.wordpress.org/plugin/' . $plugin['slug'] . '.zip';
		}
		return ( bool ) $upgrader->install( $download_link );
	}

	/**
	 * Update an existing (= installed) plugin
	 *
	 * @param  string $plugin_path
	 * @return boolean
	 */
	static public function update_plugin( $plugin_path ) {
		$upgrader = new Plugin_Upgrader( new Ionos_Automatic_Installer_Skin() );

		return (bool) $upgrader->upgrade( $plugin_path );
	}

	/**
	 * Get the list of installation paths from given plugins (in the plugins directory)
	 *
	 * @param  array $plugin_slugs
	 * @return array
	 */
	static public function get_plugin_installation_paths( $plugin_slugs = array() ) {

		/** @todo check if this is really needed to get the last state of the plugins? */
		wp_clean_plugins_cache( true );

		$plugins = get_plugins();
		$plugins_installed = array_flip( $plugin_slugs );

		foreach ( $plugins as $plugin_path => $plugin ) {
			$parts = explode( '/', $plugin_path );

			if ( empty( $plugin_slugs ) || array_key_exists( $parts[0], $plugins_installed ) ) {
				$plugins_installed[ $parts[0] ] = $plugin_path;
			}
		}
		return $plugins_installed;
	}

	/**
	 * Install a theme package
	 *
	 * @param  array $theme_meta
	 * @return boolean
	 */
	static public function install_theme( $theme_meta ) {
		$upgrader = new Theme_Upgrader( new Ionos_Automatic_Installer_Skin() );

		return ( bool ) $upgrader->install(
			'https://downloads.wordpress.org/theme/' . $theme_meta['slug'] . '.zip'
		);
	}
}