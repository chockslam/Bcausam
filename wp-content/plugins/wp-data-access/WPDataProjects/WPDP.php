<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects
 */
namespace WPDataProjects;

use  WPDataAccess\Plugin_Table_Models\WPDP_Page_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDP_Project_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model ;
use  WPDataAccess\Utilities\WPDA_Repository ;
use  WPDataAccess\WPDA ;
use  WPDataProjects\Project\WPDP_Project_Project_View ;
use  WPDataProjects\Project\WPDP_Project_Table_View ;
use  WPDataAccess\Dashboard\WPDA_Dashboard ;
/**
 * Class WPDP
 *
 * Implements Data Projects and Project Templates pages:
 * (1) WPDP_Project_Project_View - To manage Data Projects
 * (2) WPDP_Project_Table_View   - To manage Table Options
 *
 * @author  Peter Schulz
 * @since   2.0.0
 */
class WPDP
{
    /**
     * Menu slug of Data Project page
     */
    const  PAGE_MAIN = 'wpda_wpdp' ;
    /**
     * Menu slug of Project Templates page
     */
    const  PAGE_TEMPLATES = 'wpda_templates' ;
    /**
     * Menu slug taken from URL
     *
     * @var null
     */
    protected  $page = null ;
    /**
     * Templates Page title
     */
    protected  $projects_page_title ;
    /**
     * Projects Page title
     */
    protected  $templates_page_title ;
    /**
     * Data Projects menu
     *
     * @var
     */
    protected  $wpdp_projects_menu ;
    /**
     * Handle to Data Projects view
     *
     * @var
     */
    protected  $wpdp_projects_view ;
    /**
     * Handle to Project Templates view
     *
     * @var
     */
    protected  $wpdp_templates_view ;
    /**
     * Used for static pages
     *
     * @var
     */
    protected  $wpdp_projects_content ;
    /**
     * Arrary containing all project pages
     *
     * @var
     */
    protected  $wpdp_project_menus ;
    /**
     * Array containing all project page views
     *
     * @var
     */
    protected  $wpdp_project_views ;
    protected  $wp_admin_toolbar = array() ;
    protected  $main_menu_slug = null ;
    /**
     * WPDP constructor
     */
    public function __construct( $main_menu_slug = null )
    {
        
        if ( isset( $_REQUEST['page'] ) ) {
            $this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );
            // input var okay.
        }
        
