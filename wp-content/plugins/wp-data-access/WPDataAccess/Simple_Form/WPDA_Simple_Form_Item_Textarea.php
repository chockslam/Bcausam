<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	class WPDA_Simple_Form_Item_Textarea extends WPDA_Simple_Form_Item {

		protected function show_item() {
			switch ( strtolower( $this->column_type ) ) {
				case 'tinytext':
					$maxlength = 256;
					break;
				case 'mediumtext':
					$maxlength = 16777215; // think twice
					break;
				case 'longtext':
					$maxlength = 4294967295; // oops
					break;
				default:
					$maxlength = 65535;
			}
			?>
			<textarea name="<?php echo esc_attr( $this->item_name ); ?>"
					  id="<?php echo esc_attr( $this->item_name ); ?>"
					  class="wpda_data_type_<?php echo esc_attr( $this->data_type ); ?> <?php echo esc_attr( $this->item_class ); ?>"
					  maxlength="<?php echo esc_attr( $maxlength ); ?>"
					  <?php echo esc_attr( $this->show_context_item_events ); ?>
					  style="vertical-align: top;"
			><?php echo $this->show_context_column_value; // phpcs:ignore WordPress.Security.EscapeOutput ?></textarea>
			<?php
		}

		public function is_valid( $pre_insert = false ) {
			// No content limitations
			return true;
		}

	}

}
