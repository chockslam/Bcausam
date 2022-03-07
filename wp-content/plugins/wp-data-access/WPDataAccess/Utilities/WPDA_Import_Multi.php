<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Dashboard\WPDA_Dashboard;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Import_Multi
	 *
	 * Imports a script file that might contain multiple SQL statement including create table and insert into
	 * statements.
	 *
	 * @author  Peter Schulz
	 * @since   1.6.0
	 */
	class WPDA_Import_Multi {

		const SOLUTIONS = '(<a href="https://wpdataaccess.com/docs/documentation/getting-started/known-limitations/" target="_blank">see solutions</a>)';

		/**
		 * URL where to post data
		 *
		 * @var string
		 */
		protected $url;

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name;

		/**
		 * Pointer to import file
		 *
		 * @var string
		 */
		protected $file_pointer;

		/**
		 * Content of import file
		 *
		 * @var string
		 */
		protected $file_content;

		/**
		 * Text to inform user (line 1).
		 *
		 * @var string
		 */
		protected $info_title = '';

		/**
		 * Text to inform user (line 1).
		 *
		 * @var string
		 */
		protected $info_text = '';

		/**
		 * Indicicates whether ZipArchive is installed.
		 *
		 * @var bool
		 */
		protected $isinstalled_ziparchive = false;

		/**
		 * WPDA_Import constructor
		 *
		 * Checks if ZipArchive is installed to support the import of ZIP files.
		 *
		 * @param string $page Page where to post data (url).
		 * @param string $schema_name Database schema name.
		 * @param array  $args Extra arguments.
		 *
		 * @since   1.6.0
		 */
		public function __construct( $page, $schema_name, $args = null ) {
			$this->url         = $page;
			$this->schema_name = $schema_name;

			$this->isinstalled_ziparchive = class_exists( '\ZipArchive' );

			if ( ! is_null( $args ) && isset( $args[0] ) && isset( $args[1] ) ) {
				$this->info_title = $args[0];
				$this->info_text  = $args[1];
			} else {
				$this->info_title = __(
					'IMPORT DATA/EXECUTE SCRIPT(S)',
					'wp-data-access'
				);
				if ( $this->isinstalled_ziparchive ) {
					$this->info_text = __(
						'Supports <strong>sql</strong> and <strong>zip</strong> file types.',
						'wp-data-access'
					);
				} else {
					$this->info_text = __(
						'Supports only file type <strong>sql</strong> (install <strong>ZipArchive</strong> for <strong>zip</strong> file type support).',
						'wp-data-access'
					);
				}
			}
		}

		/**
		 * Checks if request is valid and allowed
		 *
		 * If the requested import is valid and allowed, the import file is loaded and its content imported.
		 *
		 * @since   1.6.0
		 */
		public function check_post() {
			// Check if import was requested.
			if (
				isset( $_REQUEST['action'] ) &&
				'import' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) // input var okay.
			) {
				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonceimport'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonceimport'] ) ) : '?'; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpda-import-from-data-explorer-' . WPDA::get_current_user_login() ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( isset( $_FILES['filename'] ) ) {

					$temp_file_name = sanitize_text_field( wp_unslash( $_FILES['filename']['tmp_name'] ) );
					$temp_file_type = sanitize_text_field( wp_unslash( $_FILES['filename']['type'] ) );
					$orig_file_name = sanitize_text_field( wp_unslash( $_FILES['filename']['name'] ) );

					if ( 0 === $_FILES['filename']['error']
						 && is_uploaded_file( $temp_file_name )
					) {
						if (
							'application/zip' === $temp_file_type ||
							'application/x-zip' === $temp_file_type ||
							'application/x-zip-compressed' === $temp_file_type
						) {
							// Process ZIP file.
							if ( $this->isinstalled_ziparchive ) {
								$zip = new \ZipArchive();
								if ( $zip->open( $temp_file_name ) ) {
									for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
										$this->file_pointer = $zip->getStream( $zip->getNameIndex( $i ) );
										$this->import( $zip->getNameIndex( $i ) );
									}
								} else {
									// Error reading ZIP file.
									$this->import_failed( sprintf( __( 'Import failed [error reading ZIP file `%s`]', 'wp-data-access' ), $orig_file_name ) );
								}
							} else {
								// ZipArchive not installed.
								$this->import_failed( sprintf( __( 'Import failed - ZipArchive not installed %s', 'wp-data-access' ), self::SOLUTIONS ) );
							}
						} else {
							// Process plain file.
							$this->file_pointer = fopen( $temp_file_name, 'rb' );
							$this->import( $orig_file_name );
						}
					}
				} else {
					$this->upload_failed();
				}
			} elseif ( isset( $_REQUEST['impchk'] ) ) {
				$this->upload_failed();
			}
		}

		/**
		 * Perform import
		 *
		 * Import file is read in chunks to allow imports of large files without resource limitations. An indicator
		 * is maintained to follow the progress of the import. The indicator is used to show a message at the end
		 * whether the import succeeded or failed.
		 *
		 * @param string $file_name Import file name
		 *
		 * @since   1.6.0
		 */
		protected function import( $file_name ) {
			// Check if errors should be shown.
			$hide_errors = isset( $_REQUEST['hide_errors'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hide_errors'] ) ) : 'off';

			$result = true;
			global $wpdb;

			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), esc_attr( $this->schema_name ) ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), esc_attr( $this->schema_name ) ) );
				}
			}

			$suppress = $wpdadb->suppress_errors( 'on' === $hide_errors );

			if ( false !== $this->file_pointer ) {
				while ( ! feof( $this->file_pointer ) ) {
					$this->file_content .= fread( $this->file_pointer, 4096 );

					// Replace WP prefix and WPDA prefix.
					$this->file_content = str_replace( '{wp_schema}', $wpdb->dbname, $this->file_content );
					$this->file_content = str_replace( '{wp_prefix}', $wpdb->prefix, $this->file_content );
					$this->file_content = str_replace( '{wpda_prefix}', 'wpda', $this->file_content ); // for backward compatibility

					// Find and process SQL statements.
					$sql_end_unix    = strpos( $this->file_content, ";\n" );
					$sql_end_windows = strpos( $this->file_content, ";\r\n" );
					while ( false !== $sql_end_unix || false !== $sql_end_windows ) {
						if ( false === $sql_end_unix ) {
							$sql_end = $sql_end_windows;
						} elseif ( false === $sql_end_windows ) {
							$sql_end = $sql_end_unix;
						} else {
							$sql_end = min( $sql_end_unix, $sql_end_windows );
						}
						$sql = rtrim( substr( $this->file_content, 0, $sql_end ) );

						$this->file_content = substr( $this->file_content, strpos( $this->file_content, $sql ) + strlen( $sql ) + 1 );

						if ( false === $wpdadb->query( $sql ) ) {
							$result = false;
						}

						// Find next SQL statement.
						$sql_end_unix    = strpos( $this->file_content, ";\n" );
						$sql_end_windows = strpos( $this->file_content, ";\r\n" );
					}
				}
			}

			$wpdadb->suppress_errors( $suppress );

			// Process file content.
			if ( ! $result ) {
				$this->import_failed( sprintf( __( 'Import `%s` failed [check import file]', 'wp-data-access' ), $file_name ) );
			} else {
				// Import succeeded.
				$msg = new WPDA_Message_Box(
					array(
						'message_text' => sprintf( __( 'Import `%s` completed succesfully', 'wp-data-access' ), $file_name ),
					)
				);
				$msg->box();
			}
		}

		/**
		 * Inform user that an error occured
		 *
		 * @param string $msg Error message text
		 *
		 * @since   1.6.0
		 */
		protected function import_failed( $msg ) {
			$msg = new WPDA_Message_Box(
				array(
					'message_text'           => $msg,
					'message_type'           => 'error',
					'message_is_dismissible' => false,
				)
			);
			$msg->box();
		}

		/**
		 * Inform user that the import failed
		 *
		 * @since   1.6.0
		 */
		protected function upload_failed() {
			$msg = new WPDA_Message_Box(
				array(
					'message_text'           => sprintf( __( 'File upload failed %s', 'wp-data-access' ), self::SOLUTIONS ),
					'message_type'           => 'error',
					'message_is_dismissible' => false,
				)
			);
			$msg->box();
		}

		/**
		 * Adds an import button
		 *
		 * @param string $label Button label.
		 * @param string $class Button CSS class.
		 *
		 * @since   1.6.0
		 */
		public function add_button( $label = '', $class = 'page-title-action' ) {
			if ( '' === $label ) {
				$label = __( 'Imports and scripts', 'wp-data-access' );
			}
			?>
			<a href="javascript:void(0)"
			   onclick="jQuery('#upload_file_container_multi').show()"
			   class="wpda_tooltip <?php echo esc_attr( $class ); ?>"
			   title="<?php echo __( 'Allows to import data and execute SQL scripts', 'wp-data-access' ); ?>.
			<?php echo __( 'Add a ; and a new line character at the end of every SQL statement', 'wp-data-access' ); ?>"
			>
				<span class="material-icons wpda_icon_on_button">code</span> <?php echo esc_attr( $label ); ?></a>
			<?php
		}

		/**
		 * Adds an import container
		 *
		 * The container contains an upload form. The container is hidden by default. When the button created in
		 * {@see WPDA_Import_Multi::add_button()} is clicked, the container is shown.
		 *
		 * @since   1.6.0
		 */
		public function add_container() {
			$file_uploads_enabled = @ini_get( 'file_uploads' );
			?>

			<script type='text/javascript'>
				function before_submit_upload() {
					if (jQuery('#filename').val() == '') {
						alert('<?php echo __( 'No file to import!', 'wp-data-access' ); ?>');
						return false;
					}
					if (!(jQuery('#filename')[0].files[0].size < <?php echo esc_attr( WPDA::convert_memory_to_decimal( @ini_get( 'upload_max_filesize' ) ) ); ?>)) {
						alert("<?php echo __( 'File exceeds maximum size of', 'wp-data-access' ); ?> <?php echo esc_attr( @ini_get( 'upload_max_filesize' ) ); ?>!");
						return false;
					}
				}
				function activeTab(tabIndex) {
					jQuery(".import_tab").hide();
					jQuery(".nav-tab").removeClass("nav-tab-active");
					jQuery("#import_tab-" + tabIndex).show();
					jQuery("#import_tabs-" + tabIndex).addClass("nav-tab-active");
				}
			</script>

			<div id="upload_file_container_multi" style="display: none">
				<div>&nbsp;</div>
				<div>
					<?php
					if ( ! WPDA_Dashboard::add_actions_to_page_title() ) {
						?>
						<div class="wpda_upload_container_dashboard">
							<form id="form_import_multi_table" method="post"
								  action="<?php echo esc_attr( $this->url ); ?>&impchk"
								  enctype="multipart/form-data">
								<?php
								echo $this->import_sql(); // phpcs:ignore WordPress.Security.EscapeOutput
								?>
							</form>
						</div>
						<?php
					} else {
						if ( $file_uploads_enabled ) {
							$showtabs = isset( $_REQUEST['page'] ) && \WP_Data_Access_Admin::PAGE_MAIN === $_REQUEST['page'];
							?>
							<p>
							</p>
							<form id="form_import_multi_table" method="post"
								  action="<?php echo esc_attr( $this->url ); ?>&impchk"
								  enctype="multipart/form-data">
								<fieldset class="wpda_fieldset" style="position:relative">
									<legend>
										<span>
											<strong><?php echo esc_attr( $this->info_title ); ?></strong>
											<a href="https://wpdataaccess.com/docs/documentation/data-explorer/actions/#export" target="_blank" class="wpda_tooltip" title="Plugin Help - open a new tab or window">
												<span class="dashicons dashicons-editor-help "style="text-decoration:none;vertical-align:middle;font-size:18px;">
											</span></a>
										</span>
									</legend>
									<?php
									if ( $showtabs ) {
										?>
									<div id="import_tabs">
										<nav class="nav-tab-wrapper">
											<a id="import_tabs-1" href="javascript:void(0)" onclick="activeTab(1)" class="nav-tab nav-tab-active">
												Import SQL or ZIP file
											</a>
											<a id="import_tabs-2" href="javascript:void(0)" onclick="activeTab(2)" class="nav-tab">
												Import CSV file
											</a>
										</nav>
										<div id="import_tab-1" class="import_tab">
											<?php
											echo $this->import_sql(); // phpcs:ignore WordPress.Security.EscapeOutput
											?>
										</div>
										<div id="import_tab-2" class="import_tab" style="display:none">
											<p class="wpda_list_indent">
												<?php
												echo __(
													'Supports <strong>csv</strong> file types.',
													'wp-data-access'
												) . ' '
													. __( 'Maximum supported file size is', 'wp-data-access' ) . ' <strong>' . esc_attr( @ini_get( 'upload_max_filesize' ) ) . '</strong>. ';
												?>
											</p>
											<p class="wpda_list_indent">
												<ol style="margin-left:4em">
													<li>Upload <strong>csv</strong> file</li>
													<li>Perform column mapping</li>
													<li>Import <strong>csv</strong> file</li>
												</ol>
											</p>
											<p class="wpda_list_indent">
												<a href="?page=wpda&page_action=wpda_import_csv"
												   class="button button-primary wpda_tooltip"
												   title="Upload, map and import CSV files"
												><span class="material-icons wpda_icon_on_button">code</span> <?php echo __( 'Import CSV file' ); ?></a>
												<a href="javascript:void(0)"
												   onclick="jQuery('#upload_file_container_multi').hide()"
												   class="button">
													<span class="material-icons wpda_icon_on_button">cancel</span>
													<?php echo __( 'Cancel', 'wp-data-access' ); ?>
												</a>
											</p>
											<p class="wpda_list_indent">
												<?php
												echo '<strong>' . __( 'IMPORTANT', 'wp-data-access' ) . '</strong> &minus; ' .
													__( 'Use reload to reused an existing column mapping.', 'wp-data-access' );
												?>
											</p>
										</div>
									</div>
										<?php
									} else {
										echo $this->import_sql(); // phpcs:ignore WordPress.Security.EscapeOutput
									}
									?>
									<a class="wpda-icon-close" onclick="jQuery('#upload_file_container_multi').hide()">
										<span class="material-icons wpda_icon_on_button">closed</span>
									</a>
								</fieldset>
							</form>
							<?php
						} else {
							?>
							<p>
								<strong><?php echo __( 'ERROR', 'wp-data-access' ); ?></strong>
							</p>
							<p class="wpda_list_indent">
								<?php
								echo __( 'Your configuration does not allow file uploads!', 'wp-data-access' );
								echo ' ';
								echo __( 'Set', 'wp-data-access' );
								echo ' <strong>';
								echo __( 'file_uploads', 'wp-data-access' );
								echo '</strong> ';
								echo __( 'to', 'wp-data-access' );
								echo ' <strong>';
								echo __( 'On', 'wp-data-access' );
								echo '</strong> (<a href="https://wpdataaccess.com/docs/documentation/getting-started/known-limitations/">';
								echo __( 'see documentation', 'wp-data-access' );
								echo '</a>).';
								?>
							</p>
							<?php
						}
					}
					?>
				</div>
				<div>&nbsp;</div>
			</div>
			<?php
		}

		private function import_sql() {
			if ( $this->isinstalled_ziparchive ) {
				$file_extentions = '.sql,.zip';
			} else {
				$file_extentions = '.sql';
			}
			?>
			<p class="wpda_list_indent">
				<?php
				echo $this->info_text . ' ' . __( 'Maximum supported file size is', 'wp-data-access' ) . ' <strong>' . @ini_get( 'upload_max_filesize' ) . '</strong>. '; // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</p>
			<p class="wpda_list_indent">
				<input type="file" name="filename" id="filename" class="wpda_tooltip"
					   accept="<?php echo esc_attr( $file_extentions ); ?>">
				<label style="vertical-align:baseline;">
					<input type="checkbox" name="hide_errors" style="vertical-align:sub;" checked>
					<?php echo __( 'Hide errors', 'wp-data-access' ); ?>
				</label>
			</p>
			<p class="wpda_list_indent">
				<button type="submit"
						class="button button-primary"
						onclick="return before_submit_upload()">
					<span class="material-icons wpda_icon_on_button">code</span>
					<?php echo __( 'Import file/Execute script(s)', 'wp-data-access' ); ?>
				</button>
				<a href="javascript:void(0)"
				   onclick="jQuery('#upload_file_container_multi').hide()"
				   class="button">
					<span class="material-icons wpda_icon_on_button">cancel</span>
					<?php echo __( 'Cancel', 'wp-data-access' ); ?>
				</a>
				<input type="hidden" name="action" value="import">
				<?php wp_nonce_field( 'wpda-import-from-data-explorer-' . WPDA::get_current_user_login(), '_wpnonceimport', false ); ?>
			</p>
			<p class="wpda_list_indent">
				<?php
				echo '<strong>' . __( 'IMPORTANT', 'wp-data-access' ) . '</strong> &minus; ' .
					__( 'Add a ; and a new line character at the end of every SQL statement', 'wp-data-access' );
				?>
			</p>
			<?php
		}

	}

}