        $this->main_menu_slug = $main_menu_slug;
        $this->projects_page_title = 'Data Projects';
        $this->template_page_title = 'Project Templates';
    }
    
    /**
     * Add menu items
     *
     * Adds Data Projects tool to dashboard menu.
     */
    public function add_menu_items()
    {
        
        if ( current_user_can( 'manage_options' ) ) {
            if ( 'on' === WPDA::get_option( WPDA::OPTION_PLUGIN_HIDE_ADMIN_MENU ) ) {
                // Hide admin menu
                return;
            }
            global  $wpdb ;
            // Add Data Projects menu
            $this->wpdp_projects_menu = add_submenu_page(
                $this->main_menu_slug,
                'Data Projects',
                'Data Projects',
                'manage_options',
                self::PAGE_MAIN,
                [ $this, 'data_projects_page' ]
            );
            if ( $this->page === self::PAGE_MAIN ) {
                $this->wpdp_projects_view = new WPDP_Project_Project_View( [
                    'page_hook_suffix' => $this->wpdp_projects_menu,
                    'table_name'       => $wpdb->prefix . 'wpda_project',
                    'edit_form_class'  => 'WPDataProjects\\Project\\WPDP_Project_Project_Form',
                    'list_table_class' => 'WPDataProjects\\Project\\WPDP_Project_Project_List',
                    'title'            => $this->projects_page_title,
                ] );
            }
            // Add Project Templates menu
            $this->wpdp_projects_menu = add_submenu_page(
                $this->main_menu_slug,
                'Project Templates',
                'Project Templates',
                'manage_options',
                self::PAGE_TEMPLATES,
                [ $this, 'project_templates_page' ]
            );
            if ( $this->page === self::PAGE_TEMPLATES ) {
                $this->wpdp_templates_view = new WPDP_Project_Table_View( [
                    'page_hook_suffix' => $this->wpdp_projects_menu,
                    'table_name'       => $wpdb->prefix . 'wpda_project_table',
                    'list_table_class' => 'WPDataProjects\\Project\\WPDP_Project_Table_List',
                    'edit_form_class'  => 'WPDataProjects\\Project\\WPDP_Project_Table_Form',
                    'title'            => $this->template_page_title,
                    'subtitle'         => '',
                ] );
            }
        }
    
    }
    
    /**
     * Implementation of the Data Projects page
     */
    public function data_projects_page()
    {
        WPDA_Dashboard::add_dashboard();
        // Check for repository tables to prevent dashboard errors.
        
        if ( WPDP_Project_Design_Table_Model::table_exists() && WPDP_Project_Model::table_exists() ) {
            $this->wpdp_projects_view->show();
        } else {
            $this->data_projects_page_not_found();
        }
    
    }
    
    /**
     * Implementation of the Project Templates page
     */
    public function project_templates_page()
    {
        WPDA_Dashboard::add_dashboard();
        // Check for repository tables to prevent dashboard errors.
        
        if ( WPDP_Page_Model::table_exists() ) {
            $this->wpdp_templates_view->show();
        } else {
            $this->data_projects_page_not_found();
        }
    
    }
    
    /**
     * Data Designer repository table not found
     */
    public function data_projects_page_not_found()
    {
        $wpda_repository = new WPDA_Repository();
        $wpda_repository->inform_user();
        ?>
			<div class="wrap">
				<h1 class="wp-heading-inline">
					<span><?php 
        echo  $this->projects_page_title ;
        ?></span>
					<a href="<?php 
        echo  'https://wpdataaccess.com/docs/documentation/data-projects/data-projects-getting-started/' ;
        ?>" target="_blank">
						<span class="dashicons dashicons-editor-help"
							  style="text-decoration:none;vertical-align:top;font-size:30px;">
						</span></a>
				</h1>
				<p>
					<?php 
        echo  __( 'ERROR: Repository table(s) not found!', 'wp-data-access' ) ;
        ?>
				</p>
			</div>
			<?php 
    }
    
    public function project_templates_page_not_found()
    {
        $wpda_repository = new WPDA_Repository();
        $wpda_repository->inform_user();
        ?>
			<div class="wrap">
				<h1 class="wp-heading-inline">
					<span><?php 
        echo  $this->projects_page_title ;
        ?></span>
					<a href="<?php 
        echo  'https://wpdataaccess.com/docs/documentation/project-templates/gettings-started/' ;
        ?>" target="_blank">
						<span class="dashicons dashicons-editor-help"
							  style="text-decoration:none;vertical-align:top;font-size:30px;">
						</span></a>
				</h1>
				<p>
					<?php 
        echo  __( 'ERROR: Repository table(s) not found!', 'wp-data-access' ) ;
        ?>
				</p>
			</div>
			<?php 
    }
    
    /**
     * Add projects to menu
     *
     * Menu items are taken from active projects. Project pages marked as "add to menu" are added to the
     * dashboard menu.
     */
    public function add_projects()
    {
        if ( !is_admin() && 'on' !== WPDA::get_option( WPDA::OPTION_FE_ADD_PROJECTS_TO_TOOLBAR ) ) {
            return;
        }
        if ( !WPDP_Project_Model::table_exists() ) {
            // Missing repository table
            return;
        }
        // Add project Menus.
        global  $wpdb ;
        $project_project_table_name = $wpdb->prefix . 'wpda_project';
        $project_page_table_name = $wpdb->prefix . 'wpda_project_page';
        $query_projects = "select * from {$project_project_table_name} where add_to_menu = 'Yes' order by project_sequence";
        $projects = $wpdb->get_results( $query_projects, 'ARRAY_A' );
        // phpcs:ignore Standard.Category.SniffName.ErrorCode
        if ( sizeof( $projects ) > 0 ) {
            // Check for repository tables to prevent dashboard errors.
            if ( !WPDP_Project_Design_Table_Model::table_exists() || !WPDP_Page_Model::table_exists() ) {
                return;
            }
        }
        foreach ( $projects as $project ) {
            
            if ( null === $project['menu_name'] || '' === trim( $project['menu_name'] ) ) {
                $menu_name = 'UNNAMED';
            } else {
                $menu_name = $project['menu_name'];
            }
            
            $user_roles = WPDA::get_current_user_roles();
            if ( false === $user_roles ) {
                // Cannot determine the user role(s). Not able to show project menus.
                break;
            }
            $query_pages = $wpdb->prepare( " select * from {$project_page_table_name} " . " where project_id = %d " . " and add_to_menu = 'Yes' " . " order by page_sequence", [ $project['project_id'] ] );
            $pages = $wpdb->get_results( $query_pages, 'ARRAY_A' );
            // phpcs:ignore Standard.Category.SniffName.ErrorCode
            $project_menu_shown = false;
            foreach ( $pages as $page ) {
                $user_has_role = false;
                
                if ( '' === $page['page_role'] || null === $page['page_role'] ) {
                    $user_has_role = in_array( 'administrator', $user_roles );
                } else {
                    $user_role_array = explode( ',', $page['page_role'] );
                    foreach ( $user_role_array as $user_role_array_item ) {
                        $user_has_role = in_array( $user_role_array_item, $user_roles );
                        if ( $user_has_role ) {
                            break;
                        }
                    }
                }
                
                if ( $user_has_role ) {
                    
                    if ( is_admin() ) {
                        $page_name = self::PAGE_MAIN . '_' . $page['project_id'] . '_' . $page['page_id'];
                        $page_schema_name = $page['page_schema_name'];
                        $page_table_name = $page['page_table_name'];
                        $page_type = $page['page_type'];
                        
                        if ( !$project_menu_shown ) {
                            $main_page_name = $page_name;
                            add_menu_page(
                                $menu_name,
                                $menu_name,
                                WPDA::get_current_user_capability(),
                                $main_page_name,
                                null,
                                'dashicons-editor-table'
                            );
                            $project_menu_shown = true;
                        }
                        
                        $this->wpdp_project_menus[$page['project_id'] . '_' . $page['page_id']] = add_submenu_page(
                            $main_page_name,
                            $menu_name,
                            $page['page_name'],
                            WPDA::get_current_user_capability(),
                            $page_name,
                            [ $this, 'manage_project_page' ]
                        );
                        
                        if ( $this->page === self::PAGE_MAIN . '_' . $page['project_id'] . '_' . $page['page_id'] ) {
                            
                            if ( 'static' !== $page_type && null !== $page['page_where'] && '' !== $page['page_where'] ) {
                                
                                if ( 'where' === strtolower( substr( str_replace( ' ', '', $page['page_where'] ), 0, 5 ) ) ) {
                                    $where_clause = " {$page['page_where']}";
                                } else {
                                    $where_clause = " where {$page['page_where']} ";
                                }
                                
                                $where_clause = WPDA::substitute_environment_vars( $where_clause );
                            } else {
                                $where_clause = '';
                            }
                            
                            switch ( $page_type ) {
                                case 'static':
                                    $this->wpdp_projects_content[$page['project_id'] . '_' . $page['page_id']] = $page['page_content'];
                                    break;
                                case 'table':
                                    $args = [
                                        'page_hook_suffix' => $this->wpdp_project_menus[$page['project_id'] . '_' . $page['page_id']],
                                        'wpdaschema_name'  => $page_schema_name,
                                        'table_name'       => $page_table_name,
                                        'project_id'       => $page['project_id'],
                                        'page_id'          => $page['page_id'],
                                        'list_table_class' => 'WPDataProjects\\List_Table\\WPDP_List_Table',
                                        'edit_form_class'  => 'WPDataProjects\\Simple_Form\\WPDP_Simple_Form',
                                        'where_clause'     => $where_clause,
                                        'orderby_clause'   => $page['page_orderby'],
                                    ];
                                    
                                    if ( 'view' === $page['page_mode'] ) {
                                        $args['allow_update'] = 'off';
                                        $args['allow_import'] = 'off';
                                    }
                                    
                                    
                                    if ( 'no' === $page['page_allow_insert'] ) {
                                        $args['allow_insert'] = 'off';
                                        $args['allow_import'] = 'off';
                                    }
                                    
                                    if ( 'no' === $page['page_allow_delete'] ) {
                                        $args['allow_delete'] = 'off';
                                    }
                                    
                                    if ( 'only' === $page['page_allow_insert'] ) {
                                        $args['action'] = 'new';
                                        $args['allow_insert'] = 'only';
                                        $args['allow_update'] = 'off';
                                        $args['allow_import'] = 'off';
                                        $args['allow_delete'] = 'off';
                                    }
                                    
                                    if ( 'no' === $page['page_allow_import'] ) {
                                        $args['allow_import'] = 'off';
                                    }
                                    if ( 'no' === $page['page_allow_bulk'] ) {
                                        $args['bulk_actions_enabled'] = false;
                                    }
                                    $this->wpdp_project_views[$page['project_id'] . '_' . $page['page_id']] = new \WPDataProjects\List_Table\WPDP_List_View( $args );
                                    break;
                                case 'parent/child':
                                    $args = [
                                        'page_hook_suffix' => $this->wpdp_project_menus[$page['project_id'] . '_' . $page['page_id']],
                                        'wpdaschema_name'  => $page_schema_name,
                                        'table_name'       => $page_table_name,
                                        'list_table_class' => 'WPDataProjects\\Parent_Child\\WPDP_Parent_List_Table',
                                        'edit_form_class'  => 'WPDataProjects\\Parent_Child\\WPDP_Parent_Form',
                                        'project_id'       => $page['project_id'],
                                        'page_id'          => $page['page_id'],
                                        'where_clause'     => $where_clause,
                                        'orderby_clause'   => $page['page_orderby'],
                                    ];
                                    
                                    if ( 'view' === $page['page_mode'] ) {
                                        $args['allow_update'] = 'off';
                                        $args['allow_import'] = 'off';
                                    }
                                    
                                    
                                    if ( 'no' === $page['page_allow_insert'] ) {
                                        $args['allow_insert'] = 'off';
                                        $args['allow_import'] = 'off';
                                    }
                                    
                                    if ( 'no' === $page['page_allow_delete'] ) {
                                        $args['allow_delete'] = 'off';
                                    }
                                    
                                    if ( 'only' === $page['page_allow_insert'] ) {
                                        $args['action'] = 'new';
                                        $args['allow_insert'] = 'only';
                                        $args['allow_update'] = 'off';
                                        $args['allow_import'] = 'off';
                                        $args['allow_delete'] = 'off';
                                    }
                                    
                                    if ( 'no' === $page['page_allow_import'] ) {
                                        $args['allow_import'] = 'off';
                                    }
                                    if ( 'no' === $page['page_allow_bulk'] ) {
                                        $args['bulk_actions_enabled'] = false;
                                    }
                                    $this->wpdp_project_views[$page['project_id'] . '_' . $page['page_id']] = new \WPDataProjects\Parent_Child\WPDP_Parent_List_View( $args );
                            }
                        }
                    
                    } else {
                        $page_title = $page['page_title'];
                        if ( null === $page_title || '' === trim( $page_title ) ) {
                            $page_title = 'Untitled';
                        }
                        $title = $project['project_description'];
                        if ( null === $title || '' === trim( $title ) ) {
                            $title = $page_title;
                        }
                        $menu = [
                            'menu_id'    => self::PAGE_MAIN . '_' . $page['project_id'] . '_' . $page['page_id'],
                            'menu_name'  => $project['menu_name'],
                            'menu_title' => $title,
                            'page_title' => $page_title,
                        ];
                        
                        if ( !isset( $this->wp_admin_toolbar[$page['project_id']] ) ) {
                            $this->wp_admin_toolbar[$page['project_id']][] = $menu;
                        } else {
                            $this->wp_admin_toolbar[$page['project_id']][] = $menu;
                        }
                    
                    }
                
                }
            }
        }
        if ( !is_admin() ) {
            $this->wpda_add_project_to_toolbar();
        }
    }
    
    protected function wpda_add_project_to_toolbar()
    {
        foreach ( $this->wp_admin_toolbar as $pid => $toolbar ) {
            foreach ( $toolbar as $key => $menu ) {
                if ( 0 === $key ) {
                    $this->additem_to_toolbar( $pid, $menu );
                }
                $this->additem_to_toolbar( $menu['menu_id'], $menu, $pid );
            }
        }
    }
    
    protected function additem_to_toolbar( $pid, $menu, $parent = null )
    {
        global  $wp_admin_bar ;
        $args = [
            'id'     => $pid,
            'title'  => ( null === $parent ? $menu['menu_name'] : $menu['page_title'] ),
            'href'   => admin_url( 'admin.php' ) . '?page=' . $menu['menu_id'],
            'zindex' => '9999',
            'meta'   => [
            'class' => ( null === $parent ? 'wpda-wpdp-toolbar' : '' ),
            'title' => ( null === $parent ? $menu['menu_title'] : $menu['page_title'] ),
        ],
        ];
        if ( null !== $parent ) {
            $args['parent'] = $parent;
        }
        $wp_admin_bar->add_node( $args );
    }
    
    /**
     * Manage project page
     */
    public function manage_project_page()
    {
        $ids = explode( '_', $this->page );
        if ( 4 !== count( $ids ) ) {
            wp_die( __( 'ERROR: Wrong arguments [missing page]', 'wp-data-access' ) );
        }
        $project_id = $ids[2];
        $page_id = $ids[3];
        
        if ( isset( $this->wpdp_project_views[$project_id . '_' . $page_id] ) ) {
            $this->wpdp_project_views[$project_id . '_' . $page_id]->show();
        } else {
            
            if ( isset( $this->wpdp_projects_content[$project_id . '_' . $page_id] ) ) {
                $post_id = $this->wpdp_projects_content[$project_id . '_' . $page_id];
                $post = get_post( $post_id );
                $content = $post->post_content;
                $content = apply_filters( 'the_content', $content );
                $content = str_replace( ']]>', ']]&gt;', $content );
                echo  $content ;
            } else {
                wp_die( __( 'ERROR: Project page initialization failed', 'wp-data-access' ) );
            }
        
        }
    
    }

}