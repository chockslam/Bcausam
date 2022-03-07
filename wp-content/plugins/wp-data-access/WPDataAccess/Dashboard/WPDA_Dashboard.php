<?php

namespace WPDataAccess\Dashboard;

use  WP_Data_Access_Admin ;
use  WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDP_Project_Model ;
use  WPDataAccess\Premium\WPDAPRO_Dashboard\WPDAPRO_Dashboard ;
use  WPDataAccess\Premium\WPDAPRO_Dashboard\WPDAPRO_Widget_Project ;
use  WPDataAccess\Premium\WPDAPRO_Data_Forms\WPDAPRO_Data_Forms_Init ;
use  WPDataAccess\Utilities\WPDA_Message_Box ;
use  WPDataAccess\WPDA ;
use  WPDataAccess\Connection\WPDADB ;
use  WPDataProjects\WPDP ;
use  WPDataAccess\Settings\WPDA_Settings_Dashboard ;
class WPDA_Dashboard
{
    const  DASHBOARD_SAVE = 'WPDA_DASHBOARD_SAVE' ;
    const  USER_DASHBOARD = 'wpda-user-dashboard-widgets' ;
    const  USER_NEW_MESSAGE = 'wpda_new_dashboard_message' ;
    protected  $dashboards = null ;
    protected  $number_of_columns = 2 ;
    protected  $wp_nonce_add = null ;
    protected  $wp_nonce_save = null ;
    protected  $dashboard = null ;
    protected  $dashboard_widgets = null ;
    protected  $dashboard_positions = null ;
    protected  $shared_dashboards = array() ;
    protected  $shared_access = array() ;
    protected  $shared_locked = array() ;
    protected  $locked_dashboards = array() ;
    protected  $cannot_create_dashboard = false ;
    protected  $tabs = array( 'Default' ) ;
    protected  $tab = 'Default' ;
    protected  $tab_name = '' ;
    protected  $tab_index = 0 ;
    protected  $hide_default_tab = false ;
    protected  $is_locked = false ;
    protected  $message = null ;
    protected  $message_type = null ;
    public function __construct( $widget_mode = false )
    {
        
        if ( $widget_mode ) {
            // Prepare nonces
            $this->wp_nonce_add = wp_create_nonce( WPDA_Widget::WIDGET_ADD . WPDA::get_current_user_login() );
            $this->wp_nonce_save = wp_create_nonce( static::DASHBOARD_SAVE . WPDA::get_current_user_login() );
            // Load Data Publisher resources
            \WPDataAccess\Data_Tables\WPDA_Data_Tables::enqueue_styles_and_script();
        }
        
        // Start with empty array
        if ( $widget_mode ) {
            update_user_meta( WPDA::get_current_user_id(), self::USER_DASHBOARD, array() );
        }
    }
    
    protected static function navigation_enabled( $navigation_type )
    {
        return in_array( WPDA::get_option( WPDA::OPTION_PLUGIN_NAVIGATION ), array( 'both', $navigation_type ) );
    }
    
    public static function dashboard_enabled()
    {
        return self::navigation_enabled( 'dashboard' );
    }
    
    public static function add_actions_to_page_title()
    {
        return !self::dashboard_enabled();
    }
    
    public static function menu_enabled()
    {
        return self::navigation_enabled( 'menu' );
    }
    
    public static function add_dashboard( $widget_mode = false )
    {
        $dashboard = new WPDA_Dashboard( $widget_mode );
        $dashboard->dashboard();
    }
    
    public function dashboard()
    {
        wp_enqueue_style( 'wpdataaccess_dashboard' );
        wp_enqueue_script( 'wpdataaccess_dashboard' );
        
        if ( current_user_can( 'manage_options' ) && self::dashboard_enabled() ) {
            $this->dashboard_default();
            $this->dashboard_mobile();
        }
        
        if ( isset( $_REQUEST['page'] ) ) {
            switch ( $_REQUEST['page'] ) {
                case 'wpda_dashboard':
                    $this->toolbar();
                    $this->add_forms();
                    $this->tabs();
                    $this->columns();
                    $this->add_panels();
                    $this->dashboard_js();
                    break;
                case WP_Data_Access_Admin::PAGE_MAIN:
                    if ( self::dashboard_enabled() ) {
                        
                        if ( !isset( $_REQUEST['page_action'] ) ) {
                            
                            if ( !isset( $_REQUEST['table_name'] ) ) {
                                $this->toolbar_wpda();
                            } else {
                                
                                if ( !isset( $_REQUEST['action'] ) || 'new' !== $_REQUEST['action'] && 'edit' !== $_REQUEST['action'] ) {
                                    $this->toolbar_wpda_table();
                                } else {
                                    $this->toolbar_wpda_row();
                                }
                            
                            }
                        
                        } elseif ( 'wpda_backup' === $_REQUEST['page_action'] && !isset( $_REQUEST['action'] ) ) {
                            $this->toolbar_backup();
                        } elseif ( 'wpda_import_csv' === $_REQUEST['page_action'] ) {
                            $this->toolbar_import_csv();
                        }
                    
                    }
                    break;
                case WP_Data_Access_Admin::PAGE_QUERY_BUILDER:
                    if ( self::dashboard_enabled() ) {
                        $this->toolbar_sql();
                    }
                    break;
                case WP_Data_Access_Admin::PAGE_DESIGNER:
                    if ( self::dashboard_enabled() && (!isset( $_REQUEST['action'] ) || 'new' !== $_REQUEST['action'] && 'edit' !== $_REQUEST['action']) ) {
                        $this->toolbar_designer();
                    }
                    break;
                case WP_Data_Access_Admin::PAGE_PUBLISHER:
                    if ( self::dashboard_enabled() && (!isset( $_REQUEST['action'] ) || 'new' !== $_REQUEST['action'] && 'edit' !== $_REQUEST['action']) ) {
                        $this->toolbar_publisher();
                    }
                    break;
                case WP_Data_Access_Admin::PAGE_CHARTS:
                    if ( self::dashboard_enabled() ) {
                        $this->toolbar_charts();
                    }
                    break;
                case WPDP::PAGE_MAIN:
                    if ( self::dashboard_enabled() && (!isset( $_REQUEST['action'] ) || 'new' !== $_REQUEST['action'] && 'edit' !== $_REQUEST['action']) ) {
                        $this->toolbar_projects();
                    }
                    break;
                case WPDP::PAGE_TEMPLATES:
                    if ( self::dashboard_enabled() && (!isset( $_REQUEST['action'] ) || 'new' !== $_REQUEST['action'] && 'edit' !== $_REQUEST['action']) ) {
                        $this->toolbar_templates();
                    }
                    break;
            }
        }
    }
    
