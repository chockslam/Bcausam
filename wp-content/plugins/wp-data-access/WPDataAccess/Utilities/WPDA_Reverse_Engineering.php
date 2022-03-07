<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Reverse_Engineering
	 *
	 * @author  Peter Schulz
	 * @since   2.0.13
	 */
	class WPDA_Reverse_Engineering {

		/**
		 * Database connection
		 *
		 * @var null
		 */
		protected $wpdadb = null;

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name;

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * WPDA_Reverse_Engineering constructor
		 *
		 * @param string $table_name Database table name
		 * @param string $schema_name Database schema name
		 *
		 * @since   2.0.13
		 */
		public function __construct( $table_name, $schema_name ) {
			$this->schema_name = $schema_name;
			$this->table_name  = $table_name;

			$this->wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $this->wpdadb ) {
				wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), esc_attr( $this->schema_name ) ) );
			}
		}

		/**
		 * Get table info in Data Designer format
		 *
		 * @param string $design_mode Possible values are: Basic or Advanced
		 *
		 * @return array Database table in Data Designer format
		 *
		 * @since   2.0.13
		 */
		public function get_designer_format( $design_mode ) {
			$table_structure = array();

			$tab  = $this->get_table_info();
			$rows = $this->get_table_columns();
			$idxs = $this->get_table_indexes();

			$idx_current         = '';
			$idx_current_unique  = '';
			$idx_current_columns = '';
			$idx_array           = array();
			$fulltext_support    = get_option( 'wpda_fulltext_support' );
			foreach ( $idxs as $idx ) {
				if ( $idx['index_name'] !== $idx_current ) {
					if ( $idx_current_columns !== '' ) {
						$idx_array[] = array(
							'index_name'   => $idx_current,
							'unique'       => $idx_current_unique,
							'column_names' => rtrim( $idx_current_columns, ',' ),
						);
					}
					$idx_current = $idx['index_name'];
				}
				if ( 'on' === $fulltext_support ) {
					if ( 'FULLTEXT' === $idx['index_type'] ) {
						$idx_current_unique = 'FULLTEXT';
					} else {
						$idx_current_unique = $idx['non_unique'] === '1' ? 'No' : 'Yes';
					}
				} else {
					$idx_current_unique = $idx['non_unique'] === '1' ? 'No' : 'Yes';
				}
				$idx_current_columns = $idx['column_name'] . ',';
			}
			if ( $idx_current !== '' ) {
				$idx_array[] = array(
					'index_name'   => $idx_current,
					'unique'       => $idx_current_unique,
					'column_names' => rtrim( $idx_current_columns, ',' ),
				);
			}

			foreach ( $rows as $row ) {
				$obj              = (object) null;
				$obj->column_name = $row['column_name'];

				$obj->data_type = $row['data_type'];
				if ( stripos( $row['column_type'], 'unsigned' ) !== false ) {
					$obj->type_attribute = 'unsigned';
				} elseif ( stripos( $row['column_type'], 'unsigned zerofill' ) !== false ) {
					$obj->type_attribute = 'unsigned zerofill';
				} else {
					$obj->type_attribute = '';
				}

				$obj->key       = 'PRI' === $row['column_key'] ? 'Yes' : 'No';
				$obj->mandatory = 'YES' === $row['is_nullable'] ? 'No' : 'Yes';

				$obj->max_length  = '';
				$max_length_start = strpos( $row['column_type'], '(' );
				if (
					false !== $max_length_start &&
					'enum' !== $row['data_type'] &&
					'set' !== $row['data_type']
				) {
					// Get max length from column_type
					$max_length_end = strpos( $row['column_type'], ')' );
					if ( false != $max_length_end ) {
						$obj->max_length = substr( $row['column_type'], $max_length_start + 1, $max_length_end - $max_length_start - 1 );
					} elseif ( null !== $row['character_maximum_length'] ) {
						$obj->max_length = $row['character_maximum_length'];
					} elseif ( null !== $row['numeric_precision'] ) {
						$obj->max_length = $row['numeric_precision'];
						if ( null !== $row['numeric_scale'] ) {
							$obj->max_length .= ",{$row['numeric_scale']}";
						}
					}
				}

				$obj->extra = $row['extra'];
				if ( '' !== $row['column_default'] && null !== $row['column_default'] ) {
					$simple_data_type = WPDA::get_type( $obj->data_type );
					if ( 'number' === $simple_data_type || 'date' === $simple_data_type ) {
						$obj->default = $row['column_default'];
					} else {
						$obj->default = "'{$row['column_default']}'";
					}
				} else {
					$obj->default = '';
				}

				$obj->list = '';
				if ( 'enum' === $row['data_type'] ) {
					$obj->list = substr( substr( $row['column_type'], 5 ), 0, - 1 );
				} elseif ( 'set' === $row['data_type'] ) {
					$obj->list = substr( substr( $row['column_type'], 4 ), 0, - 1 );
				}

				$table_structure[] = $obj;
			}

			return array(
				'design_mode' => $design_mode,
				'engine'      => $tab[0]['engine'],
				'collation'   => $tab[0]['table_collation'],
				'table'       => $table_structure,
				'indexes'     => $idx_array,
			);
		}

		/**
		 * Get database table info
		 *
		 * @return array Table info
		 *
		 * @since   2.0.13
		 */
		protected function get_table_info() {
			$query = $this->wpdadb->prepare(
				'
					SELECT engine AS engine,
				           table_collation AS table_collation
					FROM   information_schema.tables
					WHERE  table_schema = %s
					  AND  table_name   = %s
				',
				array(
					$this->wpdadb->dbname,
					$this->table_name,
				)
			);

			return $this->wpdadb->get_results( $query, 'ARRAY_A' ); // phpcs:ignore Standard.Category.SniffName.ErrorCode
		}

		/**
		 * Get database column info
		 *
		 * @return array Table column info
		 *
		 * @since   2.0.13
		 */
		protected function get_table_columns() {
			$query = $this->wpdadb->prepare(
				'
					SELECT column_name AS column_name,
						   data_type AS data_type,
						   column_type AS column_type,
						   is_nullable AS is_nullable,
						   column_default AS column_default,
						   column_key AS column_key,
						   extra AS extra,
						   character_maximum_length AS character_maximum_length,
						   numeric_precision AS numeric_precision,
					       numeric_scale AS numeric_scale
					FROM   information_schema.columns
					WHERE  table_schema = %s
					  AND  table_name   = %s
					ORDER BY ordinal_position
				',
				array(
					$this->wpdadb->dbname,
					$this->table_name,
				)
			);

			return $this->wpdadb->get_results( $query, 'ARRAY_A' ); // phpcs:ignore Standard.Category.SniffName.ErrorCode
		}

		/**
		 * Get database index info
		 *
		 * @return array Index info
		 *
		 * @since   2.0.13
		 */
		protected function get_table_indexes() {
			$query = $this->wpdadb->prepare(
				"
					SELECT index_name AS index_name,
						   column_name AS column_name,
						   non_unique AS non_unique,
	   					   index_type AS index_type
					FROM   information_schema.statistics
					WHERE  table_schema = %s
					  AND  table_name   = %s
					  AND  index_name   != 'PRIMARY'
					ORDER BY index_name, column_name, seq_in_index
				",
				array(
					$this->wpdadb->dbname,
					$this->table_name,
				)
			);

			return $this->wpdadb->get_results( $query, 'ARRAY_A' ); // phpcs:ignore Standard.Category.SniffName.ErrorCode
		}

	}

}
