<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package plugin\admin
 */
use  WPDataAccess\Backup\WPDA_Data_Export ;
use  WPDataAccess\CSV_Files\WPDA_CSV_Import ;
use  WPDataAccess\Dashboard\WPDA_Dashboard ;
use  WPDataAccess\List_Table\WPDA_List_View ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_User_Menus_Model ;
use  WPDataAccess\Query_Builder\WPDA_Query_Builder ;
use  WPDataAccess\Settings\WPDA_Settings ;
use  WPDataAccess\Utilities\WPDA_Repository ;
use  WPDataAccess\WPDA ;
use  WPDataProjects\WPDP ;
/**
 * Class WP_Data_Access_Admin
 *
 * Defines admin specific functionality for plugin WP Data Access.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
class WP_Data_Access_Admin
{
    /**
     * Menu slug for main page
     */
    const  PAGE_MAIN = 'wpda' ;
    /**
     * Menu slug for dashboard page
     */
    const  PAGE_DASHBOARD = 'wpda_dashboard' ;
    /**
     * Menu slug for setting page
     */
    const  PAGE_SETTINGS = 'wpdataaccess' ;
    /**
     * Menu slug for explorer page
     */
    const  PAGE_EXPLORER = 'wpda_explorer' ;
    /**
     * Menu slug for query builder page
     */
    const  PAGE_QUERY_BUILDER = 'wpda_query_builder' ;
    /**
     * Menu slug for data publisher page
     */
    const  PAGE_PUBLISHER = 'wpda_publisher' ;
    /**
     * Menu slug for charts page
     */
    const  PAGE_CHARTS = 'wpda_charts' ;
    /**
     * Menu slug for designer page
     */
    const  PAGE_DESIGNER = 'wpda_designer' ;
    /**
     * Menu slug for my tables page
     */
    const  PAGE_MY_TABLES = 'wpda_my_tables' ;
    /**
     * Page hook suffix to Data Explorer page or false
     *
     * @var string|false
     */
    protected  $wpda_data_explorer_menu ;
    /**
     * Page hook suffix to Data Designer page or false
     *
     * @var string|false
     */
    protected  $wpda_data_designer_menu ;
    /**
     * Page hook suffix to Data Publisher page or false
     *
     * @var string|false
     */
    protected  $wpda_data_publisher_menu ;
    /**
     * Page hook suffix to Charts page or false
     *
     * @var string|false
     */
    protected  $wpda_charts_menu ;
    /**
     * Reference to list view for Data Explorer page
     *
     * @var WPDA_List_View
     */
    protected  $wpda_data_explorer_view ;
    /**
     * Reference to list view for Data Designer page
     *
     * @var WPDA_List_View
     */
    protected  $wpda_data_designer_view ;
    /**
     * Reference to list view for Data Publisher page
     *
     * @var WPDA_List_View
     */
    protected  $wpda_data_publisher_view ;
    /**
     * Reference to list view for Charts page
     *
     * @var WPDA_List_View
     */
    protected  $wpda_charts_view ;
    /**
     * Array of page hook suffixes to user defined sub menus
     *
     * @var array
     */
    protected  $wpda_my_table_list_menu = array() ;
    /**
     * Array of list view for user defined sub menus
     *
     * @var array
     */
    protected  $wpda_my_table_list_view = array() ;
    /**
     * Page hook suffix help page or false
     *
     * @var string|false
     */
    protected  $wpda_help ;
    /**
     * Menu slug or null
     *
     * @var null
     */
    protected  $page = null ;
    /**
     * Navigation type
     *
     * @var mixed|string
     */
    protected  $default_dashboard_menu = self::PAGE_DASHBOARD ;
    /**
     * Status loading indicator
     *
     * @var bool
     */
    protected  $loaded_user_main_menu = false ;
    /**
     * Main menu page
     *
     * @var null
     */
    protected  $first_page = null ;
    /**
     * WP_Data_Access_Admin constructor
     *
     * @since   1.0.0
     */
    public function __construct()
    {
        
        if ( isset( $_REQUEST['page'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification
            $this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );
            // phpcs:ignore WordPress.Security.NonceVerification
        }
        
        
        if ( !WPDA_Dashboard::menu_enabled() ) {
            // Dashboard menu is disabled: jump to default page from main menu.
            $this->default_dashboard_menu = WPDA::get_option( WPDA::OPTION_PLUGIN_NAVIGATION_DEFAULT_PAGE );
        } else {
            // With the dashboard menu enabled the main page is always shown by default.
            $this->default_dashboard_menu = self::PAGE_MAIN;
        }
    
    }
    
    /**
     * Add stylesheets to back-end
     *
     * The following stylesheets are added:
     * + Plugin stylesheet
     * + Visual editor stylesheet
     *
     * The plugin stylesheet is used to style the setting forms {@see WPDA_Settings}, simple forms
     * {@see \WPDataAccess\Simple_Form\WPDA_Simple_Form}.
     *
     * @since   1.0.0
     *
     * @see WPDA_Settings
     * @see \WPDataAccess\Simple_Form\WPDA_Simple_Form
     * @see WP_Data_Access_Public
     */
    public function enqueue_styles()
    {
        if ( !WPDA::is_plugin_page( $this->page ) ) {
            // Admin styles are only added to plugin admin pages.
            return;
        }
        wp_enqueue_style( 'wp-jquery-ui-core' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
        wp_enqueue_style( 'wp-jquery-ui-sortable' );
        wp_enqueue_style( 'wp-jquery-ui-tabs' );
        // WPDataAccess CSS.
        wp_enqueue_style(
            'wpdataaccess',
            plugins_url( '../assets/css/wpda_style.css', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        // WPDataAccess dashboard.
        wp_register_style(
            'wpdataaccess_dashboard',
            plugins_url( '../assets/css/wpda_dashboard.css', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        // Add WP Data Projects stylesheet.
        wp_enqueue_style(
            'wpdataprojects',
            plugins_url( '../WPDataProjects/assets/css/wpdp_style.css', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        // Register datetimepicker external library.
        wp_register_style(
            'datetimepicker',
            plugins_url( '../assets/css/jquery.datetimepicker.min.css', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        // Register JQuery DataTables to test publication in the dashboard.
        wp_register_style(
            'jquery_datatables',
            plugins_url( '../assets/css/jquery.dataTables.min.css', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        // Register JQuery DataTables Responsive to test publication in the dashboard.
        wp_register_style(
            'jquery_datatables_responsive',
            plugins_url( '../assets/css/responsive.dataTables.min.css', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        
        if ( self::PAGE_DASHBOARD === $this->page || self::PAGE_QUERY_BUILDER === $this->page || self::PAGE_PUBLISHER === $this->page || self::PAGE_CHARTS === $this->page ) {
            // Load UI smoothness theme.
            wp_enqueue_style(
                'wpda_ui_smoothness',
                plugins_url( '../assets/css/jquery-ui.smoothness.min.css', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        } else {
            // Load UI darkness theme.
            wp_enqueue_style(
                'wpda_ui_darkness',
                plugins_url( '../assets/css/jquery-ui.min.css', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
        
        // SAVING SPACE - According to the plugin guideliness it is allowed to include external fonts:
        // https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/#8-plugins-may-not-send-executable-code-via-third-party-systems .
        // Load material icons.
        wp_enqueue_style(
            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
            'wpda_material_icons',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            array(),
            null
        );
        // Load fontawesome icons.
        wp_enqueue_style(
            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
            'wpda_fontawesome_icons',
            WPDA::CDN_FONTAWESOME . 'fontawesome.min.css',
            array(),
            null
        );
        wp_enqueue_style(
            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
            'wpda_fontawesome_icons_solid',
            WPDA::CDN_FONTAWESOME . 'solid.min.css',
            array(),
            null
        );
        wp_enqueue_style(
            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
            'wpda_fontawesome_icons_regular',
            WPDA::CDN_FONTAWESOME . 'regular.min.css',
            array(),
            null
        );
        if ( self::PAGE_PUBLISHER === $this->page ) {
            wp_register_style(
                'wpda_datatables_default',
                plugins_url( '../assets/css/wpda_datatables_default.css', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
        
        if ( self::PAGE_QUERY_BUILDER === $this->page ) {
            // Add Query Builder resources.
            wp_enqueue_style(
                'wpda_query_builder',
                plugins_url( '../assets/css/wpda_query_builder.css', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
            wp_enqueue_style(
                'wpda_jquery_json_viewer',
                plugins_url( '../assets/css/jquery.json-viewer.css', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
        
        if ( !current_user_can( 'manage_options' ) ) {
            wp_enqueue_style(
                'wpda_non_admin',
                plugins_url( '../assets/css/wpda_non_admin.css', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
    }
    
    /**
     * Remove icons from buttons (if configured)
     *
     * @return void
     */
    public function remove_icons()
    {
        if ( !WPDA::is_plugin_page( $this->page ) ) {
            // Only executed on plugin pages.
            return;
        }
        if ( 'on' === WPDA::get_option( WPDA::OPTION_BE_HIDE_BUTTON_ICONS ) ) {
            echo  '
				<style>
					span.material-icons.wpda_icon_on_button {
						display: none;
					}
				</style>
			' ;
        }
    }
    
    /**
     * Add scripts to back-end
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Public
     */
    public function enqueue_scripts()
    {
        if ( !WPDA::is_plugin_page( $this->page ) ) {
            // Admin styles are only added to plugin admin pages.
            return;
        }
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_script( 'jquery-ui-tabs' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'jquery-ui-tooltip' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        // Register wpda rest api.
        wp_enqueue_script(
            'wpda_rest_api',
            plugins_url( '../assets/js/wpda_rest_api.js', __FILE__ ),
            array( 'wp-api' ),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        // Register wpda admin functions.
        wp_enqueue_script(
            'wpda_admin_scripts',
            plugins_url( '../assets/js/wpda_admin.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        // Register dashboard.
        wp_register_script(
            'wpdataaccess_dashboard',
            plugins_url( '../assets/js/wpda_dashboard.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        wp_localize_script( 'wpdataaccess_dashboard', 'wpda_dashboard_vars', array(
            'wpda_ajaxurl' => admin_url( 'admin-ajax.php' ),
        ) );
        // Add WP Data Projects JS functions.RESEARCH.
        wp_enqueue_script(
            'wpdataprojects',
            plugins_url( '../WPDataProjects/assets/js/wpdp_admin.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        // Register jQuery DataTables to test publication in the dashboard.
        wp_register_script(
            'jquery_datatables',
            plugins_url( '../assets/js/jquery.dataTables.min.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        // Register jQuery DataTables Responsive to test publication in the dashboard.
        wp_register_script(
            'jquery_datatables_responsive',
            plugins_url( '../assets/js/dataTables.responsive.min.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        // Ajax call to WPDA datables implementation to test publication in the dashboard.
        wp_register_script(
            'wpda_datatables',
            plugins_url( '../assets/js/wpda_datatables.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        wp_localize_script( 'wpda_datatables', 'wpda_publication_vars', array(
            'wpda_ajaxurl' => admin_url( 'admin-ajax.php' ),
        ) );
        // Register purl external library.
        wp_register_script(
            'purl',
            plugins_url( '../assets/js/purl.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        // Register notify external library.
        wp_enqueue_script(
            'wpda_notify',
            plugins_url( '../assets/js/notify.min.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        // Register datetimepicker external library.
        wp_register_script(
            'datetimepicker',
            plugins_url( '../assets/js/jquery.datetimepicker.full.min.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        // Register clipboard.js.
        wp_enqueue_script( 'clipboard' );
        if ( self::PAGE_PUBLISHER === $this->page ) {
            
            if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'view', 'new', 'edit' ), true ) ) {
                $json_editing = WPDA::get_option( WPDA::OPTION_DP_JSON_EDITING );
                
                if ( WPDA::OPTION_DP_JSON_EDITING[1] === $json_editing ) {
                    // Register codeEditor to support JSON editing in Data Publisher (table options advanced).
                    $cm_settings['codeEditor'] = wp_enqueue_code_editor( array(
                        'type'       => 'application/json',
                        'codemirror' => array(
                        'autoRefresh' => true,
                    ),
                    ) );
                    wp_localize_script( 'wp-theme-plugin-editor', 'cm_settings', $cm_settings );
                    wp_enqueue_script( 'wp-theme-plugin-editor' );
                }
            
            }
        
        }
        if ( self::PAGE_CHARTS === $this->page ) {
            $this->load_google_charts();
        }
        
        if ( self::PAGE_QUERY_BUILDER === $this->page ) {
            // Add Query Builder resources.
            wp_enqueue_script(
                'wpda_query_builder',
                plugins_url( '../assets/js/wpda_query_builder.js', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
                false
            );
            wp_enqueue_script(
                'wpda_jquery_xml2json',
                plugins_url( '../assets/js/jquery.xml2json.js', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
                false
            );
            wp_enqueue_script(
                'wpda_jquery_json_viewer',
                plugins_url( '../assets/js/jquery.json-viewer.js', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
                false
            );
            // Add codeEditor to query builder.
            $cm_settings['codeEditor'] = wp_enqueue_code_editor( array(
                'type'       => 'text/x-sql',
                'codemirror' => array(
                'mode'            => 'sql',
                'lineNumbers'     => true,
                'autoRefresh'     => true,
                'lineWrapping'    => true,
                'styleActiveLine' => true,
            ),
            ) );
            wp_enqueue_script( 'wp-theme-plugin-editor' );
            wp_localize_script( 'wp-theme-plugin-editor', 'cm_settings', $cm_settings );
            wp_enqueue_style( 'wp-codemirror' );
        }
        
        
        if ( self::PAGE_DASHBOARD === $this->page ) {
            $this->load_google_charts();
            // Load DBMS panels.
            wp_enqueue_script(
                'wpda_dbms',
                plugins_url( '../assets/js/wpda_dbms.js', __FILE__ ),
                array(),
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
                false
            );
            wp_localize_script( 'wpda_dbms', 'wpda_dbms_vars', array(
                'wpda_ajaxurl' => admin_url( 'admin-ajax.php' ),
            ) );
        }
        
        wp_enqueue_media();
    }
    
    /**
     * Load Google Charts resources
     *
     * @return void
     */
    private function load_google_charts()
    {
        // Load Google Charts.
        wp_enqueue_script(
            'wpda_google_charts',
            WPDA::GOOGLE_CHARTS,
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        wp_enqueue_script(
            'wpda_google_charts_fnc',
            plugins_url( '../assets/js/wpda_google_charts.js', __FILE__ ),
            array(),
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION ),
            false
        );
        wp_localize_script( 'wpda_google_charts_fnc', 'wpda_chart_vars', array(
            'wpda_ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'wpda_chartdir' => plugin_dir_url( __FILE__ ) . '../assets/images/google_chart_types/',
            'wpda_premium'  => ( wpda_freemius()->is__premium_only() ? 'true' : 'false' ),
        ) );
    }
    
    /**
     * Hide admin notices
     *
     * @return void
     */
    public function user_admin_notices()
    {
        
        if ( WPDA::is_plugin_page( $this->page ) && 'on' === WPDA::get_option( WPDA::OPTION_PLUGIN_HIDE_NOTICES ) ) {
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'all_admin_notices' );
        }
    
    }
    
    /**
     * Add plugin menu and sub menus
     *
     * Adds the following menu and sub menus to the back-end menu:
     * + WP Data Access
     *   + Data Explorer
     *   + Data Designer
     *   + Data Projects
     *   + Manage Plugin
     *
     * @since   1.0.0
     */
    public function add_menu_items()
    {
        
        if ( current_user_can( 'manage_options' ) ) {
            
            if ( 'on' === WPDA::get_option( WPDA::OPTION_PLUGIN_HIDE_ADMIN_MENU ) ) {
                // Show Data Projects.
                $this->add_data_projects();
                // Hide admin menu.
                return;
            }
            
            // Specific list tables (and forms) can be made available for specific capabilities:
            // managed in method add_menu_my_tables.
            // Main menu and items are only available to admin users (set capability to 'manage_options').
            add_menu_page(
                'WP Data Access',
                'WP Data Access',
                'manage_options',
                $this->default_dashboard_menu,
                null,
                'dashicons-editor-table',
                999999999
            );
            // Add data explorer to WPDA menu.
            $this->wpda_data_explorer_menu = add_submenu_page(
                $this->default_dashboard_menu,
                'WP Data Access',
                'Data Explorer',
                'manage_options',
                self::PAGE_MAIN,
                array( $this, 'data_explorer_page' )
            );
            
            if ( self::PAGE_MAIN === $this->page ) {
                $args = array(
                    'page_hook_suffix' => $this->wpda_data_explorer_menu,
                );
                $this->wpda_data_explorer_view = new WPDA_List_View( $args );
            }
            
            // Add dashboard to menu.
            add_submenu_page(
                $this->default_dashboard_menu,
                'Dashboard',
                'Dashboard',
                'manage_options',
                self::PAGE_DASHBOARD,
                array( $this, 'data_dashboard_page' )
            );
            // Add submenu for Query Builder.
            $this->wpda_data_publisher_menu = add_submenu_page(
                $this->default_dashboard_menu,
                'WP Data Access',
                'Query Builder',
                'manage_options',
                self::PAGE_QUERY_BUILDER,
                array( $this, 'query_builder' )
            );
            // Add submenu for Data Publisher.
            $this->wpda_data_publisher_menu = add_submenu_page(
                $this->default_dashboard_menu,
                'WP Data Access',
                'Data Publisher',
                'manage_options',
                self::PAGE_PUBLISHER,
                array( $this, 'data_publisher_page' )
            );
            if ( self::PAGE_PUBLISHER === $this->page ) {
                $this->wpda_data_publisher_view = new WPDA_List_View( array(
                    'page_hook_suffix' => $this->wpda_data_publisher_menu,
                    'table_name'       => WPDA_Publisher_Model::get_base_table_name(),
                    'list_table_class' => 'WPDataAccess\\Data_Publisher\\WPDA_Publisher_List_Table',
                    'edit_form_class'  => 'WPDataAccess\\Data_Publisher\\WPDA_Publisher_Form',
                ) );
            }
            // Add Data Projects menu.
            $wpdp = new WPDP( $this->default_dashboard_menu );
            $wpdp->add_menu_items();
            // Add data designer to WPDA menu.
            $this->wpda_data_designer_menu = add_submenu_page(
                $this->default_dashboard_menu,
                'WP Data Access',
                'Data Designer',
                'manage_options',
                self::PAGE_DESIGNER,
                array( $this, 'data_designer_page' )
            );
            if ( self::PAGE_DESIGNER === $this->page ) {
                $this->wpda_data_designer_view = new WPDA_List_View( array(
                    'page_hook_suffix' => $this->wpda_data_designer_menu,
                    'table_name'       => WPDA_Design_Table_Model::get_base_table_name(),
                    'list_table_class' => 'WPDataAccess\\Design_Table\\WPDA_Design_Table_List_Table',
                    'edit_form_class'  => 'WPDataAccess\\Design_Table\\WPDA_Design_Table_Form',
                    'subtitle'         => '',
                ) );
            }
        } else {
            $this->grant_access_to_dashboard();
            $this->grant_access_to_data_publications();
        }
        
        $this->add_data_projects();
    }
    
    /**
     * Remove plugin sub menu items (when in dashboard mode)
     *
     * @param mixed $submenu_file Dashboard menu items.
     * @return mixed
     */
    public function wpda_submenu_filter( $submenu_file )
    {
        
        if ( current_user_can( 'manage_options' ) ) {
            
            if ( !WPDA_Dashboard::menu_enabled() ) {
                $hidden_submenus = array(
                    self::PAGE_DASHBOARD,
                    self::PAGE_MAIN,
                    self::PAGE_QUERY_BUILDER,
                    self::PAGE_PUBLISHER,
                    self::PAGE_CHARTS,
                    self::PAGE_DESIGNER,
                    WPDP::PAGE_MAIN,
                    WPDP::PAGE_TEMPLATES,
                    'wpda-account',
                    'wpda-wp-support-forum',
                    'wpda-pricing',
                    $this->default_dashboard_menu
                );
            } else {
                $hidden_submenus = array();
            }
            
            foreach ( $hidden_submenus as $submenu ) {
                remove_submenu_page( $this->default_dashboard_menu, $submenu );
            }
        } else {
            global  $submenu ;
            $submenu[self::PAGE_MAIN][0][2] = self::PAGE_DASHBOARD;
            // phpcs:ignore WordPress.WP.GlobalVariablesOverride
        }
        
        return $submenu_file;
    }
    
    /**
     * Add Data Projects and Project Templates to plugin navigation
     *
     * @return void
     */
    protected function add_data_projects()
    {
        // Add Data Projects.
        $wpdp = new WPDP();
        $wpdp->add_projects();
    }
    
    /**
     * Allow authorized users to access WP Data Access dashboard
     *
     * @return void
     */
    protected function grant_access_to_dashboard()
    {
        // Check user role.
        $user_roles = WPDA::get_current_user_roles();
        if ( false === $user_roles || !is_array( $user_roles ) ) {
            // Cannot determine the user roles (not able to show menus).
            return;
        }
        // Check dashboard role access.
        $dashboard_roles = get_option( \WPDataAccess\Settings\WPDA_Settings_Dashboard::DASHBOARD_ROLES );
        $user_has_role = false;
        foreach ( $user_roles as $user_role ) {
            
            if ( false !== strpos( $dashboard_roles, $user_role ) ) {
                $user_has_role = true;
                break;
            }
        
        }
        // Check dashboard user access.
        $dashboard_users = get_option( \WPDataAccess\Settings\WPDA_Settings_Dashboard::DASHBOARD_USERS );
        $user_has_access = false !== strpos( $dashboard_users, WPDA::get_current_user_login() );
        if ( !$user_has_role && !$user_has_access ) {
            return;
        }
        // User has dashboard access: add menu item.
        $this->create_non_admin_menu( self::PAGE_DASHBOARD );
        add_submenu_page(
            $this->first_page,
            'Dashboard',
            'Dashboard',
            WPDA::get_current_user_capability(),
            self::PAGE_DASHBOARD,
            array( $this, 'data_dashboard_page' )
        );
    }
    
    /**
     * Allow authorized users to access Data Publisher
     *
     * @return void
     */
    protected function grant_access_to_data_publications()
    {
        // Check user role.
        $user_roles = WPDA::get_current_user_roles();
        if ( false === $user_roles || !is_array( $user_roles ) ) {
            // Cannot determine the user roles (not able to show menus).
            return;
        }
        $publication_roles = WPDA::get_option( WPDA::OPTION_DP_PUBLICATION_ROLES );
        if ( '' === $publication_roles || 'administrator' === $publication_roles ) {
            // No access.
            return;
        }
        $user_has_role = false;
        foreach ( $user_roles as $user_role ) {
            if ( false !== stripos( $publication_roles, $user_role ) ) {
                $user_has_role = true;
            }
        }
        if ( !$user_has_role ) {
            // No access.
            return;
        }
        // Grant access to main menu.
        $this->create_non_admin_menu( self::PAGE_PUBLISHER );
        // Add submenu for Data Publisher.
        $this->wpda_data_publisher_menu = add_submenu_page(
            $this->first_page,
            'WP Data Access',
            'Data Publisher',
            WPDA::get_current_user_capability(),
            self::PAGE_PUBLISHER,
            array( $this, 'data_publisher_page' )
        );
        
        if ( self::PAGE_PUBLISHER === $this->page ) {
            global  $wpdb ;
            $this->wpda_data_publisher_view = new WPDA_List_View( array(
                'page_hook_suffix' => $this->wpda_data_publisher_menu,
                'table_name'       => $wpdb->prefix . 'wpda_publisher',
                'list_table_class' => 'WPDataAccess\\Data_Publisher\\WPDA_Publisher_List_Table',
                'edit_form_class'  => 'WPDataAccess\\Data_Publisher\\WPDA_Publisher_Form',
            ) );
        }
    
    }
    
    /**
     * Add dashboard page
     *
     * @return void
     */
    public function data_dashboard_page()
    {
        WPDA_Dashboard::add_dashboard( true );
    }
    
    /**
     * Show data explorer main page
     *
     * Initialization of $this->wpda_data_explorer_view is done earlier in
     * {@see WP_Data_Access_Admin::add_menu_items()} to support screen options. This method just shows the page
     * containing the list table.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Admin::add_menu_items()
     */
    public function data_explorer_page()
    {
        WPDA_Dashboard::add_dashboard();
        
        if ( isset( $_REQUEST['page_action'] ) && 'wpda_backup' === $_REQUEST['page_action'] ) {
            // phpcs:ignore WordPress.Security.NonceVerification
            $this->backup_page();
        } elseif ( isset( $_REQUEST['page_action'] ) && 'wpda_import_csv' === $_REQUEST['page_action'] ) {
            // phpcs:ignore WordPress.Security.NonceVerification
            $this->import_csv();
        } else {
            $this->wpda_data_explorer_view->show();
        }
    
    }
    
    /**
     * Add Query Builder
     *
     * @return void
     */
    public function query_builder()
    {
        WPDA_Dashboard::add_dashboard();
        $query_builder = new WPDA_Query_Builder();
        $query_builder->show();
    }
    
    /**
     * Add CSV page
     *
     * @return void
     */
    public function import_csv()
    {
        $csv_import = new WPDA_CSV_Import();
        $csv_import->show();
    }
    
    /**
     * Show data designer main page
     *
     * Initialization of $this->wpda_data_designer_view is done earlier in
     * {@see WP_Data_Access_Admin::add_menu_items()} to support screen options. This method just shows the page
     * containing the list table.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Admin::add_menu_items()
     */
    public function data_designer_page()
    {
        WPDA_Dashboard::add_dashboard();
        $data_designer_table_found = WPDA_Design_Table_Model::table_exists();
        
        if ( $data_designer_table_found ) {
            $this->wpda_data_designer_view->show();
        } else {
            $this->data_designer_page_not_found();
        }
    
    }
    
    /**
     * Data Designer repository table not found
     */
    public function data_designer_page_not_found()
    {
        WPDA_Dashboard::add_dashboard();
        $wpda_repository = new WPDA_Repository();
        $wpda_repository->inform_user();
        ?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<span>Data Designer</span>
				<a href="https://wpdataaccess.com/docs/documentation/data-designer/getting-started/" target="_blank" class="wpda_tooltip" title="Plugin Help - opens in a new tab or window">
					<span class="dashicons dashicons-editor-help" style="text-decoration:none;vertical-align:top;font-size:30px;">
					</span>
				</a>
			</h1>
			<p>
				<?php 
        echo  __( 'ERROR: Repository table not found!', 'wp-data-access' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>
			</p>
		</div>
		<?php 
    }
    
    /**
     * Show data publisher main page
     */
    public function data_publisher_page()
    {
        if ( current_user_can( 'manage_options' ) ) {
            WPDA_Dashboard::add_dashboard();
        }
        $data_publisher_table_found = WPDA_Publisher_Model::table_exists();
        
        if ( $data_publisher_table_found ) {
            $this->wpda_data_publisher_view->show();
        } else {
            $this->data_publisher_page_not_found();
        }
    
    }
    
    /**
     * Add Charts page
     *
     * @return void
     */
    public function data_charts_page()
    {
    }
    
    /**
     * Data Publisher repository table not found
     */
    public function data_publisher_page_not_found()
    {
        if ( current_user_can( 'manage_options' ) ) {
            WPDA_Dashboard::add_dashboard();
        }
        $wpda_repository = new WPDA_Repository();
        $wpda_repository->inform_user();
        ?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<span>Data Publisher</span>
				<a href="https://wpdataaccess.com/docs/documentation/data-publisher/data-publisher-getting-started/" target="_blank" class="wpda_tooltip" title="Plugin Help - opens in a new tab or window">
					<span class="dashicons dashicons-editor-help" style="text-decoration:none;vertical-align:top;font-size:30px;">
					</span>
				</a>
			</h1>
			<p>
				<?php 
        echo  __( 'ERROR: Repository table not found!', 'wp-data-access' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>
			</p>
		</div>
		<?php 
    }
    
    /**
     * Show data backup main page
     *
     * Calls a page to create automatic backups (in fact data exports) and offers possibilities to restore (in fact
     * data imports).
     *
     * @since   2.0.6
     *
     * @see WPDA_Data_Export::show_wp_cron()
     */
    public function backup_page()
    {
        $wpda_backup = new WPDA_Data_Export();
        
        if ( isset( $_REQUEST['action'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification
            $action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
            // phpcs:ignore WordPress.Security.NonceVerification
            
            if ( 'new' === $action ) {
                $wpda_backup->create_export( 'add' );
            } elseif ( 'add' === $action ) {
                $wpda_backup->wpda_add_cron_job();
            } elseif ( 'remove' === $action ) {
                $wpda_backup->wpda_remove_cron_job();
            } elseif ( 'edit' === $action ) {
                $wpda_backup->create_export( 'update' );
            } elseif ( 'update' === $action ) {
                $wpda_backup->wpda_update_cron_job();
            }
        
        } else {
            $wpda_backup->show_wp_cron();
        }
    
    }
    
    /**
     * Add user defined sub menu
     *
     * WPDA allows users to create sub menu for table lists and simple forms. Sub menus can be added to the WPDA
     * menu or any other (external) menu. A sub menu is added to an external menu via the menu slug. Sub menus are
     * taken from {@see WPDA_User_Menus_Model}.
     *
     * This method is called from the admin_menu action with a lower priority to make sure other menus are available.
     * User defined menu items are added to avalable menus in this method. These can be WPDA menus or external menus
     * as mentioned in the according list table and edit form. WPDA menus are added to menu WP Data Tables. External
     * menus are added to the menu having the menu slug defined by the user.
     *
     * This method does not actually show the list tables! It just creates the menu items. When the user clicks on such
     * a dynamiccally defined menu item, method {@see WP_Data_Access_Admin::my_tables_page()} is called, which takes
     * care of showing the list table.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Admin::my_tables_page()
     * @see WPDA_User_Menus_Model
     */
    public function add_menu_my_tables()
    {
        $menus_shown_to_current_user = array();
        // Add list tables to external menus.
        foreach ( WPDA_User_Menus_Model::list_external_menus() as $menu ) {
            $user_roles = WPDA::get_current_user_roles();
            $user_has_role = false;
            
            if ( '' === $menu->menu_role || null === $menu->menu_role ) {
                $user_has_role = in_array( 'administrator', $user_roles, true );
            } else {
                $user_role_array = explode( ',', $menu->menu_role );
                foreach ( $user_role_array as $user_role_array_item ) {
                    $user_has_role = in_array( $user_role_array_item, $user_roles, true );
                    if ( $user_has_role ) {
                        break;
                    }
                }
            }
            
            if ( $user_has_role ) {
                
                if ( !isset( $menus_shown_to_current_user[$menu->menu_slug . '/' . $menu->menu_name . '/' . $menu->menu_table_name . '/' . $menu->menu_schema_name] ) ) {
                    $menu_slug = self::PAGE_EXPLORER . '_' . $menu->menu_table_name;
                    $menu_index = $menu->menu_table_name;
                    $this->create_non_admin_menu( $menu_slug );
                    $this->wpda_my_table_list_menu[$menu_index] = add_submenu_page(
                        $this->first_page,
                        'WP Data Access : ' . strtoupper( $menu->menu_table_name ),
                        $menu->menu_name,
                        WPDA::get_current_user_capability(),
                        $menu_slug,
                        array( $this, 'my_tables_page' )
                    );
                    $this->wpda_my_table_list_view[$menu_index] = new WPDA_List_View( array(
                        'page_hook_suffix' => $this->wpda_my_table_list_menu[$menu_index],
                        'wpdaschema_name'  => $menu->menu_schema_name,
                        'table_name'       => $menu->menu_table_name,
                    ) );
                    $menus_shown_to_current_user[$menu->menu_slug . '/' . $menu->menu_name . '/' . $menu_index . '/' . $menu->menu_schema_name] = true;
                }
            
            }
        }
    }
    
    /**
     * Show user defined menus
     *
     * A user defined menu that are added to the plugin menu in {@see WP_Data_Access_Admin::add_menu_my_tables()} is
     * shown here. This method is called when the user clicks on the menu item generated in
     * {@see WP_Data_Access_Admin::add_menu_my_tables()}.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Admin::add_menu_my_tables()
     */
    public function my_tables_page()
    {
        // Grab table name from menu slug.
        
        if ( null !== $this->page ) {
            
            if ( strpos( $this->page, self::PAGE_EXPLORER ) !== false ) {
                $table = substr( $this->page, strlen( self::PAGE_EXPLORER . '_' ) );
            } else {
                $table = substr( $this->page, strlen( self::PAGE_MY_TABLES . '_' ) );
            }
            
            // Show list table.
            $this->wpda_my_table_list_view[$table]->show();
        }
    
    }
    
    /**
     * Add main menu for non admin user
     *
     * @param string $first_page First plugin page.
     * @return void
     */
    protected function create_non_admin_menu( $first_page )
    {
        
        if ( !$this->loaded_user_main_menu ) {
            add_menu_page(
                'WP Data Access',
                'WP Data Access',
                WPDA::get_current_user_capability(),
                $first_page,
                function () {
            },
                'dashicons-editor-table',
                999999999
            );
            $this->loaded_user_main_menu = true;
            $this->first_page = $first_page;
        }
    
    }

}