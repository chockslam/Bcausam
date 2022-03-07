<?php

namespace WPDataAccess\Settings;

use  WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist ;
use  WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists ;
use  WPDataAccess\Utilities\WPDA_Message_Box ;
use  WPDataAccess\WPDA ;
class WPDA_Settings_FrontEnd extends WPDA_Settings
{
    /**
     * Available UI themes
     */
    const  UI_THEMES = array(
        'ui-darkness',
        'ui-lightness',
        'swanky-purse',
        'sunny',
        'start',
        'smoothness',
        'black-tie',
        'blitzer',
        'cupertino',
        'dark-hive',
        'dot-luv',
        'eggplant',
        'excite-bike',
        'flick',
        'hot-sneaks',
        'humanity',
        'le-frog',
        'mint-choc',
        'overcast',
        'pepper-grinder',
        'redmond',
        'south-street',
        'trontastic',
        'vader'
    ) ;
    /**
     * Add front-end tab content
     *
     * See class documentation for flow explanation.
     *
     * @since   1.0.0
     */
    protected function add_content()
    {
        global  $wpdb ;
        
        if ( isset( $_REQUEST['database'] ) ) {
            $database = sanitize_text_field( wp_unslash( $_REQUEST['database'] ) );
            // input var okay.
        } else {
            $database = $wpdb->dbname;
        }
        
        $is_wp_database = $database === $wpdb->dbname;
        
        if ( isset( $_REQUEST['action'] ) ) {
            $action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
            // input var okay.
            // Security check.
            $wp_nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
            // input var okay.
            if ( !wp_verify_nonce( $wp_nonce, 'wpda-front-end-settings-' . WPDA::get_current_user_login() ) ) {
                wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
            }
            
            if ( 'save' === $action ) {
                
                if ( $is_wp_database ) {
                    WPDA::set_option( WPDA::OPTION_FE_TABLE_ACCESS, ( isset( $_REQUEST['table_access'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['table_access'] ) ) : null ) );
                } else {
                    update_option( WPDA::FRONTEND_OPTIONNAME_DATABASE_ACCESS . $database, ( isset( $_REQUEST['table_access'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['table_access'] ) ) : null ) );
                }
                
                $table_access_selected_new_value = ( isset( $_REQUEST['table_access_selected'] ) ? WPDA::sanitize_text_field_array( $_REQUEST['table_access_selected'] ) : null );
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
                
                if ( is_array( $table_access_selected_new_value ) ) {
                    // Check the requested table names for sql injection. This is simply done by checking if the table
                    // name exists in our WordPress database.
                    $table_access_selected_new_value_checked = array();
                    foreach ( $table_access_selected_new_value as $key => $value ) {
                        $wpda_dictionary_checks = new WPDA_Dictionary_Exist( $database, $value );
                        
                        if ( $wpda_dictionary_checks->table_exists( false, false ) ) {
                            // Add existing table to list.
                            $table_access_selected_new_value_checked[$key] = $value;
                        } else {
                            // An invalid table name was provided. Might be an sql injection attack or an invalid state.
                            wp_die( __( 'ERROR: Table not found', 'wp-data-access' ) );
                        }
                    
                    }
                } else {
                    $table_access_selected_new_value_checked = '';
                }
                
                
                if ( $is_wp_database ) {
                    WPDA::set_option( WPDA::OPTION_FE_TABLE_ACCESS_SELECTED, $table_access_selected_new_value_checked );
                } else {
                    update_option( WPDA::FRONTEND_OPTIONNAME_DATABASE_SELECTED . $database, $table_access_selected_new_value_checked );
                }
                
                WPDA::set_option( WPDA::OPTION_FE_PAGINATION, ( isset( $_REQUEST['pagination'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['pagination'] ) ) : null ) );
                if ( isset( $_REQUEST['ui_theme'] ) ) {
                    WPDA::set_option( WPDA::WPDA_DT_UI_THEME_DEFAULT, sanitize_text_field( wp_unslash( $_REQUEST['ui_theme'] ) ) );
                }
                WPDA::set_option( WPDA::OPTION_FE_ADD_PROJECTS_TO_TOOLBAR, ( isset( $_REQUEST['add_projects_to_toolbar'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['add_projects_to_toolbar'] ) ) : 'off' ) );
            } elseif ( 'setdefaults' === $action ) {
                // Set all front-end settings back to default
                
                if ( $is_wp_database ) {
                    WPDA::set_option( WPDA::OPTION_FE_TABLE_ACCESS );
                    WPDA::set_option( WPDA::OPTION_FE_TABLE_ACCESS_SELECTED );
                } else {
                    update_option( WPDA::FRONTEND_OPTIONNAME_DATABASE_ACCESS . $database, 'select' );
                    update_option( WPDA::FRONTEND_OPTIONNAME_DATABASE_SELECTED . $database, '' );
                }
                
                WPDA::set_option( WPDA::OPTION_FE_PAGINATION );
                WPDA::set_option( WPDA::WPDA_DT_UI_THEME_DEFAULT );
                WPDA::set_option( WPDA::OPTION_FE_ADD_PROJECTS_TO_TOOLBAR );
            }
            
            $msg = new WPDA_Message_Box( array(
                'message_text' => __( 'Settings saved', 'wp-data-access' ),
            ) );
            $msg->box();
        }
        
        // Get options
        
        if ( $is_wp_database ) {
            $table_access = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS );
            $table_access_selected = WPDA::get_option( WPDA::OPTION_FE_TABLE_ACCESS_SELECTED );
        } else {
            $table_access = get_option( WPDA::FRONTEND_OPTIONNAME_DATABASE_ACCESS . $database );
            if ( false === $table_access ) {
                $table_access = 'select';
            }
            $table_access_selected = get_option( WPDA::FRONTEND_OPTIONNAME_DATABASE_SELECTED . $database );
            if ( false === $table_access_selected ) {
                $table_access_selected = '';
            }
        }
        
        
        if ( is_array( $table_access_selected ) ) {
            // Convert table for simple access.
            $table_access_selected_by_name = array();
            foreach ( $table_access_selected as $key => $value ) {
                $table_access_selected_by_name[$value] = true;
            }
        }
        
