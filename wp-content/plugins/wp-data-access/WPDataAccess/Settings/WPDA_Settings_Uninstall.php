<?php

namespace WPDataAccess\Settings {

	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	class WPDA_Settings_Uninstall extends WPDA_Settings {

		/**
		 * Add uninstall tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   1.0.0
		 */
		protected function add_content() {

			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-uninstall-settings-' . WPDA::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {

					// Save changes.
					WPDA::set_option(
						WPDA::OPTION_WPDA_UNINSTALL_TABLES,
						isset( $_REQUEST['delete_tables'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['delete_tables'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_WPDA_UNINSTALL_OPTIONS,
						isset( $_REQUEST['delete_options'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['delete_options'] ) ) : 'off' // input var okay.
					);

				} elseif ( 'setdefaults' === $action ) {

					// Set back to default values.
					WPDA::set_option( WPDA::OPTION_WPDA_UNINSTALL_TABLES );
					WPDA::set_option( WPDA::OPTION_WPDA_UNINSTALL_OPTIONS );

				}

				$msg = new WPDA_Message_Box(
					array(
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					)
				);
				$msg->box();

			}

			$delete_tables  = WPDA::get_option( WPDA::OPTION_WPDA_UNINSTALL_TABLES );
			$delete_options = WPDA::get_option( WPDA::OPTION_WPDA_UNINSTALL_OPTIONS );

			?>

			<form id="wpda_settings_uninstall" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=uninstall">
				<table class="wpda-table-settings">
					<tr>
						<th>
							<?php echo __( 'On Plugin Uninstall', 'wp-data-access' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" name="delete_plugin" style="margin-right: 0" checked
									   disabled="disabled">
								<?php echo __( 'Delete plugin', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="delete_tables"
									   style="margin-right: 0" <?php echo 'on' === $delete_tables ? 'checked' : ''; ?>>
								<?php echo __( 'Delete plugin tables (all data will be lost)', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="delete_options"
									   style="margin-right: 0" <?php echo 'on' === $delete_options ? 'checked' : ''; ?>>
								<?php echo __( 'Delete plugin settings (all settings will be lost)', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Uninstall Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
						   jQuery('input[name=\'action\']').val('setdefaults');
						   jQuery('#wpda_settings_uninstall').trigger('submit');
						   }"
					   class="button button-secondary">
						<?php echo __( 'Reset Uninstall Settings To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-uninstall-settings-' . WPDA::get_current_user_login(), '_wpnonce', false ); ?>
			</form>

			<?php

		}

	}

}
