<?php

namespace WPDataAccess\Settings {

	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	class WPDA_Settings_ManageRoles extends WPDA_Settings {

		/**
		 * Add roles tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   2.7.0
		 */
		protected function add_content() {
			$wp_deault_roles = array(
				'administrator' => true,
				'editor'        => true,
				'author'        => true,
				'contributor'   => true,
				'subscriber'    => true,
			);

			if ( isset( $_REQUEST['action'] ) ) {
				// Security check.
				if ( 'delete' === $_REQUEST['action'] ) {
					$wp_nonce = isset( $_REQUEST['_wpnoncedelrole'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnoncedelrole'] ) ) : ''; // input var okay.
					if ( ! wp_verify_nonce( $wp_nonce, 'wpda-manage-roles-settings-' . WPDA::get_current_user_login() ) ) {
						wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
					}
				} else {
					$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
					if ( ! wp_verify_nonce( $wp_nonce, 'wpda-manage-roles-settings-' . WPDA::get_current_user_login() ) ) {
						wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
					}
				}

				if ( 'save' === $_REQUEST['action'] ) {
					WPDA::set_option(
						WPDA::OPTION_WPDA_ENABLE_ROLE_MANAGEMENT,
						isset( $_REQUEST['enable_role_management'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['enable_role_management'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_WPDA_USE_ROLES_IN_SHORTCODE,
						isset( $_REQUEST['use_roles_in_shortcode'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['use_roles_in_shortcode'] ) ) : 'off' // input var okay.
					);

					if ( isset( $_REQUEST['wpda_role_name'] ) && is_array( $_REQUEST['wpda_role_name'] ) &&
						isset( $_REQUEST['wpda_role_label'] ) && is_array( $_REQUEST['wpda_role_label'] )
					) {
						$no_roles = count( $_REQUEST['wpda_role_name'] );
						for ( $i = 0; $i < $no_roles; $i ++ ) {
							$sanitized_new_role_name  = sanitize_text_field( wp_unslash( $_REQUEST['wpda_role_name'][ $i ] ) ); // input var okay.
							$sanitized_new_role_label = sanitize_text_field( wp_unslash( $_REQUEST['wpda_role_label'][ $i ] ) ); // input var okay.
							add_role( $sanitized_new_role_name, $sanitized_new_role_label );
						}
					}
					$msg = new WPDA_Message_Box(
						array(
							'message_text' => __( 'Settings saved', 'wp-data-access' ),
						)
					);
					$msg->box();
				} elseif ( 'delete' === $_REQUEST['action'] ) {
					if ( isset( $_REQUEST['delete_role_name'] ) ) {
						$sanitized_role_name = sanitize_text_field( wp_unslash( $_REQUEST['delete_role_name'] ) ); // input var okay.
						$all_users           = get_users(
							array( 'role' => $sanitized_role_name )
						);
						foreach ( $all_users as $user ) {
							$wp_user = new \WP_User( $user->ID );
							$wp_user->remove_role( $sanitized_role_name );
						}
						remove_role( $sanitized_role_name );

						$msg = new WPDA_Message_Box(
							array(
								'message_text' => __( 'Settings saved', 'wp-data-access' ),
							)
						);
						$msg->box();
					}
				} elseif ( 'setdefaults' === $_REQUEST['action'] ) {
					// Set back to default values.
					WPDA::set_option( WPDA::OPTION_WPDA_ENABLE_ROLE_MANAGEMENT );
					WPDA::set_option( WPDA::OPTION_WPDA_USE_ROLES_IN_SHORTCODE );
				}
			}

			$enable_role_management = WPDA::get_option( WPDA::OPTION_WPDA_ENABLE_ROLE_MANAGEMENT );
			$use_roles_in_shortcode = WPDA::get_option( WPDA::OPTION_WPDA_USE_ROLES_IN_SHORTCODE );
			?>
			<form id="wpda_settings_manage_roles"
				  method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=roles">

				<table class="wpda-table-settings">

					<tr>
						<th>
							<?php echo __( 'Plugin Role Management', 'wp-data-access' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" name="enable_role_management"
									<?php echo 'on' === $enable_role_management ? 'checked' : ''; ?>/>
								<?php echo __( 'Enable role management', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="use_roles_in_shortcode"
									<?php echo 'on' === $use_roles_in_shortcode ? 'checked' : ''; ?>/>
								<?php echo __( 'Use roles in Data Projects shortcodes', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th>
							<?php echo __( 'Available Roles', 'wp-data-access' ); ?>
						</th>
						<td>
							<div id="list_roles">
								<?php
								global $wp_roles;
								foreach ( $wp_roles->roles as $role => $val ) {
									$is_wp_role = isset( $wp_deault_roles[ $role ] );
									$role_label = isset( $val['name'] ) ? $val['name'] : $role;
									?>
									<div id="<?php echo esc_attr( $role ); ?>">
										<span class="dashicons <?php echo $is_wp_role ? 'dashicons-wordpress' : 'dashicons-trash'; ?>"
									  		style="font-size: 14px; vertical-align: text-top;<?php echo $is_wp_role ? '' : ' cursor: pointer;'; ?>"></span>
										<?php echo esc_attr( $role_label ); ?>
									</div>
									<?php
								}
								?>
							</div>
							<p>
								<a href="javascript:void(0)" class="button" onclick="add_new_role()">Add
									New Role</a>
							</p>
						</td>
					</tr>

				</table>

				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Manage Roles Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
						   jQuery('input[name=\'action\']').val('setdefaults');
						   jQuery('#wpda_settings_manage_roles').trigger('submit');
						   }"
					   class="button button-secondary">
						<?php echo __( 'Reset Manage Roles Settings To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-manage-roles-settings-' . WPDA::get_current_user_login(), '_wpnonce', false ); ?>

			</form>

			<form id="delete_role_form"
				  method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=roles">
				<input type="hidden" id="delete_role_name" name="delete_role_name" value="">
				<input type="hidden" name="action" value="delete">
				<?php wp_nonce_field( 'wpda-manage-roles-settings-' . WPDA::get_current_user_login(), '_wpnoncedelrole', false ); ?>
			</form>


			<script type='text/javascript'>
				function add_new_role() {
					jQuery('#list_roles').append(
						'<div>' +
						'  <span class="dashicons dashicons-trash" style="font-size: 14px; vertical-align: text-top; cursor: pointer;" onclick="jQuery(this).parent().remove();"></span>' +
						'  <label for="wpda_role_name[]">Name: </label><input name="wpda_role_name[]" style="vertical-align: middle; text-transform: lowercase;"/>' +
						'  <label for="wpda_role_label[]">Label: </label><input name="wpda_role_label[]" style="vertical-align: middle;"/>' +
						'</div>');
				}

				jQuery('.dashicons-trash').on('click', function (e) {
					if (confirm('<?php echo __( 'Delete role?', 'wp-data-access' ) . '\n' . __( 'Role will be removed from all users.', 'wp-data-access' ) . '\n' . __( 'This action cannot be undone!', 'wp-data-access' ); ?>')) {
						parent = jQuery(e.target).parent();
						parent_id = parent.attr('id');
						jQuery('#delete_role_name').val(parent_id);
						jQuery('#delete_role_form').submit();
					}
				});
			</script>

			<?php
		}

	}

}
