<?php // phpcs:ignore Standard.Category.SniffName.ErrorCode
/**
 * JSON REST API
 */

namespace WPDataAccess\API {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\WPDA;

	/**
	 * JSON REST API main class
	 */
	class WPDA_API {

		const WPDA_API_VERSION = '/v1';
		const WPDA_REST_API    = 'wpda_rest_api';
		const WPDA_PER_PAGE    = 10;

		/**
		 * Tables accessible through REST API (default = none)
		 *
		 * @var array
		 */
		private $tables = array();

		/**
		 * Constructor
		 *
		 * Loads accessible tables and privileges
		 */
		public function __construct() {
			$this->tables = get_option( self::WPDA_REST_API );
			if ( false === $this->tables ) {
				$this->tables = array();
			}
		}

		/**
		 * Register routes
		 *
		 * @return void
		 */
		public function init() {
			register_rest_route(
				'wpda' . self::WPDA_API_VERSION,
				'lst',
				array(
					'methods'             => 'GET,POST',
					'callback'            => array( $this, 'lst' ),
					'permission_callback' => '__return_true',
				)
			);
		}

		/**
		 * Get list from table
		 *
		 * Supports: searching, ordering and pagination through URL arguments
		 *
		 * @return \WP_Error|\WP_REST_Response
		 */
		public function lst() {
			if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
				$schema_name = isset( $_POST['schema_name'] ) ? sanitize_text_field( wp_unslash( $_POST['schema_name'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$table_name  = isset( $_POST['table_name'] ) ? sanitize_text_field( wp_unslash( $_POST['table_name'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$page        = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
				$per_page    = isset( $_POST['per_page'] ) ? sanitize_text_field( wp_unslash( $_POST['per_page'] ) ) : self::WPDA_PER_PAGE; // phpcs:ignore WordPress.Security.NonceVerification
				$order       = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$orderby     = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$search      = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
			} elseif ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
				$schema_name = isset( $_GET['schema_name'] ) ? sanitize_text_field( wp_unslash( $_GET['schema_name'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$table_name  = isset( $_GET['table_name'] ) ? sanitize_text_field( wp_unslash( $_GET['table_name'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$page        = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
				$per_page    = isset( $_GET['per_page'] ) ? sanitize_text_field( wp_unslash( $_GET['per_page'] ) ) : self::WPDA_PER_PAGE; // phpcs:ignore WordPress.Security.NonceVerification
				$order       = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$orderby     = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$search      = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
			} else {
				$schema_name = null;
				$table_name  = null;
				$page        = null;
				$per_page    = null;
				$order       = null;
				$orderby     = null;
				$search      = null;
			}

			if (
				null === $schema_name ||
				null === $table_name ||
				! isset( $this->tables[ $schema_name ][ $table_name ] )
			) {
				return new \WP_Error( 'query', __( 'Invalid arguments', 'wp-data-access' ), array( 'status' => 404 ) );
			}

			// Remove backticks from names and add them to query to prevent SQL injection.
			$schema_name = str_replace( '`', '', $schema_name );
			$table_name  = str_replace( '`', '', $table_name );

			global $wp_rest_auth_cookie;
			if ( true !== $wp_rest_auth_cookie ) {
				// Anonymous call.
				$requesting_user = 'anonymous';
			} else {
				// Authorized user (get user id from : WPDA::get_current_user_id()).
				$requesting_user = 'authorized';
			}

			if (
				isset( $this->tables[ $schema_name ][ $table_name ][ $requesting_user ]['get'] ) &&
				true === $this->tables[ $schema_name ][ $table_name ][ $requesting_user ]['get']
			) {
				return $this->query( $schema_name, $table_name, $page, $per_page, $order, $orderby, $search );
			}

			return new \WP_Error( 'query', __( 'Forbidden', 'wp-data-access' ), array( 'status' => 403 ) );
		}

		/**
		 * Perform query and return result as JSON response
		 *
		 * @param string $schema_name Schema name (database).
		 * @param string $table_name Table Name.
		 * @param string $page Page number.
		 * @param string $per_page Rows per page.
		 * @param string $order Sorting columns.
		 * @param string $orderby Ascending (default) or descending.
		 * @param string $search Filter.
		 * @return \WP_Error|\WP_REST_Response
		 */
		private function query( $schema_name, $table_name, $page, $per_page, $order, $orderby, $search ) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( null !== $wpdadb ) {
				// Connected, perform queries.
				$suppress = $wpdadb->suppress_errors( true );
				$where    = '';
				if ( null !== $search ) {
					// Add search filter.
					$wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $schema_name, $table_name );
					$where             = WPDA::construct_where_clause(
						$schema_name,
						$table_name,
						$wpda_list_columns->get_searchable_table_columns(),
						$search
					);
					if ( '' !== $where ) {
						$where = " where {$where} ";
					}
				}
				$sqlorder = '';
				if ( null !== $orderby ) {
					// Add order by.
					$_orderby = explode( ',', $orderby );
					$_order   = explode( ',', $order );
					for ( $i = 0; $i < count( $_orderby ); $i++ ) { // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall, Squiz.PHP.DisallowSizeFunctionsInLoops
						if ( '' === $sqlorder ) {
							$sqlorder = 'order by ';
						} else {
							$sqlorder .= ',';
						}
						$sqlorder .= $_orderby[ $i ];
						if ( isset( $_order[ $i ] ) ) {
							$sqlorder .= " {$_order[ $i ]}";
						}
					}
				}
				$offset = ( $page - 1 ) * $per_page; // Calculate offset.
				// Query.
				$rows = $wpdadb->get_results(
					"select * from `{$table_name}` {$where} {$sqlorder} limit {$per_page} offset {$offset}",
					'ARRAY_A'
				);
				if ( $wpdadb->last_error ) {
					// Handle SQL errors.
					return new \WP_Error( 'query', __( 'Unprocessable Entity', 'wp-data-access' ) . $where, array( 'status' => 422 ) );
				}
				// Count rows.
				$countrows = $wpdadb->get_results(
					"select count(1) as rowcount from {$table_name}",
					'ARRAY_A'
				);
				if ( $wpdadb->last_error ) {
					// Handle SQL errors.
					return new \WP_Error( 'query', __( 'Unprocessable Entity', 'wp-data-access' ), array( 'status' => 422 ) );
				}
				$rowcount  = isset( $countrows[0]['rowcount'] ) ? $countrows[0]['rowcount'] : 0;
				$pagecount = floor( $rowcount / $per_page );
				if ( $pagecount != $rowcount / $per_page ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					$pagecount++;
				}
				$wpdadb->suppress_errors( $suppress );

				// Send response.
				$response = new \WP_REST_Response( $rows, 200 );
				$response->header( 'X-WP-Total', $rowcount ); // total rows for this query.
				$response->header( 'X-WP-TotalPages', $pagecount ); // pages for this query.
				return $response;
			} else {
				// Error connecting, return error.
				return new \WP_Error( 'query', __( 'Unprocessable Entity', 'wp-data-access' ), array( 'status' => 422 ) );
			}
		}

	}

}
