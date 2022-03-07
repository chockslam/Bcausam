<?php

namespace WPDataAccess\Settings {

	use WPDataAccess\Plugin_Table_Models\WPDA_CSV_Uploads_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Logging_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Media_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_User_Menus_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Page_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Model;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\Utilities\WPDA_Repository;
	use WPDataAccess\Utilities\WPDA_Restore_Repository;
	use WPDataAccess\WPDA;

	class WPDA_Settings_ManageRepository extends WPDA_Settings {

		public function __construct( $current_tab, $help_url ) {
			parent::__construct( $current_tab, $help_url );

			// Recreation of repository must be performed before checking the availability of menu items (done next).
			if ( isset( $_REQUEST['repos'] ) && 'true' === sanitize_text_field( wp_unslash( $_REQUEST['repos'] ) ) ) {
				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-settings-recreate-repository-' . WPDA::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				// Recreate repository.
				$wpda_repository = new WPDA_Repository();
				$wpda_repository->recreate();
				WPDA::set_option( WPDA::OPTION_WPDA_SETUP_ERROR ); // Set to default.
				WPDA::set_option( WPDA::OPTION_WPDA_SHOW_WHATS_NEW ); // Set to default.

				$msg = new WPDA_Message_Box(
					array(
						'message_text' => __( 'Repository recreation completed', 'wp-data-access' ),
					)
				);
				$msg->box();
			}
		}

		/**
		 * Counts the number of rows in a backup table. The table is queried from the MySQL meta data and cannot be
		 * entered by a user. No SQL injection checks necessary.
		 */
		protected function count_repository_backup_table( $table_name ) {
			global $wpdb;

			$suppress = $wpdb->suppress_errors( true );
			$wpdb->get_results(
				$wpdb->prepare(
					'select 1 from `%1s`', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
					array(
						WPDA::remove_backticks( $table_name ),
					)
				)
			);
			$wpdb->suppress_errors( $suppress );

			return $wpdb->num_rows;
		}

