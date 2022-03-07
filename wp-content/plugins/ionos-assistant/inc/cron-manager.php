<?php
// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Ionos_Cron_Manager {

	/**
	 * Ionos_Cron_Update_Plugin_Meta constructor.
	 * (Set up cron jobs only in Managed mode)
	 */
	public function __construct() {

		add_action( 'login_form', array( $this, 'setup_schedule' ) );
		add_action( 'ionos_assistant_cron_update_meta_cache',
			array( $this, 'update_meta_cache' ) );
		add_action( 'ionos_assistant_cron_cleanup_expired_options',
			array( $this, 'cleanup_expired_options' ) );
	}

	/**
	 * Schedule cron jobs once a day
	 */
	public function setup_schedule() {
		if ( ! wp_next_scheduled( 'ionos_assistant_cron_update_meta_cache' ) ) {
			wp_schedule_event( time(), 'daily',
				'ionos_assistant_cron_update_meta_cache' );
		}
		if ( ! wp_next_scheduled( 'ionos_assistant_cron_cleanup_expired_options' ) ) {
			wp_schedule_event( time(), 'daily',
				'ionos_assistant_cron_cleanup_expired_options' );
		}
	}

	/**
	 * Update recommended plugins/themes TXT cache
	 */
	public function update_meta_cache() {
		include_once 'cache-manager.php';
		include_once 'sitetype-filter.php';

		$cache_manager    = new Ionos_Assistant_Cache_Manager();
		$site_type_filter = new Ionos_Assistant_Sitetype_Filter();

		$cache_manager->fill_cache( $site_type_filter );
	}

	/**
	 * Clean up expired transients and expired cache/session garbage in 'options' table
	 */
	public function cleanup_expired_options() {
		global $wpdb;

		// Select all expired transients in 'options' table
		$expired_transients = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM $wpdb->options
				 WHERE option_name LIKE %s
				 AND option_value < %s",
				'%_transient_timeout_%',
				time()
			)
		);

		// Delete all selected transients
		if ( ! empty( $expired_transients ) ) {
			foreach ( $expired_transients as $value ) {
				if ( strpos( $value, '_site_' ) === 0 ) {
					$transient = str_replace( '_site_transient_timeout_', '', $value );
					delete_site_transient( $transient );
				} else {
					$transient = str_replace( '_transient_timeout_', '', $value );
					delete_transient( $transient );
				}
			}
		}

		// Delete WooCommerce & Enfold DB spam
		// https://wordpress.org/plugins/delete-expired-transients/ (for WooCommerce trick)
		// https://kriesi.at/support/topic/cleanup-wp_options-table-aviaasset_avia-head-scripts/
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
				'%_wc_session_%',
				'%aviaAsset_avia-head-scripts%'
			)
		);
	}
}

new Ionos_Cron_Manager();