    protected function get_help_url()
    {
        $help_root = 'https://wpdataaccess.com/docs/documentation/';
        switch ( $_REQUEST['page'] ) {
            case \WP_Data_Access_Admin::PAGE_MAIN:
                $help_url = $help_root . 'data-explorer/getting-started/';
                break;
            case \WP_Data_Access_Admin::PAGE_QUERY_BUILDER:
                $help_url = $help_root . 'query-builder/getting-started/';
                break;
            case \WP_Data_Access_Admin::PAGE_DESIGNER:
                $help_url = $help_root . 'data-designer/getting-started/';
                break;
            case \WP_Data_Access_Admin::PAGE_PUBLISHER:
                $help_url = $help_root . 'data-publisher/data-publisher-getting-started/';
                break;
            case WPDP::PAGE_MAIN:
                $help_url = $help_root . 'data-projects/data-projects-getting-started/';
                break;
            case WPDP::PAGE_TEMPLATES:
                $help_url = $help_root . 'project-templates/getting-started/';
                break;
            case 'wpdataaccess':
                $help_url = $help_root . 'plugin-settings/getting-started/';
                break;
            default:
                $help_url = $help_root . 'getting-started/overview/';
        }
        return $help_url;
    }
    
    protected function dashboard_default()
    {
        ?>
			<div id="wpda-dashboard" style="display:none">
				<div class="wpda-dashboard">
					<div class="wpda-dashboard-group wpda-dashboard-group-administration">
						<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda" title="Data Explorer">
							<div class="fas fa-database"></div>
							<div class="label">Explorer</div>
						</a>
						<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_query_builder" title="Query Builder">
							<div class="fas fa-tools"></div>
							<div class="label">Queries</div>
						</a>
						<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_designer" title="Data Designer">
							<div class="fas fa-drafting-compass"></div>
							<div class="label">Designer</div>
						</a>
						<div class="subject">Database management</div>
					</div>
					<div class="wpda-dashboard-group wpda-dashboard-group-publisher">
						<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_dashboard" title="Analyse, report and share">
							<div class="fas fa-tachometer-alt"></div>
							<div class="label">Dashboard</div>
						</a>
						<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_publisher" title="Data Publisher">
							<div class="fas fa-address-card"></div>
							<div class="label">Publications</div>
						</a>
						<?php 
        ?>
						<div class="subject">Data publishing</div>
					</div>
					<div class="wpda-dashboard-group wpda-dashboard-group-projects">
						<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_wpdp" title="Data Projects">
							<div class="fas fa-magic"></div>
							<div class="label">Projects</div>
						</a>
						<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_templates" title="Project Templates">
							<div class="fas fa-desktop"></div>
							<div class="label">Templates</div>
						</a>
						<div class="subject">Data projects</div>
					</div>
					<div class="wpda-dashboard-group wpda-dashboard-group-settings">
						<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
        echo  admin_url( 'options-general.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpdataaccess" title="Plugin Settings">
							<div class="fas fa-cog"></div>
							<div class="label">Settings</div>
						</a>
						<?php 
        
        if ( wpda_freemius()->is_registered() ) {
            ?>
							<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
            echo  admin_url( 'admin.php' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            ?>?page=wpda-account" title="Manage Account">
								<div class="fas fa-user"></div>
								<div class="label">Account</div>
							</a>
							<?php 
        }
        
        ?>
						<a class="wpda-dashboard-item wpda_tooltip_icons" target="_blank" href="https://wpdataaccess.com/pricing/" title="All features, online pricing, licensing and ordering">
							<div class="fas fa-hand-holding-usd"></div>
							<div class="label">Pricing</div>
						</a>
						<?php 
        
        if ( wpda_freemius()->is_not_paying() ) {
            ?>
							<a class="wpda-dashboard-item wpda_tooltip_icons" href="<?php 
            echo  admin_url( 'admin.php' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            ?>?page=wpda-pricing" title="Upgrade">
								<div class="fas fa-gem"></div>
								<div class="label">Upgrade</div>
							</a>
							<?php 
        }
        
        ?>
						<div class="subject">Manage</div>
					</div>
					<div class="wpda-dashboard-group wpda-dashboard-group-support">
						<a class="wpda-dashboard-item wpda_tooltip_icons" target="_blank" href="<?php 
        echo  $this->get_help_url() ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>" title="Online Help and Documentation">
							<div class="fas fa-question"></div>
							<div class="label">Docs</div>
						</a>
						<a class="wpda-dashboard-item wpda_tooltip_icons" target="_blank" href="https://wordpress.org/support/plugin/wp-data-access/" title="Public Support Forum">
							<div class="fas fa-life-ring"></div>
							<div class="label">Forum</div>
						</a>
						<?php 
        ?>
						<div class="subject">Support</div>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function dashboard_mobile()
    {
        ?>
			<div id="wpda-dashboard-mobile" style="display:none">
				<div id="wpda-dashboard-drop-down">
					<div class="wpda_nav_toggle" onclick="toggleMenu()"><i class="fas fa-bars"></i></div>
					<div class="wpda_nav_title">WP Data Access</div>
				</div>
				<ul>
					<li class="menu-item"><a href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda"><i class="fas fa-database"></i> Data Explorer</a></li>
					<li class="menu-item"><a href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_query_builder"><i class="fas fa-tools"></i> Query Builder</a></li>
					<li class="menu-item wpda-separator"><a href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_designer"><i class="fas fa-drafting-compass"></i> Data Designer</a></li>
					<li class="menu-item"><a href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
					<li class="menu-item wpda-separator"><a href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_publisher"><i class="fas fa-address-card"></i> Data Publisher</a></li>
					<li class="menu-item"><a href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_wpdp"><i class="fas fa-magic"></i> Data Projects</a></li>
					<li class="menu-item wpda-separator"><a href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda_templates"><i class="fas fa-desktop"></i> Project Templates</a></li>
					<li class="menu-item"><a href="<?php 
        echo  admin_url( 'options-general.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpdataaccess"><i class="fas fa-cog"></i> Settings</a></li>
					<li class="menu-item"><a href="<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=wpda-account"><i class="fas fa-user"></i> Account</a></li>
					<?php 
        $menufound = false;
        
        if ( self::menu_enabled() ) {
            global  $submenu ;
            if ( isset( $submenu[WPDA::get_option( WPDA::OPTION_PLUGIN_NAVIGATION_DEFAULT_PAGE )] ) ) {
                foreach ( $submenu[WPDA::get_option( WPDA::OPTION_PLUGIN_NAVIGATION_DEFAULT_PAGE )] as $pluginmenu ) {
                    
                    if ( 'wpda-pricing' === $pluginmenu[2] ) {
                        $menufound = true;
                        break;
                    }
                
                }
            }
        } else {
            $menufound = true;
        }
        
        ?>
					<li class="menu-item <?php 
        echo  ( $menufound ? '' : 'wpda-separator' ) ;
        ?>"><a href="https://wpdataaccess.com/pricing/" target="_blank"><i class="fas fa-hand-holding-usd"></i> Pricing</a></li>
					<?php 
        
        if ( $menufound ) {
            ?>
						<li class="menu-item wpda-separator"><a href="<?php 
            echo  admin_url( 'admin.php' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            ?>?page=wpda-pricing"><i class="fas fa-gem"></i> Upgrade</a></li>
						<?php 
        }
        
        ?>
					<li class="menu-item"><a target="_blank" href="https://wpdataaccess.com/docs/documentation/getting-started/overview/"><i class="fas fa-question"></i> Online Documentation</a></li>
					<li class="menu-item"><a target="_blank" href="https://wordpress.org/support/plugin/wp-data-access/"><i class="fas fa-life-ring"></i> Support Forum</a></li>
					<?php 
        ?>
				</ul>
			</div>
			<?php 
    }
    
    protected function tabs()
    {
    }
    
    protected function toolbar()
    {
        if ( $this->cannot_create_dashboard ) {
            return;
        }
        $remove_columns_message = __( 'Remove columns outside of range? Widgets will no longer be available!', 'wp-data-access' );
        ?>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div style="white-space:nowrap">
					<div>
					<?php 
        if ( current_user_can( 'manage_options' ) ) {
            ?>
							<div>
								<i class="fas fa-plus-circle wpda_tooltip" title="Create widget" onclick="addPanel()"></i>
								<br/>
								Create widget
							</div>
							<?php 
        }
        ?>
					</div>
				</div>
				<div style="font-size:medium;text-align:center">
					<?php 
        if ( current_user_can( 'manage_options' ) && 'removed' !== get_user_meta( WPDA::get_current_user_id(), self::USER_NEW_MESSAGE, true ) ) {
            ?>
						<div id="wpda_delete_new_dashboard_message_container" style="font-weight:bold">
							<span id="wpda_delete_new_dashboard_message"
								  class="dashicons dashicons-trash wpda_tooltip"
								  style="cursor:pointer;vertical-align:bottom"
								  title="Permanently remove message and link from toolbar"
							></span>
							<span>New to dashboards?</span>
							<span>
								<a href="https://wpdataaccess.com/docs/documentation/dashboards-and-widgets/getting-started/"
								   target="_blank"
								   class="wpda_tooltip"
								   title="Learn how to share your data anywhere"
								>
									Watch the tutorial...
								</a>
							</span>
						</div>
						<?php 
        }
        ?>
				</div>
				<div style="white-space:nowrap">
					<div>
						<div>
							<i class="fas fa-dice-one wpda_tooltip" title="One column"></i>
							<br/>
							1 column
						</div><div>
							<i class="fas fa-dice-two wpda_tooltip" title="Two columns"></i>
							<br/>
							2 columns
						</div><div>
							<i class="fas fa-dice-three wpda_tooltip" title="Three columns"></i>
							<br/>
							3 columns
						</div><div>
							<i class="fas fa-dice-four wpda_tooltip" title="Four columns"></i>
							<br/>
							4 columns
						</div>
					</div>
				</div>
			</div>
			<?php 
        if ( wpda_freemius()->is_free_plan() ) {
            ?>
				<div class="wpda_dashboard_free_message">
					<table>
						<tr>
							<td style="text-align:left">
								The free version of WP Data Access has limited dashboard support.
								Data Widgets can be added and moved, but not saved or shared.
								Update to premium to analyse, report and share your data.
							</td>
							<td style="white-space:nowrap">
								<a href="https://wpdataaccess.com/pricing/" target="_blank" class="button button-primary">UPGRADE TO PREMIUM</a>
								<a href="https://wpdataaccess.com/docs/documentation/dashboards-and-widgets/getting-started/" target="_blank" class="button">READ MORE</a>
							</td>
						</tr>
					</table>
				</div>
				<?php 
        }
        ?>
			<script type="application/javascript">
				function manageTabs() {
					closePanel();
					jQuery("#wpda-manage-tabs").show();
				}
				function noColumns() {
					return jQuery(".wpda-dashboard-column").length;
				}
				function addColumns(colNum) {
					if (noColumns()<colNum) {
						for (var i=noColumns()+1; i<=colNum; i++) {
							jQuery("#wpda-dashboard-content").append('<div id="wpda-dashboard-column-' + i + '" class="wpda-dashboard-column wpda-dashboard-column-' + i + '"></div>');
						}
						refreshPanels(colNum);
						resetColumnSelection();
						makeSortable();
						saveDashBoard();
					}
				}
				function removeColumns(colNum) {
					if (noColumns()>colNum) {
						if (confirm("<?php 
        echo  esc_html( $remove_columns_message ) ;
        ?>")) {
							noCols = noColumns();
							for (var i=colNum+1; i<=noCols; i++) {
								jQuery("#wpda-dashboard-column-" + i).remove();
							}
							refreshPanels(colNum);
							makeSortable();
							saveDashBoard();
						}
					}
				}
				function refreshPanels(colNum) {
					for (var i=1; i<=4; i++) {
						jQuery(".wpda-dashboard-column").removeClass("wpda-dashboard-column-" + i);
					}
					jQuery(".wpda-dashboard-column").addClass("wpda-dashboard-column-" + colNum);
					refreshAllPanels();
				}
				jQuery(function(){
					jQuery("#wpda_delete_new_dashboard_message").on("click", function() {
						if (confirm("Remove message and link? This cannot be undone!")) {
							jQuery.ajax({
								type: "POST",
								url: wpda_ajaxurl + "?action=wpda_remove_new_dashboard_message",
								data: {
									wp_nonce: wpda_wpnonce_save
								}
							}).done(
								function(data) {
									if (data==="OK") {
										jQuery("#wpda_delete_new_dashboard_message_container").hide();
									}
								}
							);
						}
					});
					jQuery("#wpda-dashboard-toolbar .fa-dice-one").on("click", function() {
						removeColumns(1);
					});
					jQuery("#wpda-dashboard-toolbar .fa-dice-two").on("click", function() {
						removeColumns(2);
						addColumns(2);
					});
					jQuery("#wpda-dashboard-toolbar .fa-dice-three").on("click", function() {
						removeColumns(3);
						addColumns(3);
					});
					jQuery("#wpda-dashboard-toolbar .fa-dice-four").on("click", function() {
						addColumns(4);
					});
				});
			</script>
			<?php 
    }
    
    protected function toolbar_wpda()
    {
        $url = admin_url( 'admin.php?action=wpda_show_whats_new' );
        
        if ( 'off' !== WPDA::get_option( WPDA::OPTION_WPDA_SHOW_WHATS_NEW ) ) {
            $color = 'style="color:#a00"';
            $url .= '&whats_new=off';
        } else {
            $color = '';
        }
        
        ?>
			<form id="wpda_new_design" style="display: none" method="post" action="?page=<?php 
        echo  esc_attr( WP_Data_Access_Admin::PAGE_DESIGNER ) ;
        ?>">
				<input type="hidden" name="action" value="create_table">
				<input type="hidden" name="caller" value="dataexplorer">
			</form>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i class="fas fa-plus-circle wpda_tooltip" title="Create new table design" onclick="jQuery('#wpda_new_design').submit()"></i>
							<br/>
							Create table
						</div><div>
							<i class="fas fa-bullhorn wpda_tooltip" title="What's new?" <?php 
        echo  esc_attr( $color ) ;
        ?> onclick="window.open('<?php 
        echo  esc_url( $url ) ;
        ?>')"></i>
							<br/>
							What's new?
						</div>
					</div><div>
						<div>
							<i class="fas fa-file-code wpda_tooltip" title="Import and execute SQL script files" onclick="jQuery('#upload_file_container_multi').show()"></i>
							<br/>
							Import SQL files
						</div><div>
							<i class="fas fa-file-csv wpda_tooltip" title="Import CSV files" onclick="window.location.href='<?php 
        echo  admin_url( 'admin.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>?page=<?php 
        echo  esc_attr( WP_Data_Access_Admin::PAGE_MAIN ) ;
        ?>&page_action=wpda_import_csv'"></i>
							<br/>
							Import CSV files
						</div><div>
							<i id="wpda_toolbar_icon_go_backup" class="fas fa-file-archive wpda_tooltip" title="Data Backup - unattended exports"></i>
							<br/>
							Data Backup
						</div>
					</div><div>
						<div>
							<i id="wpda_toolbar_icon_add_database" class="fas fa-database wpda_tooltip" title="Add remote database or create local database" onclick="jQuery('#wpda_db_container').show(); jQuery('#local_database').focus();"></i>
							<br/>
							Add database
						</div>
						<?php 
        
        if ( false && wpda_freemius()->is__premium_only() ) {
            ?>
							<div>
								<i class="fas fa-globe-americas wpda_tooltip" title="Premium data services configuration" onclick="window.location.href='<?php 
            echo  admin_url( 'admin.php' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            ?>?page=<?php 
            echo  esc_attr( WP_Data_Access_Admin::PAGE_MAIN ) ;
            ?>&page_action=wpda_premium_data_services'"></i>
								<br/>
								Premium data services
							</div>
							<?php 
        }
        
        ?>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function toolbar_wpda_table()
    {
        ?>
			<form id="wpda_new_row" style="display: none" method="post" action="?page=<?php 
        echo  esc_attr( WP_Data_Access_Admin::PAGE_MAIN ) ;
        ?>">
				<?php 
        
        if ( isset( $_REQUEST['wpdaschema_name'] ) ) {
            $schema_name = esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['wpdaschema_name'] ) ) );
            echo  "<input type='hidden' name='wpdaschema_name' value='{$schema_name}'>" ;
            // phpcs:ignore WordPress.Security.EscapeOutput
        }
        
        ?>
				<input type="hidden" id="wpda_new_row_table_name" name="table_name" value="">
				<input type="hidden" name="action" value="new">
			</form>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i id="wpda_toolbar_icon_add_row" class="fas fa-plus-circle wpda_tooltip" title="Add new row to table"></i>
							<br/>
							Add row
						</div><div>
							<i class="fas fa-code wpda_tooltip" title="Allows only imports into table authors" onclick="jQuery('#upload_file_container').show()"></i>
							<br/>
							Import rows
						</div>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function toolbar_wpda_row()
    {
        ?>
			<form id="wpda_new_row" style="display: none" method="post" action="?page=<?php 
        echo  esc_attr( WP_Data_Access_Admin::PAGE_MAIN ) ;
        ?>">
				<?php 
        
        if ( isset( $_REQUEST['wpdaschema_name'] ) ) {
            $schema_name = esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['wpdaschema_name'] ) ) );
            echo  "<input type='hidden' name='wpdaschema_name' value='{$schema_name}'>" ;
            // phpcs:ignore WordPress.Security.EscapeOutput
        }
        
        ?>
				<input type="hidden" id="wpda_new_row_table_name" name="table_name" value="">
				<input type="hidden" name="action" value="new">
			</form>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i id="wpda_toolbar_icon_add_row" class="fas fa-plus-circle wpda_tooltip" title="Add new row to table"></i>
							<br/>
							Add row
						</div>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function toolbar_backup()
    {
        ?>
			<form id="wpda_new_backup" style="display: none" method="post" action="?page=<?php 
        echo  esc_attr( WP_Data_Access_Admin::PAGE_MAIN ) ;
        ?>&page_action=wpda_backup">
				<input type="hidden" id="wpda_new_backup_wpdaschema_name" name="wpdaschema_name" value="">
				<input type="hidden" name="action" value="new">
			</form>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i id="wpda_toolbar_icon_add_backup" class="fas fa-plus-circle wpda_tooltip" title="Create new data backup"></i>
							<br/>
							Create backup
						</div>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function toolbar_import_csv()
    {
        if ( !isset( $_REQUEST['action'] ) ) {
            ?>
			<form id="wpda_upload_csv" style="display:none" method="post" action="?page=wpda&page_action=wpda_import_csv">
				<input type="hidden" name="action" value="upload">
			</form>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i id="wpda_toolbar_icon_upload_csv" class="fas fa-plus-circle wpda_tooltip" title="Upload new CSV File" onclick="jQuery('#wpda_upload_csv').submit()"></i>
							<br/>
							Upload CSV file
						</div>
					</div>
				</div>
			</div>
				<?php 
        }
    }
    
    protected function toolbar_sql()
    {
        ?>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i id="wpda_toolbar_icon_add_sql" class="fas fa-plus-circle wpda_tooltip" title="Create new query" onclick="tabNew()"></i>
							<br/>
							Create new query
						</div><div>
							<i class="fas fa-folder-open wpda_tooltip" title="Open existing query" onclick="openQuery()"></i>
							<br/>
							Open existing query
						</div>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function toolbar_designer()
    {
        ?>
			<form id="wpda_new_design" style="display: none"  method="post" action="?page=<?php 
        echo  esc_attr( WP_Data_Access_Admin::PAGE_DESIGNER ) ;
        ?>&table_name=<?php 
        echo  esc_attr( WPDA_Design_Table_Model::get_base_table_name() ) ;
        ?>">
				<input type="hidden" name="action" value="edit">
			</form>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i id="wpda_toolbar_icon_add_designer" class="fas fa-plus-circle wpda_tooltip" title="Create new table design" onclick="jQuery('#wpda_new_design').submit()"></i>
							<br/>
							Create table
						</div><div>
							<i class="fas fa-code wpda_tooltip" title="Import table design" onclick="jQuery('#upload_file_container').show()"></i>
							<br/>
							Import table designs
						</div>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function toolbar_publisher()
    {
        ?>
			<form id="wpda_new_publication" style="display: none" method="post" action="?page=<?php 
        echo  esc_attr( WP_Data_Access_Admin::PAGE_PUBLISHER ) ;
        ?>">
				<input type="hidden" id="wpda_new_publication_table_name" name="table_name" value="">
				<input type="hidden" name="action" value="new">
			</form>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i id="wpda_toolbar_icon_add_publication" class="fas fa-plus-circle wpda_tooltip" title="Create new publication"></i>
							<br/>
							Create publication
						</div><div>
							<i class="fas fa-code wpda_tooltip" title="Import publication" onclick="jQuery('#upload_file_container').show()"></i>
							<br/>
							Import publications
						</div>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function toolbar_charts()
    {
    }
    
    protected function toolbar_projects()
    {
        ?>
			<form id="wpda_new_project" style="display: none" method="post" action="?page=<?php 
        echo  esc_attr( WPDP::PAGE_MAIN ) ;
        ?>">
				<input type="hidden" name="action" value="new">
				<input type="hidden" name="mode" value="edit">
				<input type="hidden" name="table_name" value="<?php 
        echo  esc_attr( WPDP_Project_Model::get_base_table_name() ) ;
        ?>">
			</form>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i class="fas fa-plus-circle wpda_tooltip" title="Create new project" onclick="jQuery('#wpda_new_project').submit()"></i>
							<br/>
							Create project
						</div><div>
							<i class="fas fa-code wpda_tooltip" title="Import publication" onclick="jQuery('#upload_file_container_multi').show()"></i>
							<br/>
							Import projects
						</div>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function toolbar_templates()
    {
        ?>
			<div id="wpda-dashboard-toolbar" class="wpda-dashboard-toolbar" style="display:none">
				<div>
					<div>
						<div>
							<i class="fas fa-plus-circle wpda_tooltip" title="Create new project template" onclick="jQuery('#no_repository_buttons').hide(); jQuery('#add_table_to_repository').show(); return false;"></i>
							<br/>
							Create project template
						</div>
					</div>
				</div>
			</div>
			<?php 
    }
    
    protected function add_forms()
    {
        ?>
			<div id="wpda-select-panel-type" class="wpda-add-panel" style="display: none">
				<form>
					<fieldset class="wpda_fieldset wpda_fieldset_dashboard">
						<legend>
							Create widget
						</legend>
						<div>
							<label>
								Widget type
							</label>
							<select id="wpda-select-panel-type-choice">
								<?php 
        if ( class_exists( 'Code_Manager\\Code_Manager_Model' ) ) {
            ?>
									<option value="code">Custom Code</option>
								<?php 
        }
        ?>
								<option value="chart" selected>Chart</option>
								<option value="dbs">Database Info</option>
								<?php 
        if ( class_exists( 'WPDataAccess\\Premium\\WPDAPRO_Dashboard\\WPDAPRO_Widget_Project' ) ) {
            ?>
									<option value="project">Data Project</option>
								<?php 
        }
        ?>
								<option value="pub">Publication</option>
							</select>
							<?php 
        if ( !class_exists( 'Code_Manager\\Code_Manager_Model' ) || !class_exists( 'WPDataAccess\\Premium\\WPDAPRO_Dashboard\\WPDAPRO_Widget_Project' ) ) {
            ?>
								<a href="https://wpdataaccess.com/docs/documentation/dashboard/getting-started/" target="_blank">
									<span class="material-icons pointer wpda_tooltip" style="vertical-align:middle"
										  title="Your installation does not support all available widget types! Click to learn how to install more widget types..."
									>help</span>
								</a>
							<?php 
        }
        ?>
						</div>
					</fieldset>
					<div class="wpda-panel-buttons">
						<span class="wpda-panel-buttons">
							<button id="wpda-select-panel-type-button" class="button button-primary">
								<span class="material-icons wpda_icon_on_button">check</span>
								Next
							</button>
							<button id="wpda-select-panel-type-button-cancel" class="button">
								<span class="material-icons wpda_icon_on_button">cancel</span>
								Cancel
							</button>
						</span>
					</div>
				</form>
			</div>
			<div id="wpda-add-panel" class="wpda-add-panel" style="display: none">
				<form>
					<fieldset class="wpda_fieldset wpda_fieldset_dashboard">
						<legend>
							Create widget
						</legend>
						<div>
							<label>
								Widget type
							</label>
							<input type="text" id="wpda-add-panel-type-show" readonly>
							<input type="hidden" id="wpda-add-panel-type">
						</div>
						<div>
							<label>
								Widget name
							</label>
							<input type="text" id="wpda-add-panel-name" required/>
						</div>
						<div id="wpda-panel-code" style="display: none">
							<label>
								Select shortcode
							</label>
							<select id="wpda-add-panel-code">
								<?php 
        
        if ( class_exists( 'Code_Manager\\Code_Manager_Model' ) ) {
            $codes = \Code_Manager\Code_Manager_Model::get_active_shortcodes();
            foreach ( $codes as $code ) {
                ?>
										<option value="<?php 
                echo  esc_attr( $code['code_id'] ) ;
                ?>"><?php 
                echo  esc_attr( $code['code_name'] ) . ' (' . esc_attr( $code['code_type'] ) . ')' ;
                ?></option>
										<?php 
            }
        }
        
        ?>
							</select>
						</div>
						<div id="wpda-panel-project">
							<label>
								Select project
							</label>
							<select id="wpda-add-panel-project">
								<?php 
        $projects = WPDP_Project_Model::get_project_list();
        foreach ( $projects as $project ) {
            ?>
									<option value="<?php 
            echo  esc_attr( $project['project_id'] ) ;
            ?>"><?php 
            echo  esc_attr( $project['project_name'] ) . ' (project_id=' . esc_attr( $project['project_id'] ) . ')' ;
            ?></option>
									<?php 
        }
        ?>
							</select>
						</div>
						<div id="wpda-panel-publication">
							<label>
								Select publication
							</label>
							<select id="wpda-add-panel-publication">
								<?php 
        $pubs = WPDA_Publisher_Model::get_publication_list();
        foreach ( $pubs as $pub ) {
            ?>
									<option value="<?php 
            echo  esc_attr( $pub['pub_id'] ) ;
            ?>"><?php 
            echo  esc_attr( $pub['pub_name'] ) . ' (pub_id=' . esc_attr( $pub['pub_id'] ) . ')' ;
            ?></option>
									<?php 
        }
        ?>
							</select>
						</div>
						<div id="wpda-panel-dbms">
							<label>
								Select database
							</label>
							<select id="wpda-add-panel-dbms">
								<option value="wpdb" selected>WordPress database (
								<?php 
        global  $wpdb ;
        echo  esc_attr( $wpdb->dbname ) ;
        ?>
								)</option>
								<?php 
        $rdbs = WPDADB::get_remote_databases();
        ksort( $rdbs );
        foreach ( $rdbs as $key => $rdb ) {
            ?>
									<option value="<?php 
            echo  esc_attr( $key ) ;
            ?>"><?php 
            echo  esc_attr( $key ) ;
            ?></option>
									<?php 
        }
        ?>
							</select>
						</div>
						<div>
							<label>
								Add to column
							</label>
							<select id="wpda-add-panel-column">
								<option value="1" selected>1</option>
								<option value="2">2</option>
							</select>
							<select id="wpda-add-panel-position">
								<option value="prepend" selected>Before</option>
								<option value="append">After</option>
							</select>
						</div>
					</fieldset>
					<div class="wpda-panel-buttons">
						<span class="wpda-panel-buttons">
							<button id="wpda-add-panel-button" class="button button-primary">
								<span class="material-icons wpda_icon_on_button">check</span>
								Create
							</button>
							<button id="wpda-add-panel-button-back" class="button">
								<span class="material-icons wpda_icon_on_button">keyboard_arrow_left</span>
								Back
							</button>
							<button id="wpda-add-panel-button-cancel" class="button">
								<span class="material-icons wpda_icon_on_button">cancel</span>
								Cancel
							</button>
						</span>
					</div>
				</div>
			</form>
			<script type="application/javascript">
				jQuery(function() {
					jQuery("#wpda-select-panel-type-button").on("click", function() {
						jQuery("#wpda-panel-code").hide();
						jQuery("#wpda-panel-project").hide();
						jQuery("#wpda-panel-publication").hide();
						jQuery("#wpda-panel-dbms").hide();

						jQuery("#wpda-add-panel-type-show").val(jQuery("#wpda-select-panel-type-choice option:selected").text());
						jQuery("#wpda-add-panel-type").val(jQuery("#wpda-select-panel-type-choice").val());

						switch (jQuery("#wpda-select-panel-type-choice").val()) {
							case "code":
								jQuery("#wpda-panel-code").show();
								break;
							case "dbs":
								jQuery("#wpda-panel-dbms").show();
								break;
							case "project":
								jQuery("#wpda-panel-project").show();
								break;
							case "pub":
								jQuery("#wpda-panel-publication").show();
								break;
						}

						jQuery("#wpda-select-panel-type").hide();
						jQuery("#wpda-add-panel").show();

						return false;
					});

					jQuery("#wpda-select-panel-type-button-cancel").on("click", function() {
						jQuery("#wpda-select-panel-type").hide();

						return false;
					});

					jQuery("#wpda-add-panel-button").on("click", function () {
						panelName = jQuery("#wpda-add-panel-name").val();

						if (panelName=="") {
							alert("Widget name is required");
						} else {
							switch (jQuery("#wpda-add-panel-type").val()) {
								case "chart":
									addPanelChartToDashboard(
										"<?php 
        echo  esc_attr( $this->wp_nonce_add ) ;
        ?>",
										panelName,
										null,
										null,
										jQuery("#wpda-add-panel-column").val(),
										jQuery("#wpda-add-panel-position").val(),
									);
									break;
								case "code":
									addPanelCodeToDashboard(
										"<?php 
        echo  esc_attr( $this->wp_nonce_add ) ;
        ?>",
										panelName,
										jQuery("#wpda-add-panel-code").val(),
										jQuery("#wpda-add-panel-column").val(),
										jQuery("#wpda-add-panel-position").val(),
									);
									break;
								case "dbs":
									addPanelDbmsToDashboard(
										"<?php 
        echo  esc_attr( $this->wp_nonce_add ) ;
        ?>",
										panelName,
										jQuery("#wpda-add-panel-dbms").val(),
										jQuery("#wpda-add-panel-column").val(),
										jQuery("#wpda-add-panel-position").val(),
									);
									break;
								case "project":
									addPanelProjectToDashboard(
										"<?php 
        echo  esc_attr( $this->wp_nonce_add ) ;
        ?>",
										panelName,
										jQuery("#wpda-add-panel-project").val(),
										jQuery("#wpda-add-panel-column").val(),
										jQuery("#wpda-add-panel-position").val(),
									);
									break;
								case "pub":
									addPanelPublicationToDashboard(
										"<?php 
        echo  esc_attr( $this->wp_nonce_add ) ;
        ?>",
										panelName,
										jQuery("#wpda-add-panel-publication").val(),
										jQuery("#wpda-add-panel-column").val(),
										jQuery("#wpda-add-panel-position").val(),
									);
									break;
								default:
									alert("Unknown panel type");
							}
						}

						return false;
					});

					jQuery("#wpda-add-panel-button-back").on("click", function() {
						jQuery("#wpda-add-panel").hide();
						jQuery("#wpda-select-panel-type").show();

						return false;
					});

					jQuery("#wpda-add-panel-button-cancel").on("click", function () {
						closePanel();

						return false;
					});
				});
			</script>
			<?php 
    }
    
    protected function columns()
    {
        ?>
			<div id="wpda-dashboard-content" class="wpda-dashboard-content">
			<?php 
        // $last_key = array_key_last($this->dashboard_positions[0]); // Requires PHP 7
        $last_key = 1;
        if ( is_array( $this->dashboard_positions ) && sizeof( $this->dashboard_positions ) > 0 ) {
            foreach ( $this->dashboard_positions[0] as $key => $val ) {
                $last_key = $key;
            }
        }
        $this->number_of_columns = $last_key;
        for ( $i = 1 ;  $i <= $this->number_of_columns ;  $i++ ) {
            ?>
				<div id="wpda-dashboard-column-<?php 
            echo  esc_attr( $i ) ;
            ?>" class="wpda-dashboard-column wpda-dashboard-column-<?php 
            echo  esc_attr( $this->number_of_columns ) ;
            ?>">
				</div>
				<?php 
        }
        ?>
			</div>
			<?php 
    }
    
    protected function add_panels()
    {
    }
    
    protected static function add_panel(
        $dashboard_widget,
        $column,
        $position,
        $widget_id = null
    )
    {
    }
    
    private function get_databases()
    {
        // Get available databases
        $dbs = WPDA_Dictionary_Lists::get_db_schemas();
        foreach ( $dbs as $db ) {
            $databases[] = $db['schema_name'];
        }
        global  $wpdb ;
        $database_options = '';
        foreach ( $databases as $database ) {
            $selected = ( WPDA::get_user_default_scheme() === $database ? 'selected' : '' );
            
            if ( $database === $wpdb->dbname ) {
                $database_text = "WordPress database ({$database})";
            } else {
                $database_text = $database;
            }
            
            $database_options .= '<option value="' . esc_attr( $database ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $database_text ) . '</option>';
        }
        // Return available databases as option list
        return $database_options;
    }
    
    public function dashboard_js()
    {
        ?>
			<script type="application/javascript">
				let wpda_wpnonce_save   	= "<?php 
        echo  esc_attr( wp_create_nonce( static::DASHBOARD_SAVE . WPDA::get_current_user_login() ) ) ;
        ?>";
				let wpda_wpnonce_add    	= "<?php 
        echo  esc_attr( wp_create_nonce( WPDA_Widget::WIDGET_ADD . WPDA::get_current_user_login() ) ) ;
        ?>";
				let wpda_wpnonce_qb     	= "<?php 
        echo  esc_attr( wp_create_nonce( 'wpda-query-builder-' . WPDA::get_current_user_id() ) ) ;
        ?>";
				let wpda_wpnonce_refresh	= "<?php 
        echo  esc_attr( wp_create_nonce( WPDA_Widget::WIDGET_REFRESH . WPDA::get_current_user_login() ) ) ;
        ?>";
				let wpda_ajaxurl        	= "<?php 
        echo  admin_url( 'admin-ajax.php' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>";
				let wpda_databases			= '<?php 
        echo  $this->get_databases() ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>';
				let wpda_shared_dashboards	= '<?php 
        echo  json_encode( $this->shared_dashboards ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>';

				function saveWidgetPositions() {} // Implemented in the premium version
				function saveDashBoard(callback) {
					saveWidgetPositions();
					jQuery.ajax({
						type: "POST",
						url: wpda_ajaxurl + "?action=wpda_save_dashboard",
						data: {
							wp_nonce: wpda_wpnonce_save,
							wpda_widgets: dashboardWidgets,
							wpda_positions: dashboardWidgetPosition,
							wpda_deleted: dashboardWidgetDeleted,
							wpda_tabname: "<?php 
        echo  esc_attr( $this->tab_name ) ;
        ?>"
						}
					}).done(
						function(data) {
							dashboardWidgetDeleted = [];
							if (callback!==undefined) {
								callback();
							}
						}
					).fail(
						function (msg) {
							console.log("WP Data Access error (saveDashBoard):", msg);
						}
					);
				}
				<?php 
        ?>
			</script>
			<?php 
    }
    
    public static function save()
    {
        $wp_nonce = ( isset( $_POST['wp_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_nonce'] ) ) : '' );
        
        if ( !wp_verify_nonce( $wp_nonce, static::DASHBOARD_SAVE . WPDA::get_current_user_login() ) ) {
            WPDA::sent_header( 'application/json' );
            echo  static::msg( 'ERROR', 'Token expired, please refresh page' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            wp_die();
        }
        
        $widgets = ( isset( $_POST['wpda_widgets'] ) ? WPDA::sanitize_text_field_array( $_POST['wpda_widgets'] ) : array() );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
        $save = array();
        foreach ( $widgets as $widget ) {
            $save[$widget['widgetName']] = $widget;
        }
        update_user_meta( WPDA::get_current_user_id(), self::USER_DASHBOARD, $save );
        echo  self::msg( 'SUCCESS', 'Widget succesfully saved' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        wp_die();
    }
    
    public static function get_list()
    {
        $wp_nonce = ( isset( $_POST['wpda_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpda_wpnonce'] ) ) : '' );
        
        if ( !wp_verify_nonce( $wp_nonce, WPDA_Widget::WIDGET_REFRESH . WPDA::get_current_user_login() ) ) {
            WPDA::sent_header( 'application/json' );
            echo  static::msg( 'ERROR', 'Token expired, please refresh page' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            wp_die();
        }
        
        // Placeholder: implemented in the premium version
        echo  '' ;
        wp_die();
    }
    
    public static function delete_widget()
    {
        $wp_nonce = ( isset( $_POST['wpda_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpda_wpnonce'] ) ) : '' );
        
        if ( !wp_verify_nonce( $wp_nonce, self::DASHBOARD_SAVE . WPDA::get_current_user_login() ) ) {
            WPDA::sent_header( 'application/json' );
            echo  static::msg( 'ERROR', 'Token expired, please refresh page' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            wp_die();
        }
        
        // Placeholder: implemented in the premium version
        echo  '' ;
        wp_die();
    }
    
    public static function edit_chart()
    {
        $wp_nonce = ( isset( $_POST['wpda_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpda_wpnonce'] ) ) : '' );
        
        if ( !wp_verify_nonce( $wp_nonce, WPDA_Widget::WIDGET_REFRESH . WPDA::get_current_user_login() ) ) {
            WPDA::sent_header( 'application/json' );
            echo  static::msg( 'ERROR', 'Token expired, please refresh page' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            wp_die();
        }
        
        // Placeholder: implemented in the premium version
        echo  '' ;
        wp_die();
    }
    
    public static function load_widget()
    {
        $wp_nonce = ( isset( $_POST['wpda_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpda_wpnonce'] ) ) : '' );
        
        if ( !wp_verify_nonce( $wp_nonce, WPDA_Widget::WIDGET_REFRESH . WPDA::get_current_user_login() ) ) {
            WPDA::sent_header( 'application/json' );
            echo  static::msg( 'ERROR', 'Token expired, please refresh page' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            wp_die();
        }
        
        // Placeholder: implemented in the premium version
        echo  '' ;
        wp_die();
    }
    
    public static function get_tab_name( $tab_name )
    {
        return '_' . str_replace( ' ', '_', $tab_name );
    }
    
    public static function remove_new_dashboard_message()
    {
        WPDA::sent_header( 'text/html; charset=UTF-8' );
        $wp_nonce = ( isset( $_POST['wp_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_nonce'] ) ) : '' );
        
        if ( wp_verify_nonce( $wp_nonce, self::DASHBOARD_SAVE . WPDA::get_current_user_login() ) ) {
            // Permanently remove message from dashboard toolbar
            update_user_meta( WPDA::get_current_user_id(), self::USER_NEW_MESSAGE, 'removed' );
            echo  'OK' ;
        } else {
            echo  'FAILED' ;
        }
        
        wp_die();
    }
    
    public static function msg( $status, $msg )
    {
        $error = array(
            'status' => $status,
            'msg'    => $msg,
        );
        return json_encode( $error );
    }

}