		/**
		 * Add repository tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   1.0.0
		 */
		protected function add_content() {

			global $wpdb;

			if ( isset( $_REQUEST['action'] ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.

				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-repository-settings-' . WPDA::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( 'save' === $action ) {
					// Save changes.
					WPDA::set_option(
						WPDA::OPTION_MR_KEEP_BACKUP_TABLES,
						isset( $_REQUEST['keep_backup_tables'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['keep_backup_tables'] ) ) :
							'off' // input var okay.
					);
					WPDA::set_option(
						WPDA::OPTION_MR_BACKUP_TABLES_KEPT,
						isset( $_REQUEST['backup_tables_kept'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['backup_tables_kept'] ) ) :
							WPDA::OPTION_MR_BACKUP_TABLES_KEPT_DEFAULT // input var okay.
					);
				} elseif ( 'setdefaults' === $action ) {
					// Set back to default values.
					WPDA::set_option( WPDA::OPTION_MR_KEEP_BACKUP_TABLES );
					WPDA::set_option( WPDA::OPTION_MR_BACKUP_TABLES_KEPT );
				}

				$msg = new WPDA_Message_Box(
					array(
						'message_text' => __( 'Settings saved', 'wp-data-access' ),
					)
				);
				$msg->box();

			}

			$keep_backup_tables = WPDA::get_option( WPDA::OPTION_MR_KEEP_BACKUP_TABLES );
			$backup_tables_kept = WPDA::get_option( WPDA::OPTION_MR_BACKUP_TABLES_KEPT );

			// Check table wp_wpda_menus.
			$menus_table_name        = WPDA_User_Menus_Model::get_base_table_name();
			$menus_table_name_exists = WPDA_User_Menus_Model::table_exists();

			// Check table wp_wpda_table_design.
			$design_table_name        = WPDA_Design_Table_Model::get_base_table_name();
			$design_table_name_exists = WPDA_Design_Table_Model::table_exists();

			// Check table wp_wpda_logging
			$logging_table_name   = WPDA_Logging_Model::get_base_table_name();
			$logging_table_exists = WPDA_Logging_Model::table_exists();

			// Check table wp_wpda_media
			$media_table_name   = WPDA_Media_Model::get_base_table_name();
			$media_table_exists = WPDA_Media_Model::table_exists();

			// Check data projects tables
			$data_projects_project_name        = WPDP_Project_Model::get_base_table_name();
			$data_projects_project_name_exists = WPDP_Project_Model::table_exists();

			// Check data project page table.
			$data_projects_page_name        = WPDP_Page_Model::get_base_table_name();
			$data_projects_page_name_exists = WPDP_Page_Model::table_exists();

			// Check data project tables table
			$data_projects_table_name        = WPDP_Project_Design_Table_Model::get_base_table_name();
			$data_projects_table_name_exists = WPDP_Project_Design_Table_Model::table_exists();

			// Check data publisher table.
			$data_publication_table_name        = WPDA_Publisher_Model::get_base_table_name();
			$data_publication_table_name_exists = WPDA_Publisher_Model::table_exists();

			// Check table settings tablewp_wpda_project_
			$table_settings_table_name   = WPDA_Table_Settings_Model::get_base_table_name();
			$table_settings_table_exists = WPDA_Table_Settings_Model::table_exists();

			// Check CSV import table
			$csv_import_table_name   = WPDA_CSV_Uploads_Model::get_base_table_name();
			$csv_import_table_exists = WPDA_CSV_Uploads_Model::table_exists();

			$table       = __( 'Table', 'wp-data-access' );
			$found       = __( 'found', 'wp-data-access' );
			$not_found   = __( 'not found', 'wp-data-access' );
			$bck_postfix = WPDA_Restore_Repository::BACKUP_TABLE_EXTENSION;
			$query       = '
				select table_name AS table_name from information_schema.tables
				where table_schema = %s
				  and (
				           table_name like %s
				  		or table_name like %s
				  		or table_name like %s
				  		or table_name like %s
				  		or table_name like %s
				  		or table_name like %s
				  		or table_name like %s
				  		or table_name like %s
				  		or table_name like %s
				  		or table_name like %s
				  )
			    order by 1 desc';

			if ( isset( $_REQUEST['create_backup'] ) && 'true' === $_REQUEST['create_backup'] ) {
				// Security check
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-settings-create_backup-repository-' . WPDA::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				$wpda_repository = new WPDA_Repository();
				$wpda_repository->create_new_backup();
			} elseif ( isset( $_REQUEST['remove_one_backup'] ) && 'true' === $_REQUEST['remove_one_backup'] ) {
				// Security check
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-settings-remove_backup-repository-' . WPDA::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( isset( $_REQUEST['backup_date'] ) ) {
					$backup_date = sanitize_text_field( wp_unslash( $_REQUEST['backup_date'] ) ); // input var okay.

					// Remove specific repository backup set
					$repository = new WPDA_Repository();
					$repository->remove_backup( $backup_date );

					$msg = new WPDA_Message_Box(
						array(
							'message_text' => __( 'Repository backup tables dropped', 'wp-data-access' ),
						)
					);
					$msg->box();
				}
			} elseif ( isset( $_REQUEST['remove_backup'] ) && 'true' === $_REQUEST['remove_backup'] ) {
				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-settings-remove_backup-repository-' . WPDA::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				// Remove all repository backups
				$backup_tables = $wpdb->get_results(
					$wpdb->prepare(
						$query, // phpcs:ignore WordPress.DB.PreparedSQL
						array(
							$wpdb->dbname,
							"$menus_table_name{$bck_postfix}%",
							"$design_table_name{$bck_postfix}%",
							"$table_settings_table_name{$bck_postfix}%",
							"$logging_table_name{$bck_postfix}%",
							"$media_table_name{$bck_postfix}%",
							"$data_publication_table_name{$bck_postfix}%",
							"$csv_import_table_name{$bck_postfix}%",
							"$data_projects_project_name{$bck_postfix}%",
							"$data_projects_page_name{$bck_postfix}%",
							"$data_projects_table_name{$bck_postfix}%",
						)
					),
					'ARRAY_N'
				);
				foreach ( $backup_tables as $backup_table ) {
					$wpdb->query(
						$wpdb->prepare(
							'drop table `%1s`', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
							array(
								WPDA::remove_backticks( $backup_table[0] ),
							)
						)
					);
				}

				$msg = new WPDA_Message_Box(
					array(
						'message_text' => __( 'Repository backup tables dropped', 'wp-data-access' ),
					)
				);
				$msg->box();
			} elseif ( isset( $_REQUEST['restore_backup'] ) ) {
				if ( isset( $_REQUEST['restore_date'] ) ) {
					$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
					if ( ! wp_verify_nonce( $wp_nonce, 'wpda-settings-restore_backup-repository-' . WPDA::get_current_user_login() ) ) {
						wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
					}

					// Start repository restore procedure
					$restore_date      = sanitize_text_field( wp_unslash( $_REQUEST['restore_date'] ) ); // input var okay.
					$restore           = new WPDA_Restore_Repository();
					$repository_tables = $restore->restore( $restore_date );
				}
			}

