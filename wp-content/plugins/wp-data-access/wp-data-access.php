<?php

/**
 * Plugin Name:       WP Data Access
 * Plugin URI:        https://wpdataaccess.com/
 * Description:       Local and remote data administration, publication and app development tool available directly from the WordPress dashboard.
 * Version:           5.1.3
 * Author:            Passionate Programmers B.V.
 * Author URI:        https://wpdataaccess.com/
 * Text Domain:       wp-data-access
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 *
 *
 * @package plugin
 * @author  Peter Schulz
 * @since   1.0.0
 */
use  WPDataAccess\WPDA ;
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Add freemius to WP Data Access.

if ( function_exists( 'wpda_freemius' ) ) {
    wpda_freemius()->set_basename( false, __FILE__ );
} else {
    // Load WPDataAccess namespace.
    require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
    /**
     * Create a helper function for easy SDK access
     *
     * @return Freemius
     */
    function wpda_freemius()
    {
        global  $wpda_freemius ;
        
        if ( !isset( $wpda_freemius ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $wpda_freemius = fs_dynamic_init( array(
                'id'             => '6189',
                'slug'           => 'wp-data-access',
                'type'           => 'plugin',
                'public_key'     => 'pk_fc2d1714ca61c930152f6e326b575',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 14,
                'is_require_payment' => false,
            ),
                'menu'           => array(
                'slug'    => 'wpda',
                'contact' => false,
                'network' => true,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $wpda_freemius;
    }
    
    // Init Freemius.
    wpda_freemius();
    // Signal that SDK was initiated.
    do_action( 'wpda_freemius_loaded' );
    /**
     * Change plugin settings info
     *
     * @param mixed $links Links.
     * @param mixed $file File.
     * @return mixed
     */
    function wpda_row_meta( $links, $file )
    {
        
        if ( strpos( $file, plugin_basename( __FILE__ ) ) !== false ) {
            // Add settings link.
            $settings_url = admin_url( 'options-general.php' ) . '?page=wpdataaccess';
            $settings_link = "<a href='{$settings_url}'>Settings</a>";
            array_push( $links, $settings_link );
        }
        
        return $links;
    }
    
    add_filter(
        'plugin_row_meta',
        'wpda_row_meta',
        10,
        2
    );
    /**
     * Activate plugin
     *
     * @author  Peter Schulz
     * @since   1.0.0
     */
    function activate_wp_data_access()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-data-access-switch.php';
        WP_Data_Access_Switch::activate();
    }
    
    register_activation_hook( __FILE__, 'activate_wp_data_access' );
    /**
     * Deactivate plugin
     *
     * @author  Peter Schulz
     * @since   1.0.0
     */
    function deactivate_wp_data_access()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-data-access-switch.php';
        WP_Data_Access_Switch::deactivate();
    }
    
    register_deactivation_hook( __FILE__, 'deactivate_wp_data_access' );
    /**
     * Check if database needs to be updated
     *
     * @author  Peter Schulz
     * @since   1.5.2
     */
    function wpda_update_db_check()
    {
        if ( get_option( WPDataAccess\WPDA::OPTION_WPDA_VERSION[0] ) !== WPDataAccess\WPDA::OPTION_WPDA_VERSION[1] ) {
            activate_wp_data_access();
        }
    }
    
    add_action( 'plugins_loaded', 'wpda_update_db_check' );
    /**
     * Uninstall blog
     *
     * This functions is called when the plugin is uninstalled. The following actions are performed:
     * + Drop plugin tables (unless settings indicate not to)
     * + Delete plugin options from $wpdb->options (unless settings indicate not to)
     *
     * Actions are processed on the current blog and are repeated for every blog on a multisite installation. Must be
     * called from the dashboard (WP_UNINSTALL_PLUGIN defined). User must have the proper privileges (activate_plugins).
     *
     * @author      Peter Schulz
     * @since       1.0.0
     */
    function wpda_uninstall_blog()
    {
        global  $wpdb ;
        $drop_tables = get_option( 'wpda_uninstall_tables' );
        
        if ( 'on' === $drop_tables ) {
            // Get all plugin table names (without WP prefix).
            $plugin_tables = WPDataAccess\WPDA::get_wpda_tables();
            foreach ( $plugin_tables as $plugin_table ) {
                // Loop through plugin tables.
                // Drop plugin table.
                $wpdb->query( $wpdb->prepare(
                    'DROP TABLE IF EXISTS `%1s`',
                    // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders, WordPress.DB.DirectDatabaseQuery.SchemaChange
                    WPDataAccess\WPDA::remove_backticks( $plugin_table )
                ) );
                // db call ok; no-cache ok.
                // Get plugin backup tables (if applicable).
                $backup_tables = $wpdb->get_results( $wpdb->prepare( '
						select table_name as table_name from information_schema.tables
						where table_schema = %s
						  and table_name like %s
					  	', array( $wpdb->dbname, "{$plugin_table}_BACKUP_%" ) ), 'ARRAY_A' );
                // db call ok; no-cache ok.
                foreach ( $backup_tables as $backup_table ) {
                    // Drop plugin backup table.
                    $wpdb->query( $wpdb->prepare(
                        'DROP TABLE IF EXISTS `%1s`',
                        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders, WordPress.DB.DirectDatabaseQuery.SchemaChange
                        WPDataAccess\WPDA::remove_backticks( $backup_table['table_name'] )
                    ) );
                    // db call ok; no-cache ok.
                }
            }
        }
        
        $delete_options = get_option( 'wpda_uninstall_options' );
        
        if ( 'on' === $delete_options ) {
            // Delete all options from wp_options.
            $wpdb->query( "\n\t\t\t\t\tDELETE FROM {$wpdb->options}\n\t\t\t\t\tWHERE option_name LIKE 'wpda%'\n\t\t\t\t" );
            // db call ok; no-cache ok.
            $wpdb->query( "\n\t\t\t\t\tDELETE FROM {$wpdb->usermeta}\n\t\t\t\t\tWHERE meta_key LIKE 'wpda%'\n\t\t\t\t\t   OR meta_key LIKE '%wp-data-access%'\n\t\t\t\t" );
            // db call ok; no-cache ok.
        }
    
    }
    
    /**
     * Uninstall WP Data Access tables, options and meta keys
     *
     * @return void
     */
    function wpda_uninstall()
    {
        
        if ( is_multisite() ) {
            global  $wpdb ;
            // Uninstall plugin for alll blogs one by one (will fail silently for blogs having no plugin tables/options).
            $blogids = $wpdb->get_col( "select blog_id from {$wpdb->blogs}" );
            // db call ok; no-cache ok.
            foreach ( $blogids as $blog_id ) {
                // Uninstall blog.
                switch_to_blog( $blog_id );
                wpda_uninstall_blog();
                restore_current_blog();
            }
        } else {
            // Uninstall on single site installation.
            wpda_uninstall_blog();
        }
        
        WPDA::wpda_delete_content_folder();
    }
    
    wpda_freemius()->add_action( 'after_uninstall', 'wpda_uninstall' );
    /**
     * Add WP Data Access icon to freemius
     *
     * @return string
     */
    function wpda_freemius_icon()
    {
        return dirname( __FILE__ ) . '/freemius/assets/img/wpda.png';
    }
    
    wpda_freemius()->add_filter( 'plugin_icon', 'wpda_freemius_icon' );
    /**
     * Handle freemius menu items
     *
     * @param boolean $is_visible Visibility.
     * @param string  $submenu_id Sub menu ID.
     * @return false|mixed
     */
    function wpda_freemius_menu_visible( $is_visible, $submenu_id )
    {
        // Support, account, contact, pricing.
        if ( 'dashboard' === WPDA::get_option( WPDA::OPTION_PLUGIN_NAVIGATION ) ) {
            if ( 'support' === $submenu_id || 'account' === $submenu_id ) {
                $is_visible = false;
            }
        }
        return $is_visible;
    }
    
    wpda_freemius()->add_filter(
        'is_submenu_visible',
        'wpda_freemius_menu_visible',
        10,
        2
    );
    /**
     * Start plugin
     *
     * @author  Peter Schulz
     * @since   1.0.0
     */
    function run_wp_data_access()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-data-access.php';
        $wpdataaccess = new WP_Data_Access();
        $wpdataaccess->run();
    }
    
    run_wp_data_access();
}
