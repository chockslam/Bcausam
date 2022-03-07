<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Settings
 */
namespace WPDataAccess\Settings;

use  WPDataAccess\Connection\WPDADB ;
use  WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist ;
use  WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists ;
use  WPDataAccess\Plugin_Table_Models\WPDA_CSV_Uploads_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Logging_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_User_Menus_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Media_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDP_Page_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDP_Project_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model ;
use  WPDataAccess\Utilities\WPDA_Import ;
use  WPDataAccess\Utilities\WPDA_Message_Box ;
use  WPDataAccess\Utilities\WPDA_Repository ;
use  WPDataAccess\Utilities\WPDA_Restore_Repository ;
use  WPDataAccess\WPDA ;
use  WPDataAccess\Dashboard\WPDA_Dashboard ;
/**
 * Class WPDA_Settings
 *
 * All tabs have the following similar structure:
 * + If form was posted save options (show success or error message)
 * + Read options
 * + Show form with options for selected tab
 *
 * Tabs Back-end Settings, Front-end Settings, Data Backup Settings and Uninstall Settings have reset buttons. When
 * the reset button on a specific tab is clicked, the default values for the settings on that tab are taken from
 * WPDA and stored in $pwdb->options.
 *
 * When the users clicks on tab Manage Repository, the repository is validated and the status of the repository
 * is shown. If the repository has errors, a button is offered to recreate the repository.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
class WPDA_Settings
{
    /**
     * Menu slug of the current page
     *
     * @var string
     */
    protected  $page ;
    /**
     * Available tabs on the page
     *
     * @var array
     */
    protected  $tabs ;
    /**
     * Current tab name
     *
     * @var string
     */
    protected  $current_tab ;
    /**
     * Reference to wpda import object
     *
     * @var WPDA_Import
     */
    protected  $wpda_import ;
    /**
     * URL to help page on p ugin website
     *
     * @var string
     */
    protected  $help_url ;
    /**
     * WPDA_Settings constructor
     *
     * Member $this->tabs is filled in the constructor to support i18n.
     *
     * If a request was send for recreation of the repository, this is done in the constructor. This action must
     * be performed before checking the user menu model, which is part of the constructor as well, necessary to
     * inform the user if any errors were reported.
     *
     * @param $current_tab Current tab label
     * @param $help_url URL help page plugin website
     *
     * @since   1.0.0
     */
    public function __construct( $current_tab, $help_url )
    {
        // Get menu slag of current page.
        
        if ( isset( $_REQUEST['page'] ) ) {
            $this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );
            // input var okay.
        } else {
            // In order to show a list table we need a page.
            wp_die( __( 'ERROR: Wrong arguments [missing page argument]', 'wp-data-access' ) );
        }
        
        $this->current_tab = $current_tab;
        $this->help_url = $help_url;
        // Tabs array is filled in constructor to add i18n.
        $this->tabs = array(
            'plugin'        => 'Plugin',
            'backend'       => 'Back-end',
            'frontend'      => 'Front-end',
            'datatables'    => 'DataTables',
            'datapublisher' => 'Data Publisher',
            'databackup'    => 'Data Backup',
            'uninstall'     => 'Uninstall',
            'repository'    => 'Repository',
            'roles'         => 'Roles',
            'system'        => 'System Info',
        );
    }
    
    /**
     * Show setting page
     *
     * Consists of tabs {@see WPDA_Settings::add_tabs()} and the content of the selected tab
     * {@see WPDA_Settings::add_content()}.
     *
     * @since   1.0.0
     *
     * @see WPDA_Settings::add_tabs()
     * @see WPDA_Settings::add_content()
     */
    public function show()
    {
        ?>
			<div class="wrap">
				<h1>
					<?php 
        echo  __( 'WP Data Access Settings', 'wp-data-access' ) ;
        ?>
					<?php 
        
        if ( WPDA_Dashboard::add_actions_to_page_title() ) {
            ?>
						<a href="<?php 
            echo  $this->help_url ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            ?>" target="_blank" class="wpda_tooltip" title="Plugin Help - open a new tab or window">
						<span class="dashicons dashicons-editor-help"
							style="text-decoration:none;vertical-align:top;font-size:30px;">
							</span></a>
						<?php 
        }
        
        ?>
				</h1>
				<?php 
        $this->add_tabs();
        $this->add_content();
        ?>
			</div>
			<script>
				jQuery(function() {
					jQuery( '.wpda_tooltip' ).tooltip();
				});
			</script>
			<?php 
    }
    
    /**
     * Add tabs to page
     *
     * @since   1.0.0
     */
    protected function add_tabs()
    {
        ?>
			<h2 class="nav-tab-wrapper">
				<?php 
        foreach ( $this->tabs as $tab => $name ) {
            $class = ( $tab === $this->current_tab ? ' nav-tab-active' : '' );
            echo  '<a class="nav-tab' . esc_attr( $class ) . '" href="?page=' . esc_attr( $this->page ) . '&tab=' . esc_attr( $tab ) . '">' . esc_attr( $name ) . '</a>' ;
        }
        ?>
			</h2>
			<?php 
    }

}