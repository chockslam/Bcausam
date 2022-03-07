<?php
/**
 * Plugin Name: 1&1 Product Subdomain
 * Plugin URI: http://www.1and1.com/
 * Description: Handles product subdomain installs in accordance with search engines best practices.
 * Version: 1.1.0
 * Author: 1&1
 * Author URI: http://www.1and1.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Product_Subdomains {

	public function __construct() {
		if ( ! is_admin() && isset( $_SERVER['SERVER_NAME'] ) ) {
			$this->load_hooks();
		}
	}

	public function load_hooks() {
		$domain = $this->get_main_domain( $_SERVER['SERVER_NAME'] );

		if ( in_array( $domain, $this->product_subdomains() ) ) {
			add_action( 'pre_option_blog_public', array( $this, 'make_blog_private' ), 1000 );
			add_action( 'default_option_rewrite_rules', array( $this, 'fix_robotstxt_rewrite' ) );
			add_action( 'option_rewrite_rules', array( $this, 'fix_robotstxt_rewrite' ) );
		}
	}

	public function make_blog_private() {
		return 0;
	}

	/**
	 * This makes robots.txt work for the default settings of permalinks
	 */
	public function fix_robotstxt_rewrite( $rewrite_rules ) {
		global $wp_rewrite;

		if ( ! empty( $wp_rewrite ) ) {
			if ( ! empty( $wp_rewrite->permalink_structure ) ) {
				return $rewrite_rules;
			}

			$wp_rewrite->rules = $rewrite_rules;

			if ( empty( $rewrite_rules ) ) {

				/** robots.txt -only if installed at the root */
				$home_path = parse_url( home_url() );

				return ( empty( $home_path['path'] ) || '/' == $home_path['path'] ) ? array( 'robots\.txt$' => $wp_rewrite->index . '?robots=1' ) : array();
			}
		}

		return $rewrite_rules;
	}


	private function get_main_domain( $domain ) {
		$domain_parts = explode( '.', $domain );
		$domain_parts = array_slice( $domain_parts, - 2 );
		$main_domain  = implode( '.', $domain_parts );

		return $main_domain;
	}

	private function product_subdomains() {
		return array(
			'apps-1and1.net',
			'apps-1and1.com'
		);
	}

}

new Product_Subdomains;