			// Count backup tables rows
			if ( $menus_table_name_exists ) {
				$no_menu_items = WPDA_User_Menus_Model::count();
			} else {
				$no_menu_items = 0;
			}
			if ( $design_table_name_exists ) {
				$no_table_designs = WPDA_Design_Table_Model::count();
			} else {
				$no_table_designs = 0;
			}
			if ( $logging_table_exists ) {
				$no_logs = WPDA_Logging_Model::count();
			} else {
				$no_logs = 0;
			}
			if ( $media_table_exists ) {
				$no_media = WPDA_Media_Model::count();
			} else {
				$no_media = 0;
			}
			if ( $data_projects_project_name_exists ) {
				$no_projects = WPDP_Project_Model::count();
			} else {
				$no_projects = 0;
			}
			if ( $data_projects_page_name_exists ) {
				$no_pages = WPDP_Page_Model::count();
			} else {
				$no_pages = 0;
			}
			if ( $data_projects_table_name_exists ) {
				$no_project_table_designs = WPDP_Project_Design_Table_Model::count();
			} else {
				$no_project_table_designs = 0;
			}
			if ( $data_publication_table_name_exists ) {
				$no_data_publication = WPDA_Publisher_Model::count();
			} else {
				$no_data_publication = 0;
			}
			if ( $table_settings_table_exists ) {
				$no_table_settings = WPDA_Table_Settings_Model::count();
			} else {
				$no_table_settings = 0;
			}
			if ( $csv_import_table_exists ) {
				$no_csv_import = WPDA_CSV_Uploads_Model::count();
			} else {
				$no_csv_import = 0;
			}

			// Count backup tables
			$backup_tables    = $wpdb->get_results(
				$wpdb->prepare(
					$query, // phpcs:ignore WordPress.DB.PreparedSQL
					array(
						$wpdb->dbname,
						"$menus_table_name{$bck_postfix}%",
						"$design_table_name{$bck_postfix}%",
						"$table_settings_table_name{$bck_postfix}%",
						"$logging_table_name{$bck_postfix}%",
						"$media_table_name{$bck_postfix}%",
						"$data_publication_table_name{$bck_postfix}%",
						"$csv_import_table_name{$bck_postfix}%",
						"$data_projects_project_name{$bck_postfix}%",
						"$data_projects_page_name{$bck_postfix}%",
						"$data_projects_table_name{$bck_postfix}%",
					)
				),
				'ARRAY_N'
			);
			$no_backup_tables = $wpdb->num_rows;

			$wpnonce_create_backup  = wp_create_nonce( 'wpda-settings-create_backup-repository-' . WPDA::get_current_user_login() );
			$wpnonce_remove_backup  = wp_create_nonce( 'wpda-settings-remove_backup-repository-' . WPDA::get_current_user_login() );
			$wpnonce_restore_backup = wp_create_nonce( 'wpda-settings-restore_backup-repository-' . WPDA::get_current_user_login() );

			if ( isset( $repository_tables ) ) {
				// Show restore results
				$output_restore_data = '';
				foreach ( $repository_tables as $key => $repository_table ) {
					$rows                 = false === $repository_table['rows'] ? '0' : $repository_table['rows'];
					$output_restore_data .= '
						<tr>
							<td>' . esc_attr( $key ) . '</td>
							<td>' . esc_attr( $rows ) . '</td>
							<td>' . esc_attr( $repository_table['errors'] ) . '</td>
						</tr>
					';
				}
				$output_restore = "
					<h3>Repository Restore Results</h3>
					<table class='wpda-repository-restore'>
						<thead>
							<tr>
								<th>Repository table name</th>
								<th>Rows imported</th>
								<th>Error messages</th>
							</tr>
						</thead>
						<tbody>
							{$output_restore_data}
						</tbody>
					</table>
				";
				$msgbox         = new WPDA_Message_Box(
					array(
						'message_text' => 'custom',
					)
				);
				$msgbox->custom_box( $output_restore );
			}

