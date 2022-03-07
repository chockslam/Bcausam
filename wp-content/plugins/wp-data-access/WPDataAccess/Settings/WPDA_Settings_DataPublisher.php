<?php

namespace WPDataAccess\Settings;

use  WPDataAccess\Utilities\WPDA_Message_Box ;
use  WPDataAccess\WPDA ;
class WPDA_Settings_DataPublisher extends WPDA_Settings
{
    /**
     * Add data publisher tab content
     *
     * See class documentation for flow explanation.
     *
     * @since   2.0.15
     */
    protected function add_content()
    {
        
        if ( isset( $_REQUEST['action'] ) ) {
            $action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
            // input var okay.
            // Security check.
            $wp_nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
            // input var okay.
            if ( !wp_verify_nonce( $wp_nonce, 'wpda-publication-settings-' . WPDA::get_current_user_login() ) ) {
                wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
            }
            
            if ( 'save' === $action ) {
                // Save options.
                
                if ( isset( $_REQUEST['publication_roles'] ) ) {
                    $publication_roles_request = ( isset( $_REQUEST['publication_roles'] ) ? $_REQUEST['publication_roles'] : null );
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
                    
                    if ( is_array( $publication_roles_request ) ) {
                        $publication_roles = sanitize_text_field( wp_unslash( implode( ',', $publication_roles_request ) ) );
                    } else {
                        $publication_roles = '';
                    }
                
                } else {
                    $publication_roles = '';
                }
                
                WPDA::set_option( WPDA::OPTION_DP_PUBLICATION_ROLES, $publication_roles );
                if ( isset( $_REQUEST['json_editing'] ) ) {
                    WPDA::set_option( WPDA::OPTION_DP_JSON_EDITING, sanitize_text_field( wp_unslash( $_REQUEST['json_editing'] ) ) );
                }
                if ( isset( $_REQUEST['publication_style'] ) ) {
                    WPDA::set_option( WPDA::OPTION_DP_STYLE, sanitize_text_field( wp_unslash( $_REQUEST['publication_style'] ) ) );
                }
            } elseif ( 'setdefaults' === $action ) {
                // Set all publication settings back to default.
                WPDA::set_option( WPDA::OPTION_DP_PUBLICATION_ROLES );
                WPDA::set_option( WPDA::OPTION_DP_STYLE );
                WPDA::set_option( WPDA::OPTION_DP_JSON_EDITING );
            }
            
            $msg = new WPDA_Message_Box( array(
                'message_text' => __( 'Settings saved', 'wp-data-access' ),
            ) );
            $msg->box();
        }
        
        global  $wp_roles ;
        $roles = $wp_roles->roles;
        unset( $roles['administrator'] );
        $lov_roles = array();
        foreach ( $wp_roles->roles as $role => $val ) {
            array_push( $lov_roles, $role );
        }
        $publication_roles = WPDA::get_option( WPDA::OPTION_DP_PUBLICATION_ROLES );
        $publication_style = WPDA::get_option( WPDA::OPTION_DP_STYLE );
        $json_editing = WPDA::get_option( WPDA::OPTION_DP_JSON_EDITING );
        ?>
			<form id="wpda_settings_publication" method="post"
				  action="?page=<?php 
        echo  esc_attr( $this->page ) ;
        ?>&tab=datapublisher">
				<table class="wpda-table-settings">
					<?php 
        ?>
					<tr>
						<th><?php 
        echo  __( 'JSON Editing', 'wp-data-access' ) ;
        ?></th>
						<td>
							<label>
								<input type="radio" name="json_editing" value="validate"
									<?php 
        echo  ( 'validate' === $json_editing ? 'checked' : '' ) ;
        ?>
								><?php 
        echo  __( 'Use code editor with JSON validation', 'wp-data-access' ) ;
        ?>
							</label>
							<br/>
							<label>
								<input type="radio" name="json_editing" value="text"
									<?php 
        echo  ( 'text' === $json_editing ? 'checked' : '' ) ;
        ?>
								><?php 
        echo  __( 'Use textarea without JSON validation', 'wp-data-access' ) ;
        ?>
							</label>
						</td>
					</tr>
					<tr>
						<th>Data Publisher Tool Access</th>
						<td><div style="padding-bottom:10px">
								<?php 
        echo  __( 'Select WordPress roles allowed to access Data Publisher', 'wp-data-access' ) ;
        ?>
							</div>
							<select name="publication_roles[]" multiple size="6">
								<?php 
        foreach ( $roles as $key => $role ) {
            $selected = ( false !== strpos( $publication_roles, $key ) ? 'selected' : '' );
            ?>
									<option value="<?php 
            echo  esc_attr( $key ) ;
            ?>" <?php 
            echo  esc_attr( $selected ) ;
            ?>>
										<?php 
            echo  esc_attr( $role['name'] ) ;
            ?>
									</option>
									<?php 
        }
        ?>
							</select>
							<div style="margin-top:10px">
								Administrators have access by default
							</div>
						</td>
					</tr>
					<tr>
						<th><span class="dashicons dashicons-info" style="float:right;font-size:300%;"></span></th>
						<td>
							<span class="dashicons dashicons-yes"></span>
							<?php 
        echo  __( 'Users have readonly access to tables to which you have granted access in Front-end Settings only', 'wp-data-access' ) ;
        ?>
							<br/>
							<span class="dashicons dashicons-yes"></span>
							<?php 
        echo  __( 'Table access is automatically granted to tables used in the Data Publisher', 'wp-data-access' ) ;
        ?>
						</td>
					</tr>
				</table>
				<div class="wpda-table-settings-button">
					<input type="hidden" name="action" value="save"/>
					<input type="submit"
						   value="<?php 
        echo  __( 'Save Publication Settings', 'wp-data-access' ) ;
        ?>"
						   class="button button-primary"/>
					<a href="javascript:void(0)"
					   onclick="if (confirm('<?php 
        echo  __( 'Reset to defaults?', 'wp-data-access' ) ;
        ?>')) {
						   jQuery('input[name=&quot;action&quot;]').val('setdefaults');
						   jQuery('#wpda_settings_publication').trigger('submit')
						   }"
					   class="button">
						<?php 
        echo  __( 'Reset Publication Settings To Defaults', 'wp-data-access' ) ;
        ?>
					</a>
				</div>
				<?php 
        wp_nonce_field( 'wpda-publication-settings-' . WPDA::get_current_user_login(), '_wpnonce', false );
        ?>
			</form>
			<?php 
    }

}