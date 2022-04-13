<?php
	class FundersFormClass extends \ElementorPro\Modules\Forms\Classes\Action_Base {
		public function get_name() {
			return 'funders_form';
		}

		public function get_label() {
			return __( 'Funders Form', 'elmformaction' );
		}

		/**
		 * @param \Elementor\Widget_Base $widget
		 */
		public function register_settings_section( $widget ) {
			$widget->start_controls_section(
				'section_custom',
				[
					'label' => __( 'Custom', 'elmformaction' ),
					'condition' => [
						'submit_actions' => $this->get_name(),
					],
				]
			);

			$widget->add_control(
				'success_image',
				[
					'label' => __( 'Success Image', 'elmformaction' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'label_block' => true,
					'separator' => 'before',
					'description' => __( 'Select the image to be displayed after the form is submitted successfully.', 'elmformaction' ),
				]
			);

			$widget->end_controls_section();
		}

		/**
		 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
		 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
		 */
		public function run( $record, $ajax_handler ) {
			$settings = $record->get( 'form_settings' );

			if ( empty( $settings['success_image'] ) ) {
				return;
			}

			$ajax_handler->add_response_data( 'success_image', $settings['success_image']['url'] );
		}

		public function on_export($element) {}
?>