			$wpnonce_download_backup = wp_create_nonce( 'wpda-export-' . WPDA::get_current_user_login() );
			?>
			<form id="wpda-download-actual-respository"
				  method="post"
				  action="<?php echo admin_url( 'admin.php' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>?action=wpda_export"
				  target="_blank"
				  style="display: none"
			>
				<input type="hidden" name="type" value="table">
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $wpnonce_download_backup ); ?>">
				<input type="hidden" name="show_create" value="off">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $table_settings_table_name ); ?>">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $media_table_name ); ?>">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $design_table_name ); ?>">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $data_projects_project_name ); ?>">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $data_projects_page_name ); ?>">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $data_projects_table_name ); ?>">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $data_publication_table_name ); ?>">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $menus_table_name ); ?>">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $csv_import_table_name ); ?>">
				<input type="hidden" name="table_names[]" value="<?php echo esc_attr( $logging_table_name ); ?>">
				<input type="submit">
			</form>
			<form id="wpda-download-backup"
				  method="post"
				  action="<?php echo admin_url( 'admin.php' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>?action=wpda_export"
				  target="_blank"
				  style="display: none"
			>
				<input type="hidden" name="type" value="table">
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $wpnonce_download_backup ); ?>">
				<input type="hidden" name="show_create" value="off">
				<input type="hidden" name="table_names[]" id="table_settings_table_name">
				<input type="hidden" name="table_names[]" id="media_table_name">
				<input type="hidden" name="table_names[]" id="design_table_name">
				<input type="hidden" name="table_names[]" id="data_projects_project_name">
				<input type="hidden" name="table_names[]" id="data_projects_page_name">
				<input type="hidden" name="table_names[]" id="data_projects_table_name">
				<input type="hidden" name="table_names[]" id="data_publication_table_name">
				<input type="hidden" name="table_names[]" id="menus_table_name">
				<input type="hidden" name="table_names[]" id="csv_import_table_name">
				<input type="hidden" name="table_names[]" id="logging_table_name">
				<input type="submit">
			</form>
			<form id="wpda-remove-backup"
				  method="post"
				  action="<?php echo admin_url( 'admin.php' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>?page=<?php echo esc_attr( $this->page ); ?>&tab=repository"
				  style="display: none"
			>
				<input type="hidden" name="backup_date" id="remove_backup_date">
				<input type="hidden" name="remove_one_backup" value="true">
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $wpnonce_remove_backup ); ?>">
				<input type="submit">
			</form>
			<form id="wpda-create-backup"
				  method="post"
				  action="<?php echo admin_url( 'admin.php' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>?page=<?php echo esc_attr( $this->page ); ?>&tab=repository"
				  style="display: none"
			>
				<input type="hidden" name="create_backup" value="true">
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $wpnonce_create_backup ); ?>">
				<input type="submit">
			</form>
			<form id="wpda-remove-all-backups"
				  method="post"
				  action="<?php echo admin_url( 'admin.php' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>?page=<?php echo esc_attr( $this->page ); ?>&tab=repository"
				  style="display: none"
			>
				<input type="hidden" name="remove_backup" value="true">
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $wpnonce_remove_backup ); ?>">
				<input type="submit">
			</form>
			<form id="wpda-restore-respository"
				  method="post"
				  action="<?php echo admin_url( 'admin.php' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>?page=<?php echo esc_attr( $this->page ); ?>&tab=repository"
				  style="display: none"
			>
				<input type="hidden" name="wpda_table_settings" id="restore_table_settings_table_name">
				<input type="hidden" name="wpda_media" id="restore_media_table_name">
				<input type="hidden" name="wpda_table_design" id="restore_design_table_name">
				<input type="hidden" name="wpda_project" id="restore_data_projects_project_name">
				<input type="hidden" name="wpda_project_page" id="restore_data_projects_page_name">
				<input type="hidden" name="wpda_project_table" id="restore_data_projects_table_name">
				<input type="hidden" name="wpda_publisher" id="restore_data_publication_table_name">
				<input type="hidden" name="wpda_menus" id="restore_menus_table_name">
				<input type="hidden" name="wpda_csv_uploads" id="restore_csv_import_table_name">
				<input type="hidden" name="wpda_logging" id="restore_logging_table_name">
				<input type="hidden" name="restore_date" id="restore_date">
				<input type="hidden" name="restore_backup" value="true">
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $wpnonce_restore_backup ); ?>">
				<input type="submit">
			</form>
			<form id="wpda_settings_repository" method="post"
				  action="<?php echo admin_url( 'admin.php' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>?page=<?php echo esc_attr( $this->page ); ?>&tab=repository">
				<table class="wpda-table-settings">

					<tr>
						<th>
							<?php echo __( 'On Plugin Update', 'wp-data-access' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" name="keep_backup_tables"
									<?php echo 'on' === $keep_backup_tables ? 'checked' : ''; ?>>
								<strong><?php echo __( 'Keep backup of repository tables', 'wp-data-access' ); ?></strong>
								<?php echo __( '(creates backup tables on plugin updates)', 'wp-data-access' ); ?>
							</label>
							<br/>
							<label style="display: inline-block; margin-top: 5px">
								<strong>Max backups saved:</strong>&nbsp;
								<input type="number" name="backup_tables_kept" value="<?php echo esc_attr( $backup_tables_kept ); ?>" />
							</label>
							<br/><br/>
							<a href="javascript:void(0)"
							   class="button"
							   onclick="jQuery('#wpda-download-actual-respository').submit()"
							>
								<?php echo __( 'Download actual repository tables' ); ?>
							</a>
							<?php
							if ( $no_backup_tables > 0 ) {
								?>
								<table class="wpda-table-backup">
									<tr>
										<th style="vertical-align: bottom">Current repository backups</th>
										<th class="wpda-repository-column"></th>
										<th class="wpda-repository-column"></th>
										<th class="wpda-repository-column"></th>
										<th class="wpda-repository-column"><span>Table Settings</span></th>
										<th class="wpda-repository-column"><span>Media Columns</span></th>
										<th class="wpda-repository-column"><span>Data Designer</span></th>
										<th class="wpda-repository-column"><span>Data Projects</span></th>
										<th class="wpda-repository-column"><span>Project Pages</span></th>
										<th class="wpda-repository-column"><span>Project Templates</span></th>
										<th class="wpda-repository-column"><span>Data Publisher</span></th>
										<th class="wpda-repository-column"><span>Data Menus</span></th>
										<th class="wpda-repository-column"><span>CSV Uploads</span></th>
										<th class="wpda-repository-column"><span>Data Logging</span></th>
									</tr>
									<?php
									foreach ( $backup_tables as $backup_table ) {
										if (
											strtolower( "{$data_projects_page_name}{$bck_postfix}" ) ===
											strtolower( substr( $backup_table[0], 0, strlen( "{$data_projects_page_name}{$bck_postfix}" ) ) )
										) {
											$backup_date  = substr( $backup_table[0], 25 + strlen( $wpdb->prefix ) );
											$display_date =
												substr( $backup_date, 0, 4 ) . '-' .
												substr( $backup_date, 4, 2 ) . '-' .
												substr( $backup_date, 6, 2 ) . ' ' .
												substr( $backup_date, 8, 2 ) . ':' .
												substr( $backup_date, 10, 2 ) . ':' .
												substr( $backup_date, 12, 2 );
											?>
											<tr data-backup-date="<?php echo esc_attr( $backup_date ); ?>">
												<td>
												<span>
													Repository backup <?php echo esc_attr( $display_date ); ?>
												</span>
												</td>
												<td>
												<span class="dashicons dashicons-download wpda_tooltip"
													  title="Download backup: <?php echo esc_attr( $display_date ); ?>"
													  onclick="backup_respository_tables('<?php echo "{$table_settings_table_name}{$bck_postfix}{$backup_date}"; // phpcs:ignore WordPress.Security.EscapeOutput ?>', '<?php echo "{$media_table_name}{$bck_postfix}{$backup_date}"; // phpcs:ignore WordPress.Security.EscapeOutput ?>', '<?php echo "{$design_table_name}{$bck_postfix}{$backup_date}"; ?>', '<?php echo "{$data_projects_project_name}{$bck_postfix}{$backup_date}"; // phpcs:ignore WordPress.Security.EscapeOutput ?>', '<?php echo "{$data_projects_page_name}{$bck_postfix}{$backup_date}"; // phpcs:ignore WordPress.Security.EscapeOutput ?>', '<?php echo "{$data_projects_table_name}{$bck_postfix}{$backup_date}"; // phpcs:ignore WordPress.Security.EscapeOutput ?>', '<?php echo "{$data_publication_table_name}{$bck_postfix}{$backup_date}"; // phpcs:ignore WordPress.Security.EscapeOutput ?>', '<?php echo "{$menus_table_name}{$bck_postfix}{$backup_date}"; // phpcs:ignore WordPress.Security.EscapeOutput ?>', '<?php echo "{$csv_import_table_name}{$bck_postfix}{$backup_date}"; // phpcs:ignore WordPress.Security.EscapeOutput ?>', '<?php echo "{$logging_table_name}{$bck_postfix}{$backup_date}"; // phpcs:ignore WordPress.Security.EscapeOutput ?>')"
												>
												</span>
												</td>
												<td>
												<span class="dashicons dashicons-upload wpda_tooltip"
													  title="Restore backup: <?php echo esc_attr( $display_date ); ?>"
													  onclick="jQuery('.wpda-restore-repository-backup-selected').removeClass('wpda-restore-repository-backup-selected'); jQuery(this).closest('tr').addClass('wpda-restore-repository-backup-selected'); jQuery('#wpda-restore-repository-backup').show();"
												>
												</span>
												</td>
												<td>
												<span class="dashicons dashicons-trash wpda_tooltip"
													  title="Remove repository backup: <?php echo esc_attr( $display_date ); ?>"
													  onclick="if (confirm('Remove backup?')) { jQuery('#remove_backup_date').val('<?php echo esc_attr( $backup_date ); ?>'); jQuery('#wpda-remove-backup').submit(); }"
												>
												</span>
												</td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $table_settings_table_name . $bck_postfix . $backup_date ) ); ?></td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $media_table_name . $bck_postfix . $backup_date ) ); ?></td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $design_table_name . $bck_postfix . $backup_date ) ); ?></td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $data_projects_project_name . $bck_postfix . $backup_date ) ); ?></td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $data_projects_page_name . $bck_postfix . $backup_date ) ); ?></td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $data_projects_table_name . $bck_postfix . $backup_date ) ); ?></td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $data_publication_table_name . $bck_postfix . $backup_date ) ); ?></td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $menus_table_name . $bck_postfix . $backup_date ) ); ?></td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $csv_import_table_name . $bck_postfix . $backup_date ) ); ?></td></td>
												<td class="wpda-repository-column"><?php echo esc_attr( $this->count_repository_backup_table( $logging_table_name . $bck_postfix . $backup_date ) ); ?></td>
											</tr>
											<?php
										}
									}
									?>
								</table>
								<?php
								$replace_title = __( 'CAREFULL This will truncate your actual repository table before restoring the selected version. Make a backup first!', 'wp-data-access' );
								$add_title     = __( 'IMPORTANT This option fails for which existing rows.', 'wp-data-access' );
								?>
								<div id="wpda-restore-repository-backup" style="display: none">
									<fieldset class="wpda_fieldset wpda-restore-repository-backup">
										<legend>
											Restore repository backup
										</legend>
										<table class="wpda-restore-repository-backup">
											<tr>
												<td>Table Settings</td>
												<th>
													<label title="<?php echo esc_attr( $replace_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_table_settings_table_name" value="replace"/>
														Replace
													</label>
												</th>
												<th>
													<label title="<?php echo esc_attr( $add_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_table_settings_table_name" value="add"/>
														Add
													</label>
												</th>
												<th>
													<label>
														<input type="radio" name="restore_table_settings_table_name" value="noaction" checked/>
														No action
													</label>
												</th>
											</tr>
											<tr>
												<td>Media Columns</td>
												<th>
													<label title="<?php echo esc_attr( $replace_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_media_table_name" value="replace"/>
														Replace
													</label>
												</th>
												<th>
													<label title="<?php echo esc_attr( $add_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_media_table_name" value="add"/>
														Add
													</label>
												</th>
												<th>
													<label>
														<input type="radio" name="restore_media_table_name" value="noaction" checked/>
														No action
													</label>
												</th>
											</tr>
											<tr>
												<td>Data Designer</td>
												<th>
													<label title="<?php echo esc_attr( $replace_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_design_table_name" value="replace"/>
														Replace
													</label>
												</th>
												<th>
													<label title="<?php echo esc_attr( $add_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_design_table_name" value="add"/>
														Add
													</label>
												</th>
												<th>
													<label>
														<input type="radio" name="restore_design_table_name" value="noaction" checked/>
														No action
													</label>
												</th>
											</tr>
											<tr>
												<td>Data Projects (incl pages)</td>
												<th>
													<label title="<?php echo esc_attr( $replace_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_data_projects_project_name" value="replace"/>
														Replace
													</label>
												</th>
												<th>
													<label title="<?php echo esc_attr( $add_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_data_projects_project_name" value="add"/>
														Add
													</label>
												</th>
												<th>
													<label>
														<input type="radio" name="restore_data_projects_project_name" value="noaction" checked/>
														No action
													</label>
												</th>
											</tr>
											<tr>
												<td>Project Templates</td>
												<th>
													<label title="<?php echo esc_attr( $replace_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_data_projects_table_name" value="replace"/>
														Replace
													</label>
												</th>
												<th>
													<label title="<?php echo esc_attr( $add_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_data_projects_table_name" value="add"/>
														Add
													</label>
												</th>
												<th>
													<label>
														<input type="radio" name="restore_data_projects_table_name" value="noaction" checked/>
														No action
													</label>
												</th>
											</tr>
											<tr>
												<td>Data Publisher</td>
												<th>
													<label title="<?php echo esc_attr( $replace_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_data_publication_table_name" value="replace"/>
														Replace
													</label>
												</th>
												<th>
													<label title="<?php echo esc_attr( $add_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_data_publication_table_name" value="add"/>
														Add
													</label>
												</th>
												<th>
													<label>
														<input type="radio" name="restore_data_publication_table_name" value="noaction" checked/>
														No action
													</label>
												</th>
											</tr>
											<tr>
												<td>Data Menus</td>
												<th>
													<label title="<?php echo esc_attr( $replace_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_menus_table_name" value="replace"/>
														Replace
													</label>
												</th>
												<th>
													<label title="<?php echo esc_attr( $add_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_menus_table_name" value="add"/>
														Add
													</label>
												</th>
												<th>
													<label>
														<input type="radio" name="restore_menus_table_name" value="noaction" checked/>
														No action
													</label>
												</th>
											</tr>
											<tr>
												<td>CSV Uploads</td>
												<th>
													<label title="<?php echo esc_attr( $replace_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_csv_import_table_name" value="replace"/>
														Replace
													</label>
												</th>
												<th>
													<label title="<?php echo esc_attr( $add_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_csv_import_table_name" value="add"/>
														Add
													</label>
												</th>
												<th>
													<label>
														<input type="radio" name="restore_csv_import_table_name" value="noaction" checked/>
														No action
													</label>
												</th>
											</tr>
											<tr>
												<td>Data Logging</td>
												<th>
													<label title="<?php echo esc_attr( $replace_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_logging_table_name" value="replace"/>
														Replace
													</label>
												</th>
												<th>
													<label title="<?php echo esc_attr( $add_title ); ?>" class="wpda_tooltip">
														<input type="radio" name="restore_logging_table_name" value="add"/>
														Add
													</label>
												</th>
												<th>
													<label>
														<input type="radio" name="restore_logging_table_name" value="noaction" checked/>
														No action
													</label>
												</th>
											</tr>
										</table>
										<div style="padding-top: 10px; text-align: center;">
											<a href="javascript:void(0)"
											   class="button"
											   onclick="jQuery('.wpda-restore-repository-backup-selected').removeClass('wpda-restore-repository-backup-selected'); jQuery('#wpda-restore-repository-backup').hide();"
											>
												<?php echo __( 'Cancel' ); ?>
											</a>
											<a href="javascript:void(0)"
											   class="button"
											   onclick="restore_respository_tables()"
											>
												<?php echo __( 'Restore' ); ?>
											</a>
										</div>
									</fieldset>
								</div>
								<?php
							}
							?>
							<div class="wpda-spacer"></div>
							<a href="javascript:void(0)"
							   class="button"
							   onclick=" if (confirm('<?php echo __( 'Backup repository tables?', 'wp-data-access' ); ?>')) { jQuery('#wpda-create-backup').submit(); }"
							>
								<?php echo __( 'Create new repository backup' ); ?>
							</a>
							<a href="javascript:void(0)"
							   class="button <?php echo 0 === $no_backup_tables ? 'disabled' : ''; ?>"
							   onclick="if (confirm('<?php echo __( 'Delete all backup tables?', 'wp-data-access' ) . '\n' . __( 'This action cannot be undone.', 'wp-data-access' ) . '\n' . __( '\\\'Cancel\\\' to stop, \\\'OK\\\' to delete.', 'wp-data-access' ); ?>')) { jQuery('#wpda-remove-all-backups').submit(); }"
							>
								<?php echo __( 'Delete all (' ) . esc_html( $no_backup_tables ) . __( ') repository backup tables' ); ?>
							</a>
						</td>
					</tr>

					<tr>
						<th>
							<?php echo __( 'Table Settings', 'wp-data-access' ); ?>
						</th>
						<td>
							<span class="dashicons <?php echo $table_settings_table_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $table_settings_table_name ); ?></strong>
							<?php echo $table_settings_table_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $table_settings_table_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_table_settings ); ?>
									<?php echo __( 'table settings defined in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

					<tr>
						<th>
							<?php echo __( 'Manage Media', 'wp-data-access' ); ?>
						</th>
						<td>
							<span class="dashicons <?php echo $media_table_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $media_table_name ); ?></strong>
							<?php echo $media_table_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $media_table_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_media ); ?>
									<?php echo __( 'media columns defined in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

					<tr>
						<th>
							Data Designer
						</th>
						<td>
							<span class="dashicons <?php echo $design_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $design_table_name ); ?></strong>
							<?php echo $design_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $design_table_name_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_table_designs ); ?>
									<?php echo __( 'table designs in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

					<tr>
						<th>
							Data Projects
						</th>
						<td>
							<span class="dashicons <?php echo $design_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $data_projects_project_name ); ?></strong>
							<?php echo $design_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<strong>( = Data Projects )</strong>
							<br/>
							<span class="dashicons <?php echo $design_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $data_projects_page_name ); ?></strong>
							<?php echo $design_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<strong>( = Project Pages )</strong>
							<br/>
							<span class="dashicons <?php echo $design_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $data_projects_table_name ); ?></strong>
							<?php echo $design_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<strong>( = Project Templates )</strong>
							<br/>
							<?php
							if ( $data_projects_project_name_exists ) {
								?>
								<br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_projects ); ?>
									<?php echo __( 'data projects in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							if ( $data_projects_page_name_exists ) {
								?>
								<br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_pages ); ?>
									<?php echo __( 'project pages in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							if ( $data_projects_table_name_exists ) {
								?>
								<br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_project_table_designs ); ?>
									<?php echo __( 'project tables in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

					<tr>
						<th>
							Data Publisher
						</th>
						<td>
							<span class="dashicons <?php echo $data_publication_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $data_publication_table_name ); ?></strong>
							<?php echo $data_publication_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $data_publication_table_name_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_data_publication ); ?>
									<?php echo __( 'publication in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

					<tr>
						<th>
							Data Menus
						</th>
						<td>
							<span class="dashicons <?php echo $menus_table_name_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $menus_table_name ); ?></strong>
							<?php echo $menus_table_name_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $menus_table_name_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_menu_items ); ?>
									<?php echo __( 'menus in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

					<tr>
						<th>
							<?php echo __( 'CSV Uploads', 'wp-data-access' ); ?>
						</th>
						<td>
							<span class="dashicons <?php echo $csv_import_table_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $csv_import_table_name ); ?></strong>
							<?php echo $csv_import_table_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $csv_import_table_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_csv_import ); ?>
									<?php echo __( 'menus in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

					<tr>
						<th>
							Data Logging
						</th>
						<td>
							<span class="dashicons <?php echo $logging_table_exists ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
							<?php echo esc_attr( $table ); ?>
							<strong><?php echo esc_attr( $logging_table_name ); ?></strong>
							<?php echo $logging_table_exists ? esc_attr( $found ) : esc_attr( $not_found ); ?>
							<?php
							if ( $logging_table_exists ) {
								?>
								<br/><br/>
								<span class="dashicons dashicons-yes"></span>
								<strong>
									<?php echo esc_attr( $no_logs ); ?>
									<?php echo __( 'logging rows in repository', 'wp-data-access' ); ?>
								</strong>
								<?php
							}
							?>
						</td>
					</tr>

				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php echo __( 'Save Manage Respository Settings', 'wp-data-access' ); ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php echo __( 'Reset to defaults?', 'wp-data-access' ); ?>')) {
						   jQuery('input[name=\'action\']').val('setdefaults');
						   jQuery('#wpda_settings_repository').trigger('submit');
						   }"
					   class="button button-secondary">
						<?php echo __( 'Reset Manage Repository Settings To Defaults', 'wp-data-access' ); ?>
					</a>
					<?php
					$wpnonce_recreate = wp_create_nonce( 'wpda-settings-recreate-repository-' . WPDA::get_current_user_login() );
					?>
					<a href="<?php echo admin_url( 'admin.php' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>?page=<?php echo esc_attr( $this->page ); ?>&tab=repository&repos=true&_wpnonce=<?php echo esc_attr( $wpnonce_recreate ); ?>"
					   class="button button-secondary">
						<?php echo __( 'Recreate', 'wp-data-access' ); ?> WP Data Access
						<?php echo __( 'Repository', 'wp-data-access' ); ?>
					</a>
				</div>
				<?php wp_nonce_field( 'wpda-repository-settings-' . WPDA::get_current_user_login(), '_wpnonce', false ); ?>
			</form>

			<div class="wpda-table-settings-button">

				<?php

				$repository_valid = true;

				// Check if repository should be recreated.
				if (
					! $menus_table_name_exists ||
					! $design_table_name_exists ||
					! $data_projects_project_name_exists ||
					! $data_projects_page_name_exists ||
					! $data_projects_table_name_exists ||
					! $data_publication_table_name_exists
				) {
					?>
					<p><strong><?php echo __( 'Your repository has errors!', 'wp-data-access' ); ?></strong></p>
					<p>
						<?php echo __( 'Recreate the WP Data Access repository to solve this problem.', 'wp-data-access' ); ?>
						<?php echo __( 'Please leave your comments on the support forum if the problem remains.', 'wp-data-access' ); ?>
						(<a href="https://wordpress.org/support/plugin/wp-data-access/" target="_blank">go to forum</a>)
					</p>
					<?php

					$repository_valid = false;
				}

				?>

				
			</div>

			<?php

		}

	}

}
