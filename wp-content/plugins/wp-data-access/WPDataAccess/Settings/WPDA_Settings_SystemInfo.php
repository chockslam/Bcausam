<?php

namespace WPDataAccess\Settings {

	use WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Logging_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Media_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_User_Menus_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Page_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Model;
	use WPDataAccess\WPDA;

	class WPDA_Settings_SystemInfo extends WPDA_Settings {

		/**
		 * Add system info tab content
		 *
		 * See class documentation for flow explanation.
		 *
		 * @since   2.0.13
		 */
		protected function add_content() {
			global $wpdb;
			global $wp_version;

			$uploads = wp_get_upload_dir();

			$menus_table_name        = WPDA_User_Menus_Model::get_base_table_name();
			$menus_table_name_exists = WPDA_User_Menus_Model::table_exists();

			$design_table_name        = WPDA_Design_Table_Model::get_base_table_name();
			$design_table_name_exists = WPDA_Design_Table_Model::table_exists();

			$logging_table_name   = WPDA_Logging_Model::get_base_table_name();
			$logging_table_exists = WPDA_Logging_Model::table_exists();

			$data_projects_project_name        = WPDP_Project_Model::get_base_table_name();
			$data_projects_project_name_exists = WPDP_Project_Model::table_exists();

			$data_projects_page_name        = WPDP_Page_Model::get_base_table_name();
			$data_projects_page_name_exists = WPDP_Page_Model::table_exists();

			$data_projects_table_name        = WPDP_Project_Design_Table_Model::get_base_table_name();
			$data_projects_table_name_exists = WPDP_Project_Design_Table_Model::table_exists();

			$media_table_name   = WPDA_Media_Model::get_base_table_name();
			$media_table_exists = WPDA_Media_Model::table_exists();

			$data_publication_table_name        = WPDA_Publisher_Model::get_base_table_name();
			$data_publication_table_name_exists = WPDA_Publisher_Model::table_exists();

			$table_settings_table_name   = WPDA_Table_Settings_Model::get_base_table_name();
			$table_settings_table_exists = WPDA_Table_Settings_Model::table_exists();

			// Check table characteristics.
			$table_chararteristics_results   = $wpdb->get_results(
				$wpdb->prepare(
					'
					select table_name AS table_name, engine AS engine, table_collation AS table_collation
					from information_schema.tables
					where table_schema = %s
					  and table_name in (%s, %s, %s, %s, %s, %s, %s, %s, %s)',
					array(
						$wpdb->dbname,
						$menus_table_name,
						$design_table_name,
						$logging_table_name,
						$data_projects_project_name,
						$data_projects_page_name,
						$data_projects_table_name,
						$media_table_name,
						$data_publication_table_name,
						$table_settings_table_name,
					)
				),
				'ARRAY_A'
			);
			$table_chararteristics_engine    = array();
			$table_chararteristics_collation = array();
			if ( false !== $table_chararteristics_results ) {
				foreach ( $table_chararteristics_results as $table_chararteristics_result ) {
					$table_chararteristics_engine[ $table_chararteristics_result['table_name'] ]    = $table_chararteristics_result['engine'];
					$table_chararteristics_collation[ $table_chararteristics_result['table_name'] ] = $table_chararteristics_result['table_collation'];
				}
			}
			?>
			<style>
				.wpda-table-system-info th {
					font-style: italic;
					font-weight: normal;
					padding: 0;
				}

				.wpda-table-system-info td {
					padding: 0;
				}

				.wpda-table-settings tr:nth-child(even) {
					background: unset;
				}
			</style>
			<script type='text/javascript'>
				jQuery(function () {
					var text_to_clipboard = new ClipboardJS("#button-copy-to-clipboard", {
						text: function () {
							clipboard_text = "";
							jQuery("#wpda_table_info tr .wpda_system_info_title").each(function () {
								clipboard_text += jQuery(this).text().trim() + "\n";
								jQuery(this).parent().find("th.wpda_system_info_subtitle").each(function () {
									clipboard_text += jQuery(this).text().trim();
									clipboard_text += "=";
									clipboard_text += jQuery(this).parent().find("td.wpda_system_info_value").text().trim() + "\n";
								});
							});
							return clipboard_text;
						}
					});
					text_to_clipboard.on('success', function (e) {
						jQuery.notify('<?php echo __( 'System info successfully copied to clipboard!' ); ?>','info');
					});
					text_to_clipboard.on('error', function (e) {
						jQuery.notify('<?php echo __( 'Could not copy system info to clipboard!' ); ?>','error');
					});
				});
			</script>
			<table class="wpda-table-settings" id="wpda_table_info">
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'Operating System' ); ?></th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Type' ); ?></th>
								<td class="wpda_system_info_value">
									<?php echo esc_attr( php_uname( 's' ) ); ?>
								</td>
								<td style="float:right">
									<a id="button-copy-to-clipboard" href="javascript:void(0)"
									   class="button button-primary">
										<span class="material-icons wpda_icon_on_button">content_copy</span>
										<?php echo __( 'Copy to clipboard' ); ?>
									</a>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Release' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( php_uname( 'r' ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( php_uname( 'v' ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Machine Type' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( php_uname( 'm' ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Host Name' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( php_uname( 'n' ) ); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'Database Management System' ); ?></th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									$db_version = $wpdb->get_results( "SHOW VARIABLES LIKE 'version'", 'ARRAY_N' );
									if ( is_array( $db_version ) && isset( $db_version[0][1] ) ) {
										echo esc_attr( $db_version[0][1] );
									} else {
										echo esc_attr( $wpdb->db_version );
									}
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Pivileges' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									$db_privileges = $wpdb->get_results( 'SHOW PRIVILEGES', 'ARRAY_N' );
									if ( is_array( $db_privileges ) ) {
										$db_privileges_output = '';
										foreach ( $db_privileges as $db_privilege ) {
											$db_privileges_output .= "$db_privilege[0], ";
										}
										echo esc_attr( substr( $db_privileges_output, 0, strlen( $db_privileges_output ) - 2 ) );
									}
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Grants' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									$db_grants = $wpdb->get_results( 'SHOW GRANTS', 'ARRAY_N' );
									if ( is_array( $db_grants ) ) {
										$db_grants_output = '';
										foreach ( $db_grants as $db_grant ) {
											$strpos = stripos( $db_grant[0], 'IDENTIFIED BY PASSWORD ' );
											if ( false !== $strpos ) {
												$db_grants_output .= substr( $db_grant[0], 0, $strpos ) . 'IDENTIFIED BY PASSWORD \'*****\'<br/>';
											} else {
												$db_grants_output .= "$db_grant[0]<br/>";
											}
										}
										echo esc_attr( $db_grants_output );
									}
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'SQL Mode' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									$db_sql_mode = $wpdb->get_results( 'SHOW VARIABLES LIKE \'sql_mode\'', 'ARRAY_N' );
									if ( isset( $db_sql_mode[0][1] ) ) {
										echo esc_attr( $db_sql_mode[0][1] );
									}
									?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'Web Server' ); ?></th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Software' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'PHP Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( phpversion() ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Protocol' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Name' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Address' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Root DIR' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Temp DIR' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( sys_get_temp_dir() ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'HTTP Upload' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo @ini_get( 'file_uploads' ) ? 'Enabled' : 'Disabled'; ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Max Upload File Size' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( @ini_get( 'upload_max_filesize' ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Post Max Size' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( @ini_get( 'post_max_size' ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Max Execution Time' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( @ini_get( 'max_execution_time' ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Max Input Time' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( @ini_get( 'max_input_time' ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Memory Limit' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( @ini_get( 'memory_limit' ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Output Buffering' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( @ini_get( 'output_buffering' ) ); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'WordPress' ); ?></th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( $wp_version ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Home DIR' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									echo esc_attr( get_home_path() );

									$error_level = error_reporting();
									error_reporting( E_ALL ^ E_WARNING );
									$file_permission = fileperms( get_home_path() );
									error_reporting( $error_level );
									echo '&nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;' . esc_attr( decoct( $file_permission & 0777 ) );
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Uploads DIR' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									echo esc_attr( $uploads['basedir'] );

									$error_level = error_reporting();
									error_reporting( E_ALL ^ E_WARNING );
									$file_permission = fileperms( $uploads['basedir'] );
									error_reporting( $error_level );
									echo '&nbsp;&nbsp;&nbsp;>&nbsp;&nbsp;&nbsp;' . esc_attr( decoct( $file_permission & 0777 ) );
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Home URL' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( home_url() ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Site URL' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( site_url() ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Upload URL' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( $uploads['baseurl'] ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Use MySQLi' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									// Taken from wp-db class
									if ( function_exists( 'mysqli_connect' ) ) {
										$use_mysqli = true;
										if ( defined( 'WP_USE_EXT_MYSQL' ) ) {
											$use_mysqli = ! WP_USE_EXT_MYSQL;
										}
										echo $use_mysqli ? 'true' : 'false';
									}
									?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database Host' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( DB_HOST ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database Name' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( DB_NAME ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database User' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( DB_USER ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database Character Set' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( DB_CHARSET ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Database Collate' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( DB_COLLATE ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'WP Debugging Mode' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo ! defined( 'WP_DEBUG' ) ? 'undefined' : ( true === WP_DEBUG ? 'true' : 'false' ); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title">WP Data Access</th>
					<td>
						<table class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Version' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( WPDA::get_option( WPDA::OPTION_WPDA_VERSION ) ); ?>
								</td>
							</tr>
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Repository' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php
									echo $menus_table_name_exists ? '+' : '-';
									echo esc_attr( $menus_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $menus_table_name ] ) ? $table_chararteristics_engine[ $menus_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $menus_table_name ] ) ? $table_chararteristics_collation[ $menus_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ')';
									echo ' <br/>';
									echo $design_table_name_exists ? '+' : '-';
									echo esc_attr( $design_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $design_table_name ] ) ? $table_chararteristics_engine[ $design_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $design_table_name ] ) ? $table_chararteristics_collation[ $design_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ')';
									echo ' <br/>';
									echo $logging_table_exists ? '+' : '-';
									echo esc_attr( $logging_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $logging_table_name ] ) ? $table_chararteristics_engine[ $logging_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $logging_table_name ] ) ? $table_chararteristics_collation[ $logging_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ')';
									echo ' <br/>';
									echo $data_projects_project_name_exists ? '+' : '-';
									echo esc_attr( $data_projects_project_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $data_projects_project_name ] ) ? $table_chararteristics_engine[ $data_projects_project_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $data_projects_project_name ] ) ? $table_chararteristics_collation[ $data_projects_project_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ')';
									echo ' <br/>';
									echo $data_projects_page_name_exists ? '+' : '-';
									echo esc_attr( $data_projects_page_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $data_projects_page_name ] ) ? $table_chararteristics_engine[ $data_projects_page_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $data_projects_page_name ] ) ? $table_chararteristics_collation[ $data_projects_page_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ')';
									echo ' <br/>';
									echo $data_projects_table_name_exists ? '+' : '-';
									echo esc_attr( $data_projects_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $data_projects_table_name ] ) ? $table_chararteristics_engine[ $data_projects_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $data_projects_table_name ] ) ? $table_chararteristics_collation[ $data_projects_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ')';
									echo ' <br/>';
									echo $media_table_exists ? '+' : '-';
									echo esc_attr( $media_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $media_table_name ] ) ? $table_chararteristics_engine[ $media_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $media_table_name ] ) ? $table_chararteristics_collation[ $media_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ')';
									echo ' <br/>';
									echo $data_publication_table_name_exists ? '+' : '-';
									echo esc_attr( $data_publication_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $data_publication_table_name ] ) ? $table_chararteristics_engine[ $data_publication_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $data_publication_table_name ] ) ? $table_chararteristics_collation[ $data_publication_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ')';
									echo ' <br/>';
									echo $table_settings_table_exists ? '+' : '-';
									echo esc_attr( $table_settings_table_name );
									echo ' (';
									echo isset( $table_chararteristics_engine[ $table_settings_table_name ] ) ? $table_chararteristics_engine[ $table_settings_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ' | ';
									echo isset( $table_chararteristics_collation[ $table_settings_table_name ] ) ? $table_chararteristics_collation[ $table_settings_table_name ] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
									echo ')';
									?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="wpda_system_info_title"><?php echo __( 'Browser' ); ?></th>
					<td>
						<table id="wpda_system_info_browser" class="wpda-table-system-info" style="width:100%">
							<tr>
								<th class="wpda_system_info_subtitle"><?php echo __( 'Agent' ); ?></th>
								<td class="wpda_system_info_value" colspan="2">
									<?php echo esc_attr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ); ?>
								</td>
							</tr>
							<script type='text/javascript'>
								jQuery.each(jQuery.browser, function (i, val) {
									jQuery("#wpda_system_info_browser").append("<tr><th class=\"wpda_system_info_subtitle\">" + i[0].toUpperCase() + i.substring(1).toLowerCase() + "</th><td class=\"wpda_system_info_value\" colspan=\"2\">" + val + "</td></tr>");
								});
							</script>
						</table>
					</td>
				</tr>
			</table>
			<?php
		}

	}

}
