<?php
/**
 * Branda Admin Custom CSS class.
 *
 * @package Branda
 * @subpackage AdminArea
 */
if ( ! class_exists( 'Branda_Admin_Css' ) ) {
	class Branda_Admin_Css extends Branda_Helper {
		protected $option_name = 'ub_admin_css';

		public function __construct() {
			parent::__construct();
			add_filter( 'ultimatebranding_settings_custom_admin_css', array( $this, 'admin_options_page' ) );
			add_filter( 'ultimatebranding_settings_custom_admin_css_process', array( $this, 'update' ), 10, 1 );
			add_action( 'admin_head', array( $this, 'output' ) );
			add_action( 'init', array( $this, 'upgrade_options' ) );
		}

		/**
		 * Upgrade option
		 *
		 * @since 2.0.0
		 */
		public function upgrade_options() {
			$value = $this->get_value();
			if ( is_string( $value ) ) {
				$value = array( 'admin' => array( 'css' => $value ) );
				$this->update_value( $value );
			}
			/**
			 * Change option name
			 *
			 * @since 3.0.0
			 */
			$old_name = 'global_admin_css';
			$value    = branda_get_option( $old_name );
			if ( ! empty( $value ) ) {
				$this->update_value( $value );
				branda_delete_option( $old_name );
			}
		}

		/**
		 * Set options
		 *
		 * @since 2.0.0
		 */
		protected function set_options() {
			$options       = array(
				'admin' => array(
					'title'       => __( 'Admin CSS', 'ub' ),
					'description' => __( 'For more advanced customization of admin pages use the CSS. This will be added to the header of every admin page.', 'ub' ),
					'hide-th'     => true,
					'placeholder' => esc_attr__( 'Enter custom CSS here…', 'ub' ),
					'fields'      => array(
						'css' => array(
							'type'          => 'css_editor',
							'label'         => __( 'Cascading Style Sheets', 'ub' ),
							'ace_selectors' => array(
								array(
									'title'     => '',
									'selectors' => array(
										'#wpadminbar'    => __( 'Bar', 'ub' ),
										'#wpcontent'     => __( 'Content', 'ub' ),
										'#wpbody'        => __( 'Body', 'ub' ),
										'#wpfooter'      => __( 'Footer', 'ub' ),
										'#adminmenu'     => __( 'Menu', 'ub' ),
										'#adminmenuwrap' => __( 'Menu Wrap', 'ub' ),
									),
								),
							),
						),
					),
				),
			);
			$this->options = $options;
		}

		public function output() {
			$value = $this->get_value( 'admin', 'css' );
			if ( empty( $value ) ) {
				return;
			}
			printf(
				'<style id="%s" type="text/css">%s</style>',
				esc_attr( __CLASS__ ),
				stripslashes( $value )
			);
		}
	}
}
new Branda_Admin_Css();