        $pagination = WPDA::get_option( WPDA::OPTION_FE_PAGINATION );
        $ui_theme_default = WPDA::get_option( WPDA::WPDA_DT_UI_THEME_DEFAULT );
        $add_projects_to_toolbar = WPDA::get_option( WPDA::OPTION_FE_ADD_PROJECTS_TO_TOOLBAR );
        ?>
			<form id="wpda_settings_frontend" method="post"
				  action="?page=<?php 
        echo  esc_attr( $this->page ) ;
        ?>&tab=frontend">
				<table class="wpda-table-settings">
					<?php 
        ?>
					<tr>
						<th><?php 
        echo  __( 'Default pagination value', 'wp-data-access' ) ;
        ?></th>
						<td>
							<input
								type="number" step="1" min="1" max="999" name="pagination" maxlength="3"
								value="<?php 
        echo  esc_attr( $pagination ) ;
        ?>">
							<div style="padding-top:10px">
								Only for shortcode <strong>wpdadiehard</strong>
							</div>
						</td>
					</tr>
					<tr>
						<th><?php 
        echo  __( 'Table access', 'wp-data-access' ) ;
        ?></th>
						<td>
							<select name="database" id="schema_name">
								<?php 
        $schema_names = WPDA_Dictionary_Lists::get_db_schemas();
        foreach ( $schema_names as $schema_name ) {
            $selected = ( $database === $schema_name['schema_name'] ? ' selected' : '' );
            echo  "<option value='{$schema_name['schema_name']}'{$selected}>{$schema_name['schema_name']}</option>" ;
            // phpcs:ignore WordPress.Security.EscapeOutput
        }
        ?>
							</select>
							<br/><br/>
							<label>
								<input
									type="radio"
									name="table_access"
									value="show"
									<?php 
        echo  ( 'show' === $table_access ? 'checked' : '' ) ;
        ?>
								><?php 
        echo  ( $is_wp_database ? __( 'Show WordPress tables', 'wp-data-access' ) : __( 'Show all tables', 'wp-data-access' ) ) ;
        ?>
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
										<?php 
            echo  ( 'hide' === $table_access ? 'checked' : '' ) ;
            ?>
									><?php 
            echo  __( 'Hide WordPress tables', 'wp-data-access' ) ;
            ?>
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
									<?php 
        echo  ( 'select' === $table_access ? 'checked' : '' ) ;
        ?>
								><?php 
        echo  __( 'Show only selected tables', 'wp-data-access' ) ;
        ?>
							</label>
							<div id="tables_selected" <?php 
        echo  ( 'select' === $table_access ? '' : 'style="display:none"' ) ;
        ?>>
								<br/>
								<select name="table_access_selected[]" multiple size="10">
									<?php 
        $tables = WPDA_Dictionary_Lists::get_tables( true, $database );
        foreach ( $tables as $table ) {
            $table_name = $table['table_name'];
            ?>
										<option value="<?php 
            echo  esc_attr( $table_name ) ;
            ?>" <?php 
            echo  ( isset( $table_access_selected_by_name[$table_name] ) ? 'selected' : '' ) ;
            ?>><?php 
            echo  esc_attr( $table_name ) ;
            ?></option>
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
										window.location = '?page=<?php 
        echo  esc_attr( $this->page ) ;
        ?>&tab=frontend&database=' + jQuery(this).val();
									});
								});
							</script>
						</td>
					</tr>
					<tr>
						<th><?php 
        echo  __( 'Admin toolbar', 'wp-data-access' ) ;
        ?></th>
						<td>
							<label>
								<input type="checkbox" name="add_projects_to_toolbar"
									<?php 
        echo  ( 'on' === $add_projects_to_toolbar ? 'checked' : '' ) ;
        ?>
								/>
								<?php 
        echo  __( 'Add projects to toolbar', 'wp-data-access' ) ;
        ?>
							</label>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php 
        echo  __( 'Save Front-end Settings', 'wp-data-access' ) ;
        ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php 
        echo  __( 'Reset to defaults?', 'wp-data-access' ) ;
        ?>')) {
						   jQuery('input[name=&quot;action&quot;]').val('setdefaults');
						   jQuery('#wpda_settings_frontend').trigger('submit')
						   }"
					   class="button">
						<?php 
        echo  __( 'Reset Front-end Settings To Defaults', 'wp-data-access' ) ;
        ?>
					</a>
				</div>
				<?php 
        wp_nonce_field( 'wpda-front-end-settings-' . WPDA::get_current_user_login(), '_wpnonce', false );
        ?>
			</form>

			<?php 
    }

}