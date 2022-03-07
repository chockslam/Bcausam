<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Export_Csv
	 *
	 * @author  Peter Schulz
	 * @since   2.0.13
	 */
	class WPDA_Export_Csv extends WPDA_Export_Formatted {

		/**
		 * File header for CSV export
		 *
		 * @since   2.0.13
		 */
		protected function header() {
			WPDA::sent_header( 'text/csv', null, "{$this->table_names}.csv" );

			$first_col = true;
			foreach ( $this->rows[0] as $column_name => $column_value ) {
				if ( $first_col ) {
					$first_col = false;
				} else {
					echo ',';
				}
				echo $this->wpda_list_columns->get_column_label( $column_name ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo "\n";
		}

		/**
		 * Process one row to be export in CSV format
		 *
		 * @param array $row
		 *
		 * @since   2.0.13
		 */
		protected function row( $row ) {
			$first_col = true;
			foreach ( $row as $column_name => $column_value ) {
				if ( $first_col ) {
					$first_col = false;
				} else {
					echo ',';
				}
				$is_string = 'number' === WPDA::get_type( $this->data_types[ $column_name ] ) ? '' : '"';
				echo $is_string . str_replace( '"', '""', $column_value ) . $is_string; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo "\n";
		}

	}

}
