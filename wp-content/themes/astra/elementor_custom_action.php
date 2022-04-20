<?php


class FundersFormClass extends \ElementorPro\Modules\Forms\Classes\Action_Base {
	
	public function get_name() {
			return 'funders_form';
		}
		
		public function get_label() {
			return __( 'Funders Form', 'text-domain' );
		}
		
		/**
		 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
		 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
		 */
		
		public function run( $record, $ajax_handler ) {
			$settings = $record->get( 'form_settings' );
		
			if ( empty( $settings['email'] ) ) {
				return;
			}
			
			if ( empty( $settings['charity_number'] ) ) {
				return;
			}
			
			$ajax_handler->add_response_data( 'You did it' );
			
			$raw_fields = $record->get( 'fields' );
			$fields = [];
			foreach ( $raw_fields as $id => $field ) {
				$fields[ $id ] = $field['value'];
			}
			
			require 'mandrill-transactional.php';
			require 'db-classcode-query.php';
			
			// Once these inputs had been retrieved from the form submission, store them here then makethe api call to the charity comission to search the 
			// database for the tags of the charity
			

			// $inputtedName = $fields[$setting['email']];
			// $inputtedCharityNumber = $fields[$setting['charity_number']];
			
			
			$inputtedName = $fields['email'];
			$inputtedCharityNumber = $fields['charity_number'];
			// MailChimp monkey
			if (!test_connection()) {
				echo "Failed to connect to MailChimp";
			}
			
			$var = CopyDatabase($inputtedCharityNumber);
			
			if (is_null($var)) {
				test_email($inputtedName);
			}
			
			$funderlist_short = array_slice($var, 0, 5);
			$mc_csv = build_csv($var);
			$mc_content = build_content($inputtedName, $funderlist_short, $mc_csv);
			$res = post_message($mc_content);

			

			// TODO: Give the user a response
		}
		
		/**
		 * @param \Elementor\Widget_Base $widget
		 */
		public function register_settings_section( $widget ) {
			$widget->start_controls_section(
				'section_funders_form',
				[
					'label' => __( 'funders_form', 'text-domain' ),
					'condition' => [
						'submit_actions' => $this->get_name(),
					],
				]
			);
			
			$widget->add_control(
				'email',
				[
					'label' => __( 'email', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::TEXT,
				]
				);

			$widget->add_control(
				'charity_number',
				[
					'label' => __( 'charity_number', 'text-domain' ),
					'type' => \Elementor\Controls_Manager::TEXT,
				]
			);

			$widget->end_controls_section();
		}

		/**
		 * On Export
		 *
		 * Clears form settings on export
		 * @access Public
		 * @param array $element
	 */

		public function on_export($element) {
			unset(
				$element['email'],
				$element['charity_number'],
			);
		}
	}
?>