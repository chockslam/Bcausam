<?php

namespace WPDataAccess\Settings {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	class WPDA_Settings_BackEnd extends WPDA_Settings {

		const WPNONCE_BACKEND_SETTING = 'WPDA_BACKEND_SETTINGS';

		/**
		 * Add back-end tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   1.0.0
		 */
		protected function add_content() {
			global $wpdb;

			if ( isset( $_REQUEST['database'] ) ) {
				$database = sanitize_text_field( wp_unslash( $_REQUEST['database'] ) ); // input var okay.
			} else {
				$database = $wpdb->dbname;
			}
			$is_wp_database = $database === $wpdb->dbname;

			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-back-end-settings' ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {
					// Save options.
					if ( $is_wp_database ) {
						WPDA::set_option(
							WPDA::OPTION_BE_TABLE_ACCESS,
							isset( $_REQUEST['table_access'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['table_access'] ) ) : null // input var okay.
						);
					} else {
						update_option(
							WPDA::BACKEND_OPTIONNAME_DATABASE_ACCESS . $database,
							isset( $_REQUEST['table_access'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['table_access'] ) ) : null // input var okay.
						);}

					$wpda_hide_manage_link = array();
					if ( isset( $_REQUEST['wpda_hide_manage_link'] ) ) {
						foreach ( $_REQUEST['wpda_hide_manage_link'] as $userid ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
							if ( is_numeric( $userid ) ) {
								$wpda_hide_manage_link[] = sanitize_text_field( wp_unslash( $userid ) );
							}
						}
					}
					update_option( 'wpda_hide_manage_link', $wpda_hide_manage_link );

					$table_access_selected_new_value = isset( $_REQUEST['table_access_selected'] ) ?
						WPDA::sanitize_text_field_array( $_REQUEST['table_access_selected'] ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					if ( is_array( $table_access_selected_new_value ) ) {
						// Check the requested table names for sql injection. This is simply done by checking if the table
						// name exists in our WordPress database.
						$table_access_selected_new_value_checked = array();
						foreach ( $table_access_selected_new_value as $key => $value ) {
							$wpda_dictionary_checks = new WPDA_Dictionary_Exist( $database, $value );
							if ( $wpda_dictionary_checks->table_exists( false ) ) {
								// Add existing table to list.
								$table_access_selected_new_value_checked[ $key ] = $value;
							} else {
								// An invalid table name was provided. Might be an sql injection attack or an invalid state.
								wp_die( __( 'ERROR: Invalid table name', 'wp-data-access' ) );
							}
						}
					} else {
						$table_access_selected_new_value_checked = '';
					}
					if ( $is_wp_database ) {
						WPDA::set_option(
							WPDA::OPTION_BE_TABLE_ACCESS_SELECTED,
							$table_access_selected_new_value_checked
						);
					} else {
						update_option(
							WPDA::BACKEND_OPTIONNAME_DATABASE_SELECTED . $database,
							$table_access_selected_new_value_checked
						);
					}

					WPDA::set_option(
						WPDA::OPTION_BE_VIEW_LINK,
						isset( $_REQUEST['view_link'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['view_link'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_ALLOW_INSERT,
						isset( $_REQUEST['allow_insert'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['allow_insert'] ) ) : 'off' // input var okay.
					);
					WPDA::set_option(
						WPDA::OPTION_BE_ALLOW_UPDATE,
						isset( $_REQUEST['allow_update'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['allow_update'] ) ) : 'off' // input var okay.
					);
					WPDA::set_option(
						WPDA::OPTION_BE_ALLOW_DELETE,
						isset( $_REQUEST['allow_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['allow_delete'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_EXPORT_ROWS,
						isset( $_REQUEST['export_rows'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['export_rows'] ) ) : 'off' // input var okay.
					);
					WPDA::set_option(
						WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX,
						isset( $_REQUEST['export_variable_rows'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['export_variable_rows'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_ALLOW_IMPORTS,
						isset( $_REQUEST['allow_imports'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['allow_imports'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_CONFIRM_EXPORT,
						isset( $_REQUEST['confirm_export'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['confirm_export'] ) ) : 'off' // input var okay.
					);
					WPDA::set_option(
						WPDA::OPTION_BE_CONFIRM_VIEW,
						isset( $_REQUEST['confirm_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['confirm_view'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_PAGINATION,
						isset( $_REQUEST['pagination'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['pagination'] ) ) : null // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_REMEMBER_SEARCH,
						isset( $_REQUEST['remember_search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['remember_search'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_INNODB_COUNT,
						isset( $_REQUEST['innodb_count'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['innodb_count'] ) ) : 100000 // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_DESIGN_MODE,
						isset( $_REQUEST['design_mode'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['design_mode'] ) ) : 'basic' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_TEXT_WRAP_SWITCH,
						isset( $_REQUEST['text_wrap_switch'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['text_wrap_switch'] ) ) : 'off' // input var okay.
					);

					WPDA::set_option(
						WPDA::OPTION_BE_TEXT_WRAP,
						isset( $_REQUEST['text_wrap'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['text_wrap'] ) ) : 400 // input var okay.
					);

					if (
						isset( $_REQUEST['wpda_default_user'] ) &&
						isset( $_REQUEST['wpda_default_database'] ) &&
						'' !== $_REQUEST['wpda_default_user'] &&
						'' !== $_REQUEST['wpda_default_database']
					) {
						$default_databases = get_option( 'wpda_default_database' );
						if ( false === $default_databases ) {
							$default_databases = array();
						}
						$wpda_default_user     = sanitize_text_field( wp_unslash( $_REQUEST['wpda_default_user'] ) ); // input var okay.
						$wpda_default_database = sanitize_text_field( wp_unslash( $_REQUEST['wpda_default_database'] ) ); // input var okay.

						$default_databases[ $wpda_default_user ] = $wpda_default_database;
						update_option( 'wpda_default_database', $default_databases );
					}

					WPDA::set_option(
						WPDA::OPTION_BE_HIDE_BUTTON_ICONS,
						isset( $_REQUEST['hide_button_icons'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hide_button_icons'] ) ) : 'off' // input var okay.
					);
				} elseif ( 'setdefaults' === $action ) {
					// Set all back-end settings back to default.
					if ( $is_wp_database ) {
						WPDA::set_option( WPDA::OPTION_BE_TABLE_ACCESS );
						WPDA::set_option( WPDA::OPTION_BE_TABLE_ACCESS_SELECTED );
					} else {
						update_option(
							WPDA::BACKEND_OPTIONNAME_DATABASE_ACCESS . $database,
							'show'
						);
						update_option(
							WPDA::BACKEND_OPTIONNAME_DATABASE_SELECTED . $database,
							''
						);
					}
					update_option( 'wpda_hide_manage_link', array() );
					WPDA::set_option( WPDA::OPTION_BE_VIEW_LINK );
					WPDA::set_option( WPDA::OPTION_BE_ALLOW_INSERT );
					WPDA::set_option( WPDA::OPTION_BE_ALLOW_UPDATE );
					WPDA::set_option( WPDA::OPTION_BE_ALLOW_DELETE );
					WPDA::set_option( WPDA::OPTION_BE_EXPORT_ROWS );
					WPDA::set_option( WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX );
					WPDA::set_option( WPDA::OPTION_BE_ALLOW_IMPORTS );
					WPDA::set_option( WPDA::OPTION_BE_CONFIRM_EXPORT );
					WPDA::set_option( WPDA::OPTION_BE_CONFIRM_VIEW );
					WPDA::set_option( WPDA::OPTION_BE_PAGINATION );
					WPDA::set_option( WPDA::OPTION_BE_REMEMBER_SEARCH );
					WPDA::set_option( WPDA::OPTION_BE_INNODB_COUNT );
					WPDA::set_option( WPDA::OPTION_BE_DESIGN_MODE );
					WPDA::set_option( WPDA::OPTION_BE_TEXT_WRAP_SWITCH );
					WPDA::set_option( WPDA::OPTION_BE_TEXT_WRAP );
					update_option( 'wpda_default_database', array() );
					WPDA::set_option( WPDA::OPTION_BE_HIDE_BUTTON_ICONS );
				} elseif ( 'delete_default_user_database' === $action ) {
					if ( isset( $_REQUEST['wpda_default_database_delete'] ) ) {
						$delete_user_id = sanitize_text_field( wp_unslash( $_REQUEST['wpda_default_database_delete'] ) ); // input var okay.

						$default_databases = get_option( 'wpda_default_database' );
						if ( false !== $default_databases && isset( $default_databases[ $delete_user_id ] ) ) {
							unset( $default_databases[ $delete_user_id ] );
							update_option( 'wpda_default_database', $default_databases );
						}
					}
				}

				$msg = new WPDA_Message_Box(
					array(
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					)
				);
				$msg->box();
			}

			// Get options.
			if ( $is_wp_database ) {
				$table_access          = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS );
				$table_access_selected = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS_SELECTED );
			} else {
				$table_access = get_option( WPDA::BACKEND_OPTIONNAME_DATABASE_ACCESS . $database );
				if ( false === $table_access ) {
					$table_access = 'show';
				}
				$table_access_selected = get_option( WPDA::BACKEND_OPTIONNAME_DATABASE_SELECTED . $database );
				if ( false === $table_access_selected ) {
					$table_access_selected = '';
				}
			}

			if ( is_array( $table_access_selected ) ) {
				// Convert table for simple access.
				$table_access_selected_by_name = array();
				foreach ( $table_access_selected as $key => $value ) {
					$table_access_selected_by_name[ $value ] = true;
				}
			}

			$wpda_hide_manage_link = get_option( 'wpda_hide_manage_link' );
			if ( is_array( $wpda_hide_manage_link ) ) {
				$wpda_hide_manage_list = array_flip( $wpda_hide_manage_link );
			} else {
				$wpda_hide_manage_list = array();
			}

			$view_link = WPDA::get_option( WPDA::OPTION_BE_VIEW_LINK );

			$allow_insert = WPDA::get_option( WPDA::OPTION_BE_ALLOW_INSERT );
			$allow_update = WPDA::get_option( WPDA::OPTION_BE_ALLOW_UPDATE );
			$allow_delete = WPDA::get_option( WPDA::OPTION_BE_ALLOW_DELETE );

			$export_rows          = WPDA::get_option( WPDA::OPTION_BE_EXPORT_ROWS );
			$export_variable_rows = WPDA::get_option( WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX );

			$allow_imports = WPDA::get_option( WPDA::OPTION_BE_ALLOW_IMPORTS );

			$confirm_export = WPDA::get_option( WPDA::OPTION_BE_CONFIRM_EXPORT );
			$confirm_view   = WPDA::get_option( WPDA::OPTION_BE_CONFIRM_VIEW );

			$pagination = WPDA::get_option( WPDA::OPTION_BE_PAGINATION );

			$remember_search = WPDA::get_option( WPDA::OPTION_BE_REMEMBER_SEARCH );

			$innodb_count = WPDA::get_option( WPDA::OPTION_BE_INNODB_COUNT );

			$design_mode = WPDA::get_option( WPDA::OPTION_BE_DESIGN_MODE );

			$text_wrap_switch = WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP_SWITCH );
			$text_wrap        = WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP );

			$hide_button_icons = WPDA::get_option( WPDA::OPTION_BE_HIDE_BUTTON_ICONS );
			?>
			<form id="wpda_settings_backend" method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=backend">
				<table class="wpda-table-settings">
					<tr>
						<th><?php echo __( 'Table access', 'wp-data-access' ); ?></th>
						<td>
							<select name="database" id="schema_name">
								<?php
								$schema_names = WPDA_Dictionary_Lists::get_db_schemas();
								foreach ( $schema_names as $schema_name ) {
									$selected = $database === $schema_name['schema_name'] ? ' selected' : '';
									echo "<option value='{$schema_name['schema_name']}'$selected>{$schema_name['schema_name']}</option>"; // phpcs:ignore WordPress.Security.EscapeOutput
								}
								?>
							</select>
							<br/><br/>
							<label>
								<input
									type="radio"
									name="table_access"
									value="show"
									<?php echo 'show' === $table_access ? 'checked' : ''; ?>
								><?php echo $is_wp_database ? __( 'Show WordPress tables', 'wp-data-access' ) : __( 'Show all tables', 'wp-data-access' ); ?>
							</label>
							<br/>
							<?php
							if ( $is_wp_database ) {
								?>
								<label>
									<input
										type="radio"
										name="table_access"
										value="hide"
										<?php echo 'hide' === $table_access ? 'checked' : ''; ?>
									><?php echo __( 'Hide WordPress tables', 'wp-data-access' ); ?>
								</label>
								<br/>
								<?php
							}
							?>
							<label>
								<input
									type="radio"
									name="table_access"
									value="select"
									<?php echo 'select' === $table_access ? 'checked' : ''; ?>
								><?php echo __( 'Show only selected tables', 'wp-data-access' ); ?>
							</label>
							<div id="tables_selected" <?php echo 'select' === $table_access ? '' : 'style="display:none"'; ?>>
								<br/>
								<select name="table_access_selected[]" multiple size="10">
									<?php
									$tables = WPDA_Dictionary_Lists::get_tables( true, $database );
									foreach ( $tables as $table ) {
										$table_name = $table['table_name'];
										?>
										<option value="<?php echo esc_attr( $table_name ); ?>" <?php echo isset( $table_access_selected_by_name[ $table_name ] ) ? 'selected' : ''; ?>><?php echo esc_attr( $table_name ); ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<script type='text/javascript'>
								jQuery(function () {
									jQuery("input[name='table_access']").on("click", function () {
										if (this.value == 'select') {
											jQuery("#tables_selected").show();
										} else {
											jQuery("#tables_selected").hide();
										}
									});
									jQuery('#schema_name').on('change', function() {
										window.location = '?page=<?php echo esc_attr( $this->page ); ?>&tab=backend&database=' + jQuery(this).val();
									});
								});
							</script>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Restrict table management', 'wp-data-access' ); ?></th>
						<td>
							<select name="wpda_hide_manage_link[]" multiple="true">
								<?php
								foreach ( get_users( array( 'role' => 'administrator' ) ) as $user ) {
									$selected = isset( $wpda_hide_manage_list[ $user->ID ] ) ? 'selected' : '';
									echo "<option value='{$user->ID}' {$selected}>{$user->user_login} ({$user->user_email})</option>"; // phpcs:ignore WordPress.Security.EscapeOutput
								}
								?>
							</select>
							<div style="margin-top:5px;margin-left:-5px">
								<span class="dashicons dashicons-yes"></span>
								Removes the manage link in the Data Explorer for selected admin users
							</div>
							<div style="margin-left:-5px">
								<span class="dashicons dashicons-yes"></span>
								Hold control key to deselect or select multiple users
							</div>
							<div style="margin-left:-5px">
								<span class="dashicons dashicons-yes"></span>
								Non admin users have no access by default
							</div>
							<div style="margin-left:-5px">
								<span class="dashicons dashicons-yes"></span>
								Every administrator can change this option
							</div>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Row access', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
									type="checkbox"
									name="view_link"
									<?php echo 'on' === $view_link ? 'checked' : ''; ?>
								><?php echo __( 'Add view link to list table', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo __( 'Allow transactions?', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="allow_insert"
									<?php echo 'on' === $allow_insert ? 'checked' : ''; ?> /><?php echo __( 'Allow insert', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="allow_update"
									<?php echo 'on' === $allow_update ? 'checked' : ''; ?> /><?php echo __( 'Allow update', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="allow_delete"
									<?php echo 'on' === $allow_delete ? 'checked' : ''; ?> /><?php echo __( 'Allow delete', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo __( 'Allow exports?', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="export_rows"
									<?php echo 'on' === $export_rows ? 'checked' : ''; ?> /><?php echo __( 'Allow row export', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="export_variable_rows"
									<?php echo 'on' === $export_variable_rows ? 'checked' : ''; ?> /><?php echo __( 'Export with variable WP prefix', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo __( 'Allow imports?', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="allow_imports"
									<?php echo 'on' === $allow_imports ? 'checked' : ''; ?> /><?php echo __( 'Allow to import scripts from Data Explorer table pages', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Ask for confirmation?', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="confirm_export"
									<?php echo 'on' === $confirm_export ? 'checked' : ''; ?> /><?php echo __( 'When starting export', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label>
								<input type="checkbox" name="confirm_view"
									<?php echo 'on' === $confirm_view ? 'checked' : ''; ?> /><?php echo __( 'When viewing non WPDA table', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Default pagination value', 'wp-data-access' ); ?></th>
						<td>
							<input
								type="number" step="1" min="1" max="999" name="pagination" maxlength="3"
								value="<?php echo esc_attr( $pagination ); ?>">
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Search box', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
									type="checkbox"
									name="remember_search" <?php echo 'on' === $remember_search ? 'checked' : ''; ?>
								><?php echo __( 'Remember last search', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Max row count', 'wp-data-access' ); ?></th>
						<td>
							<input
								type="number" step="1" min="1" max="999999" name="innodb_count" maxlength="3"
								value="<?php echo esc_attr( $innodb_count ); ?>">
							<p>
								<strong>This works for InnoDB tables and views only!</strong>
							</p>
							<p>
								The real row count is shown for other table types.
							</p>
							<p>
								<strong>BEHAVIOUR</strong><br/>
								IF estimated row count > max row count:<br/>
								&nbsp;&nbsp;&nbsp;&nbsp;use estimated row count<br/>
								ELSE<br/>
								&nbsp;&nbsp;&nbsp;&nbsp;user real row count
							</p>
							<p>
								Showing the estimated row count instead of the real row count <strong>improves performance</strong>
								for <strong>large tables and views</strong>.
								An estimated row count is <strong>less accurate</strong> than a real row count.
							</p>
							<p>
								This option can be changed for InnoDB tables and views in the Data Explorer:<br/>
								WP Data Access > Data Explorer > YOUR TABLE > Manage > Settings > Table Settings > Row count
							</p>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Default designer mode', 'wp-data-access' ); ?></th>
						<td>
							<select name="design_mode">
								<option value="basic" <?php echo 'basic' === $design_mode ? 'selected' : ''; ?>>Basic
								</option>
								<option value="advanced" <?php echo 'advanced' === $design_mode ? 'selected' : ''; ?>>
									Advanced
								</option>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Content wrap', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
									type="checkbox"
									name="text_wrap_switch" <?php echo 'on' === $text_wrap_switch ? 'checked' : ''; ?>
								><?php echo __( 'No content wrap', 'wp-data-access' ); ?>
							</label>
							<br/>
							<input
								type="number" step="1" min="1" max="999999" name="text_wrap" maxlength="3"
								value="<?php echo esc_attr( $text_wrap ); ?>">
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Default database', 'wp-data-access' ); ?></th>
						<td>
							<div>
								<?php
								$users = array();
								foreach ( get_users() as $user ) {
									$users[ $user->data->ID ] = $user->data->user_login;
								}

								$databases    = array();
								$db_databases = WPDA_Dictionary_Lists::get_db_schemas();
								foreach ( $db_databases as $db_database ) {
									$databases[ $db_database['schema_name'] ] = true;
								}

								$default_databases = get_option( 'wpda_default_database' );
								if ( false === $default_databases ) {
									$default_databases = array();
								}
								if ( is_array( $default_databases ) ) {
									foreach ( $default_databases as $user_id => $database ) {
										?>
										<div id="wpda_default_database_<?php echo esc_attr( $user_id ); ?>">
											<span class="dashicons dashicons-trash"
												  style="font-size: 14px; vertical-align: text-top; cursor: pointer;"
												  onclick="if (confirm('Remove default database for this user?')) { jQuery('#wpda_default_database_delete').val('<?php echo esc_attr( $user_id ); ?>'); jQuery('#delete_default_user_database_form').submit(); } "
											></span>
											<span>
												<?php echo esc_attr( $users[ $user_id ] ); ?> > <?php echo esc_attr( $database ); ?>
											</span>
										</div>
										<?php
									}
								}
								?>
							</div>
							<?php
							if ( sizeof( $default_databases ) > 0 ) {
								echo '<br/>';
							}
							?>
							<div>
								<a href="javascript:void(0)" onclick="jQuery('#list_default_databases').show()" class="button">Define default database for user in Data Explorer</a>
							</div>
							<div id="list_default_databases" style="display:none">
								<br/>
								<div>
									<label for="wpda_default_user">User: </label>
									<select name="wpda_default_user" id="wpda_default_user">
										<option value="">Select user</option>
										<?php
										foreach ( get_users() as $user ) {
											echo '<option value="' . esc_attr( $user->data->ID ) . '">' . esc_attr( $user->data->user_login ) . '</option>';
										}
										?>
									</select>
									<label for="wpda_default_database">Database: </label>
									<select name="wpda_default_database" id="wpda_default_database">
										<option value="">Select database</option>
										<?php
										foreach ( $databases as $database => $value ) {
											echo '<option value="' . esc_attr( $database ) . '">' . esc_attr( $database ) . '</option>';
										}
										?>
									</select>
									<span class="dashicons dashicons-trash"
										  style="font-size: 14px; vertical-align: text-top; cursor: pointer;"
										  onclick="jQuery('#list_default_databases').hide(); jQuery('#wpda_default_user').val(''); jQuery('#wpda_default_database').val('');"
									></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th><?php echo __( 'Hide button icons', 'wp-data-access' ); ?></th>
						<td>
							<label>
								<input
										type="checkbox"
										name="hide_button_icons" <?php echo 'on' === $hide_button_icons ? 'checked' : ''; ?>
								><?php echo __( 'Hide icons on admin buttons', 'wp-data-access' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit" value="<?php echo __( 'Save Back-end Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
						   jQuery('input[name=&quot;action&quot;]').val('setdefaults');
						   jQuery('#wpda_settings_backend').trigger('submit')
						   }"
					   class="button">
						<?php echo __( 'Reset Back-end Settings To Defaults', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-back-end-settings', '_wpnonce', false ); ?>
			</form>
			<form id="delete_default_user_database_form"
				  method="post"
				  action="?page=<?php echo esc_attr( $this->page ); ?>&tab=backend"
				  style="display:none">
				<input type="hidden" name="wpda_default_database_delete" id="wpda_default_database_delete" value=""/>
				<input type="hidden" name="action" value="delete_default_user_database"/>
				<?php wp_nonce_field( 'wpda-back-end-settings', '_wpnonce', false ); ?>
			</form>
			<?php
		}

	}

}
