<?php

namespace WPDataAccess\Settings {

	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	class WPDA_Settings_Dashboard extends WPDA_Settings {

		const DASHBOARD_ROLES              = 'wpda_dashboard_roles';
		const DASHBOARD_USERS              = 'wpda_dashboard_users';
		const DASHBOARD_ROLES_HIDE_DEFAULT = 'wpda_dashboard_roles_hide_default';
		const DASHBOARD_USERS_HIDE_DEFAULT = 'wpda_dashboard_users_hide_default';
		const DASHBOARD_ROLES_CREATE       = 'wpda_dashboard_roles_create';
		const DASHBOARD_USERS_CREATE       = 'wpda_dashboard_users_create';

		protected function add_content() {
			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-dashboard-settings-' . WPDA::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {
					$this->update( 'dashboard_roles', self::DASHBOARD_ROLES );
					$this->update( 'dashboard_users', self::DASHBOARD_USERS );
					$this->update( 'hide_default_roles', self::DASHBOARD_ROLES_HIDE_DEFAULT );
					$this->update( 'hide_default_users', self::DASHBOARD_USERS_HIDE_DEFAULT );
					$this->update( 'create_roles', self::DASHBOARD_ROLES_CREATE );
					$this->update( 'create_users', self::DASHBOARD_USERS_CREATE );
				} elseif ( 'setdefaults' === $action ) {
					delete_option( self::DASHBOARD_ROLES );
					delete_option( self::DASHBOARD_USERS );
					delete_option( self::DASHBOARD_ROLES_HIDE_DEFAULT );
					delete_option( self::DASHBOARD_USERS_HIDE_DEFAULT );
					delete_option( self::DASHBOARD_ROLES_CREATE );
					delete_option( self::DASHBOARD_USERS_CREATE );
				}

				$msg = new WPDA_Message_Box(
					array(
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					)
				);
				$msg->box();
			}

			global $wp_roles;
			$roles = $wp_roles->roles;
			unset( $roles['administrator'] );

			$users = get_users();

			$granted_roles = get_option( self::DASHBOARD_ROLES );
			$granted_users = get_option( self::DASHBOARD_USERS );

			$hide_default_roles = get_option( self::DASHBOARD_ROLES_HIDE_DEFAULT );
			$hide_default_users = get_option( self::DASHBOARD_USERS_HIDE_DEFAULT );

			$create_roles = get_option( self::DASHBOARD_ROLES_CREATE );
			$create_users = get_option( self::DASHBOARD_USERS_CREATE );
			?>
			<form id="wpda_settings_dashboard" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=dashboard">
				<table class="wpda-table-settings">
					<tr>
						<th><?php echo __( 'Dashboard role access', 'wp-data-access' ); ?></th>
						<td>
							<div class="wpda_separator_bottom">
								Select roles to grant dashboard access:
							</div>
							<select name="dashboard_roles[]" multiple size="<?php echo esc_attr( min( 5, sizeof( $roles ) ) ); ?>">
								<?php
								foreach ( $roles as $key => $role ) {
									$selected = false !== strpos( $granted_roles, $key ) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>>
										<?php echo esc_attr( $role['name'] ); ?>
									</option>
									<?php
								}
								?>
							</select>
							<ul>
								<li>Administrators have access by default</li>
								<li>Hold the control key to select multiple</li>
							</ul>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Dashboard user access', 'wp-data-access' ); ?></th>
						<td>
							<div class="wpda_separator_bottom">
								Select users to grant dashboard access:
							</div>
							<select name="dashboard_users[]" multiple size="<?php echo esc_attr( min( 5, sizeof( $users ) ) ); ?>">
								<?php
								foreach ( $users as $user ) {
									$selected = false !== strpos( $granted_users, $user->data->user_login ) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $user->data->user_login ); ?>" <?php echo esc_attr( $selected ); ?>>
										<?php echo esc_attr( $user->data->display_name ); ?>
									</option>
									<?php
								}
								?>
							</select>
							<ul>
								<li>No role access needed to grant access to specific users</li>
								<li>Hold the control key to select multiple</li>
							</ul>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Hide default tab', 'wp-data-access' ); ?></th>
						<td>
							<div class="wpda_separator_bottom">
								Hide default tab for the following roles:
							</div>
							<select name="hide_default_roles[]" multiple size="<?php echo esc_attr( min( 5, sizeof( $roles ) ) ); ?>">
								<?php
								foreach ( $roles as $key => $role ) {
									$selected = false !== strpos( $hide_default_roles, $key ) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>>
										<?php echo esc_attr( $role['name'] ); ?>
									</option>
									<?php
								}
								?>
							</select>
							<div class="wpda_separator_top wpda_separator_bottom">
								Hide default tab for the following users:
							</div>
							<select name="hide_default_users[]" multiple size="<?php echo esc_attr( min( 5, sizeof( $users ) ) ); ?>">
								<?php
								foreach ( $users as $user ) {
									$selected = false !== strpos( $hide_default_users, $user->data->user_login ) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $user->data->user_login ); ?>" <?php echo esc_attr( $selected ); ?>>
										<?php echo esc_attr( $user->data->display_name ); ?>
									</option>
									<?php
								}
								?>
							</select>
							<ul>
								<li>Widgets currently added the users default tab will not be delete (user can readd them to another tab)</li>
							</ul>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Create dashboard', 'wp-data-access' ); ?></th>
						<td>
							<div class="wpda_separator_bottom">
								All dashboard users are allowed to create new dashboards by default.
								Select only roles and users NOT allowed to create dashboards.
							</div>
							<div class="wpda_separator_bottom">
								The following roles are NOT allowed to create dashboards:
							</div>
							<select name="create_roles[]" multiple size="<?php echo esc_attr( min( 5, sizeof( $roles ) ) ); ?>">
								<?php
								foreach ( $roles as $key => $role ) {
									$selected = false !== strpos( $create_roles, $key ) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>>
										<?php echo esc_attr( $role['name'] ); ?>
									</option>
									<?php
								}
								?>
							</select>
							<div class="wpda_separator_top wpda_separator_bottom">
								The following users are NOT allowed to create dashboards:
							</div>
							<select name="create_users[]" multiple size="<?php echo esc_attr( min( 5, sizeof( $users ) ) ); ?>">
								<?php
								foreach ( $users as $user ) {
									$selected = false !== strpos( $create_users, $user->data->user_login ) ? 'selected' : '';
									?>
									<option value="<?php echo esc_attr( $user->data->user_login ); ?>" <?php echo esc_attr( $selected ); ?>>
										<?php echo esc_attr( $user->data->display_name ); ?>
									</option>
									<?php
								}
								?>
							</select>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Dashboard Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
						   jQuery('input[name=&quot;action&quot;]').val('setdefaults');
						   jQuery('#wpda_settings_dashboard').trigger('submit')
						   }"
					   class="button">
						<?php echo __( 'Reset Dashboard Settings To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-dashboard-settings-' . WPDA::get_current_user_login(), '_wpnonce', false ); ?>
			</form>
			<style>
				#wpda_settings_dashboard ul {
					list-style-type: disc;
					margin-left: 20px;
					margin-bottom: 0;
				}
				#wpda_settings_dashboard ul li {
					margin-bottom: 0;
				}
			</style>
			<?php
		}

		private function update( $key, $option ) {
			if ( isset( $_REQUEST[ $key ] ) ) {
				$request = isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				if ( is_array( $request ) ) {
					$value = sanitize_text_field( wp_unslash( implode( ',', $request ) ) );
				} else {
					$value = '';
				}
			} else {
				$value = '';
			}

			update_option( $option, $value );
		}

	}

}
