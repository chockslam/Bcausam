<?php
	class FundersFormClass extends \ElementorPro\Modules\Forms\Classes\Action_Base {
		public function get_name() {
			return 'funders_form-submit';
		}
		
		public function get_label() {
			return __( 'Funders Form', 'elmformaction' );
		}
		

		public function run( $record, $ajax_handler ) {
			$settings = $record->get( 'form_settings' );
		
			if ( empty( $settings['email_field'] ) ) {
				return;
			}

			if ( empty( $settings['charity_field'] ) ) {
				return;
			}
		
			
			$raw_fields = $record->get( 'fields' );
			$fields = [];
			foreach ( $raw_fields as $id => $field ) {
				$fields[ $id ] = $field['value'];
			}

			require 'mandrill-transactional.php';
			require 'db-classcode-query.php';

			// Once these inputs had been retrieved from the form submission, store them here then makethe api call to the charity comission to search the 
			// database for the tags of the charity
			$inputtedName = $fields[$setting['email_field']];
			$inputtedCharityNumber = $fields[$setting['charity_field']];
			$var = CopyDatabase($inputtedCharityNumber);
			
			// MailChimp monkey
			if (!test_connection()) {
				echo "Failed to connect to MailChimp";
			}

			
			$funderlist_short = array_slice($var, 0, 5);

			$mc_csv = build_csv($var);
			// print_r($mc_csv);

			
			$mc_content = build_content($inputtedName, $funderlist_short, $mc_csv);
			// print_r($mc_content);

			$res = post_message($mc_content);

	
			
			// TODO: Give the user a response
			// $ajax_handler->add_response_data( 'success_image', $settings['success_image']['url'] );
		}
		
		/**
		 * @param \Elementor\Widget_Base $widget
		 */
		public function register_settings_section( $widget ) {
			$widget->start_controls_section(
				'section_charityform',
				[
					'label' => __( 'Custom', 'elmformaction' ),
					'condition' => [
						'submit_actions' => $this->get_name(),
					],
				]
			);
			
			$widget->add_control(
				'email_field',
				[
					'label' => __( 'Email Field ID', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::TEXT,
				]
				);

			$widget->add_control(
				'charity_field',
				[
					'label' => __( 'Charity Field ID', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::TEXT,
				]
			);


			$widget->end_controls_section();
		}

		/**
		 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
		 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
		 */

		public function on_export($element) {}
?>