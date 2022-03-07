<?php
/*
Plugin Name: Video User Manuals
Plugin URI: http://www.videousermanuals.com/
Description: A complete video manual for your clients
Version: 2.5.10
Author: Video User Manuals Pty Ltd
Author URI: http://www.videousermanuals.com
*/

define("VUM_PLUGIN_DIR", WP_PLUGIN_DIR . "/" . basename(dirname(__FILE__)) . "/");
require_once ( VUM_PLUGIN_DIR . "functions.php" );
class Vum{
 
    const pluginver             = '2.5.10';
    const ver_key               = 'wpm_o_ver';
    const vum_domain            = 'http://wordpress.videousermanuals.com/';
    const iframe_url            = '//wordpress.videousermanuals.com/json.php?jsoncallback=?';
    const activate_url          = 'http://vum2.videousermanuals.com/activate.php?serial=';
    const profile_url           = 'http://vum2.videousermanuals.com/save-profile.php';
    const prefs_url             = 'http://vum2.videousermanuals.com/prefs.php?';
    const api_url               = 'http://vum2.videousermanuals.com/api.php';
    const embed_domain          = 'http://vum2.videousermanuals.com/embed-video-setting.php';
    const iframe_frontend_url   = 'json_embed.php?jsoncallback=?';
    const iframe_frontend_embed_url = 'iframe-embed-frontend.php?jsoncallback=?';
    const iframe_frontend_embed_user = 'player-embed-iframe-user.php?'; // <iframe> URL to embed user video

    var $serial, $form, $formPrefs, $WPLang, $pluginURL; // Holds the form data
    var $video_pages_settings = false;
    var $sectionsToShow = false;
    var $_enabled_onpage_sections = null;
    var $videosToHide = false;
    var $localvideos_sections = false;
    var $get_local_videos = false;
    var $embed_footer_js = "";
    var $embeded_videos = array();
    var $guttenburg;

    function __construct() {

        // Set serial to use throughout
        $this->serial = get_option('wpm_o_user_id');

        // URL of the plugin folder, used from within views etc.
        $this->pluginURL = plugins_url( '', __FILE__ );

        // Get lang of what WP is running
        $lang = explode( '-', get_bloginfo('language') );
        $this->WPLang = $lang[0];

        // Set the WP Domain / site url and parse it
        $domain = parse_url( get_option( 'siteurl' ) );
        $this->domain = $domain[ 'host' ];
        
        $this->guttenburg = $this->has_gutenberg();
        $this->has_classic_editor = $this->has_classic_editor();

        add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_links' ) );

      // Need to ensure the prefs are set as we need them to build the view if not in english!
        if( !get_option('wpm_o_form_prefs') ) {
            $this->update_prefs();
        }

        add_action( 'wp_head', array( $this, 'wp_head' ) );
        add_action( 'admin_menu', array( $this,'add_pages' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts'), 999 );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'current_screen', array( $this, 'help_tab' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'install_plugins_pre_plugin-information' , array( $this, 'update_plugin' ) );
		add_action( "wp_footer", array($this, "video_shortcode_footer_js"), 200 );
        
        add_shortcode("vum_embed", array( $this, 'video_shortcode' ) );
        
        register_activation_hook( __FILE__, array( $this, 'install' ) );

        // display any error notices in wp-admin
        add_action( 'admin_notices', array( $this, 'error_notices' ) );
        
        // add action for frontend pop-up url
        add_action( 'init', array( $this, 'init' ) );

    }
    
    private function has_classic_editor()
    {
        return function_exists('classic_editor_init_actions') || class_exists( 'Classic_Editor' );
    }

    private function has_gutenberg()
    {
        $wp_version = get_bloginfo('version');

        $is_gutenberg_wp_version = version_compare( $wp_version, '5.0-RC1', '>=' );

        if( ! $is_gutenberg_wp_version ){
            return false;
        }

        if( ! $this->has_classic_editor() ){
            return true;
        }
        
        $wp_editor = get_option('classic-editor-replace');
        
        return ($wp_editor && $wp_editor == 'replace') ? false : true;

    }
    function init(){
        $this->embed_vum_video_view();
        $this->embed_vum_ebook_view();
    }
    function wp_head() {

        // Only show if a child acount
        if( ! get_option( 'wpm_o_host' ) )
            return;

        // Show only on home page
        if( ! is_front_page() )
            return;

        // Output the version
        echo '<meta name="vum" content="' . self::pluginver. '" />' . "\n";
    }

    /**
     * Enques JS we need in admin.
     */
    function admin_scripts() {
        
        wp_register_style( 'vumcss', plugins_url( 'vum.css', __FILE__ ), array(), self::pluginver );

        wp_register_script( 'jquery-scrollbar', $this->pluginURL . '/js/jquery.scrollbar.min.js' );
        wp_enqueue_script( array('jquery', 'jquery-scrollbar' ) );
        
        wp_enqueue_style( 'vumcss' );
    }

    function enqueue_scripts() {

        $is_embed_enabled = $this->is_embed_enabled();
        if ( is_admin() || $is_embed_enabled->status == 2) {
            wp_enqueue_script( 'jquery' );
        }

    }
    /**
     *  Adds plugin action links used to reset.
     */
    function plugin_links( $links ) {

        $new_links   = array();
        $new_links[] = '<a href="' . admin_url( 'options-general.php?page=vum-reset' ) . '">' . $this->terms( 'Reset' ) . '</a>';

        return array_merge( $new_links, $links );

    }

    /**
     * Helper function to access what version this plugin is.
     * @return string
     */
    function get_ver_key() {
        return self::ver_key;
    }

    /**
     * Helper Function to return the constant.
     * @return string
     */
    function get_plugin_ver() {
        return self::pluginver;
    }

    /**
     * Helper function to access the API URL.
     * @return string
     */
    function get_api_url() {
        return self::api_url;
    }

    function admin_preload() {

        // Check serial is set!
        $this->pre_activation();

        /* If not posting - go away */
        if( !isset($_POST) || empty($_POST) ) return;

        if( ! wp_verify_nonce($_POST['vum_save'],'vum_nonce') )
            wp_die('VUM Nonce Failed');

        /* Define the form array */
        $this->form = $this->defineForm();

        /*
         * Loop through all fields that should be there and
         * update them in wp_options. If they don't exist at
         * this point, they'll be added.
         */

        global $wpdb;

        $sections_option = array();
        foreach( $this->form->fields() as $key => $val ) {

            if( isset( $_POST[ $key ] ) ) {
                
                //Finde all enabled video sections
                $findsection_key = $key;
                $findvidsection = 'show_video_';
                $findsection = 'show_';
                $sectionvid_post = strpos($findsection_key, $findvidsection);
                $section_post = strpos($findsection_key, $findsection);

                if( $sectionvid_post === false && $section_post !== false && $_POST[ $key ] == '1') {
                    $sections_option[] = str_replace("show_", "", $key);
                }

                update_option( $val['dbName'], $_POST[ $key ], false );
            }

        }
        //save enabled video sections
        update_option("wpm_o_vum_sections", $sections_option, false);

        if( isset( $_POST['set_master_profile'] ) ) {

            // Get all settings stored in the options table prefixed with VUM's prefix.
            $settings = $wpdb->get_results("select option_name, option_value from $wpdb->options where option_name like 'wpm_o_%'");

            // Store in serialized array to pass back to VUM server.

            if( @unserialize( $settings ) === false )
            $settings = json_encode($settings);
            else
            $settings = unserialize( $settings );
        
            // Post the settings to Video User Manuals server for storage and future use on your sites.
            $response = wp_remote_post( apply_filters( 'vum_profile', self::profile_url ), array(
                                'method' => 'POST',
                                'timeout' => 3,
                                'redirection' => 0,
                                'httpversion' => '1.0',
                                'blocking' => true,
                                'body' => array( 'settings' => $settings, 'serial' => $this->serial ),
                            )
                        );

            if( is_wp_error( $response ) ) {
               // echo 'Something went wrong!';
            }

        }

        //Embed Video Tab
        if( isset( $_POST['enable_embed'] ) ){

                $response = $this->request( self::embed_domain, array(
                                'action' => 'set_embed_serial', 'serial' => $this->serial)
                            );

                if( ! is_wp_error( $response ) ) {

                    $json = json_decode($response);
                    
                    return true;
                    exit;

                } else {

                    return ;

                }
        }

        // If we saved master profile - set a URL flag.
        $master_profile_tag = ( isset( $_POST['set_master_profile'] ) ? '&master_profile=saved' : '' );

        if( isset( $_POST['return'] ) && $_POST['return'] )
            wp_redirect( $_POST['return'] . $master_profile_tag );
        else
            wp_redirect( remove_query_arg('saved') . '&saved=true' . $master_profile_tag );
        exit;
    }

    /**
     * Our admin_init calls.
     */
    function admin_init() {

        // Load our DB upgrader class & run it.
        require_once VUM_PLUGIN_DIR . 'db-upgrades.php';
        new VUM_DB_Upgrader( $this );

        // Register the style
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-tabs' );

        // Check if just installed, redirect to activation.
        if ( get_option( 'wpm_o_just_installed' ) ) {
            delete_option( 'wpm_o_just_installed' );
            wp_redirect( admin_url( 'admin.php?page=vum-activation' ) );
            exit;
        }

        // If serial is set, but the lang isn't
        if ( $this->serial && ! get_option( 'wpm_o_lang' ) ) {

            // If the heading is there, then this must be legacy, so set lang to AU english, else, try WP default.
            if ( get_option( 'wpm_o_custom_video_title' ) ) {
                update_option( 'wpm_o_lang', 'en-au', false );
            } else {
                $lang = explode( '-', strtolower( get_bloginfo( 'language' ) ) );
                $lang = ( $lang[0] == $lang[1] ? $lang[0] : strtolower( get_bloginfo( 'language' ) ) );
                update_option( 'wpm_o_lang', $lang, false );
            }
        }

    }

    /**
     * Add Menu pages to WP-Admin
     */
    function add_pages() {

        $location = ( get_option( 'wpm_o_move_menu_item' ) ? '2.1' : null );

        $custom_title = get_option( 'wpm_o_custom_menu_name' ) ? get_option( 'wpm_o_custom_menu_name' ) : $this->terms( 'Manual' );

        $access = get_option( 'wpm_o_view_access' ) ? 'edit_posts' : 'read';

        // If equal or less than 3.7, show old icon. For newer WP, show CSS space.
        $icon = version_compare( get_bloginfo( 'version' ), '3.8-beta', '>=' ) ? 'div' : plugins_url( 'images/vum-logo.png', __FILE__ );

        $view_page = add_menu_page(
            $custom_title,
            $custom_title,
            $access,
            __FILE__,
            array( $this, 'display' ),
            $icon,
            $location
        );

        add_action( 'load-' . $view_page, array( $this, 'pre_activation' ) );

        // Dont want child pages to show if no serial
        if ( $this->serial ) {

            add_submenu_page(
                __FILE__,
                $this->terms( 'Videos' ),
                $this->terms( 'Videos' ),
                $access,
                __FILE__,
                array( $this, 'display' )
            );

            if ( ! get_option( 'wpm_o_hide_manual' ) ) {
                add_submenu_page(
                    __FILE__,
                    $this->terms( 'User Manual' ),
                    $this->terms( 'User Manual' ),
                    $access,
                    'vum-ebook',
                    array( $this, 'ebook' )
                );
            }

            // If the setting is false (doesn't exist, or is set to no, OR there is a match.
            if ( ! get_option( 'wpm_o_user_menu_restrict' ) || get_option( 'wpm_o_user_menu_restrict' ) == get_current_user_id() ) {
                $admin_page = add_submenu_page(
                    __FILE__,
                    $this->terms( 'Manual Options' ),
                    $this->terms( 'Manual Options' ),
                    'activate_plugins',
                    'vum-options',
                    array( $this, 'admin' )
                );
                add_action( 'load-' . $admin_page, array( $this, 'admin_preload' ) );
            }
        }

        /* Dont want these pages in the menu, but still register the name & actions */
        $reset_page = add_submenu_page( null, 'Reset', 'Reset VUM', 'activate_plugins', 'vum-reset', array( $this, 'reset' ) );
        add_action( 'load-' . $reset_page, array( $this, 'do_reset' ) );

        $activation_page = add_submenu_page( null, 'Activation', 'Activate VUM', 'activate_plugins', 'vum-activation', array( $this, 'activate' ) );
        add_action( 'load-' . $activation_page, array( $this, 'do_activate' ) );

        $embed_page = add_submenu_page( null, 'Video Player', 'Video Player', 'read', 'vum-embed', array( $this, 'embed' ) );
        add_action( 'load-' . $embed_page, array( $this, 'embed' ) );

    }

    /**
     * Install action
     */
    function install() {
        // Flag to know we need to finish setting  up VUM
        add_option( 'wpm_o_just_installed', true );

        // Add the plugin version so an upgrade doesn't occur.
        add_option( $this->get_ver_key(), self::pluginver );
        $this->video_pages_settings(true);
        $this->is_embed_enabled(true);
    }

    /**
     * Reset VUM Settings
     */
    function do_reset() {
        $this->reset();

        wp_redirect( admin_url( 'admin.php?page=vum-activation' ) );
        exit;
    }

    /**
     * Do the reset query.
     */
    function reset() {
        global $wpdb;
        // Delete everything from wp_options where it's with our prefix.
        $wpdb->query( "delete from $wpdb->options where option_name like 'wpm_o_%'" );
        $this->video_pages_settings(true);
        $this->is_embed_enabled(true);
    }

    /**
     * Load our own CSS for admin.
     */
    function admin_css() {
        wp_enqueue_style( 'vumcss' );
    }


    function defineForm()
    {
        // Updadate prefs
        $this->formPrefs = $this->update_prefs();

        // Only need this in admin.
        if(!is_admin())
            return;

        require_once( VUM_PLUGIN_DIR . 'form.php');

        $form = new Vum_form( 'Video User Manuals Settings' );

        $form->setPluginURL($this->pluginURL);

        $form->openTab( 'Branding &amp; Customization' );

        $form->addDropdown(
            'lang',
            'Language',
            'Which accent would you like?',
            $this->formPrefs->langs,
            strtolower( get_bloginfo('language') ) // Use WP Lang as default
        );

        $form->addRadioGroup(
            'user_menu_restrict',
            'Restrict VUM Settings',
            'This setting will only show the plugin settings for the admin user who installed the plugin - which is you! This allows you to give clients admin access and hide the plugin settings from them.',
            array( get_current_user_id() => 'Yes', 0 => 'No' ),
            0
        );

        $form->addYesNo(
            'hide_manual',
            'Hide the User Manual',
            'You can remove the written user manual option from the admin sidebar if you want. ',
            0
        );

        $form->addYesNo(
            'move_menu_item',
            'Menu up top?',
            'The "Manual" menu item can appear up the top of the sidebar under the "Dashboard" rather than down the bottom',
            0
        );

        $form->addYesNo(
            'view_access',
            'Hide from subscribers',
            'Hide Video User Manuals from anyone who doesnt have edit_posts access.',
            1
        );

        $form->addYesNo(
            'change_popup_url',
            'Change popup url',
            'This setting will replace the url of the popup with the url of this website: ' . site_url(). '/?vum_video_view=49',
            0
        );

        $form->addTextbox(
            'custom_menu_name',
            'Change Menu Name',
            'Change the menu name in the sidebar from Manual to whatever you want.',
            $this->terms( 'Manual' )
        );

        $form->addTextbox(
            'plugin_heading_video',
            'Heading on the videos page',
            'Change the heading of the plugin from Manual to whatever you want.',
            $this->terms( 'Manual' )
        );

        $form->addTextbox(
            'plugin_heading_user',
            'Custom User Manual Heading',
            'Change the heading of the plugin from User Manual to whatever you want.',
            $this->terms( 'User Manual' )
        );

        $form->addTextarea(
            'intro_text',
            'Custom Introduction Text',
            'Change the introduction text for your clients if you want.',
            $this->terms( 'intro', false )
        );

        $form->addTextbox(
            'plugin_custom_logo',
            'Custom Logo For Plugin Pages',
            'Appears top left of pages next to heading. 32px high will look the best',
            vum_ssl_source( 'http://vum.s3.amazonaws.com/wp/assets/vum-logo-32.png' )
        );

        $form->addTextbox(
            'custom_ebook_img',
            'Custom Ebook Image',
            'Change the ebook image. Please put in the full url including http://'
        );

        $form->addTextbox(
            'branding_img',
            'Custom Logo above video player',
            'Absolute url to your logo. Max size 960 x 30px.',
            vum_ssl_source( 'http://vum.s3.amazonaws.com/wp/assets/vum-logo.gif' )
        );

        $form->addTextbox(
            'branding_logo',
            'Custom Logo on the video player',
            'Absolute url to your logo. Max size 86 x 86px.',
            ''
        );

        $form->addTextbox(
            'custom_vid_placeholder',
            'Custom Video Placeholder',
            'This is the image that will appear before the video plays. Should be 960px x 600px. Must have http://'
        );

        $form->closeTab();

        $form->openTab( 'Videos' );

        foreach( $this->formPrefs->sections as $section ) {

            $key = strtolower(str_replace(' ','_',$section->title) );

            $form->addYesNo(
                'show_' . $key,
                'Show ' . $section->title . ' Videos',
                '',
                $section->showDefault
            );

            if( $section->videos ) {

                $id = $form->openSection( '',  'show_' . $key );
                $form->addClass( $id, 'manual' );
                foreach( $section->videos as $video ) {
                    $form->addYesNo(
                        'show_video_' . $video->video_id,
                        'Show <em>' . $video->vidTitle . '</em>',
                        ''
                    );
                }

                $form->closeSection();
            }

        }

        $form->closeTab();

        $form->openTab( 'Custom Videos' );

        $form->html( '<p>If you want the videos thumbnails to look consistent, then we recommend you use our <a href="http://vum.s3.amazonaws.com/wp/assets/thumbs-template.psd">Photoshop thumbnail template</a>.</p><br /> ');

        $form->addDropdown(
            'num_local',
            'Number of custom videos',
            'How many custom videos would you like to add?',
            array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30)
        );

        // Only show followiung three options is over 0 vids selected
        if( get_option( 'wpm_o_num_local' ) > 0 ) {

        $form->addTextbox(
            'local_title',
            'Title',
            'Title to appear above your custom videos section',
            'Introduction Videos'
        );

        $form->addTextbox(
            'local_video_height',
            'Popup Window Height',
            'Height of your videos. Just include a number, do not include px.',
            660
        );

        $form->addTextbox(
            'local_video_width',
            'Popup Window Width',
            'Width of your videos. Just include a number, do not include px.',
            900
        );

        }

        $count = 1;

        $locations = array('0' => 'At the Top' );

        foreach( $this->formPrefs->sections as $section ) {
            $locations[$section->id] = 'Inside ' . $section->title;
        }

        while($count <= get_option( 'wpm_o_num_local' ) ) {
            $form->addHeading( 'Video ' . $count, 'h3' );

            $form->addTextbox(
                'localvideos_' . $count . '_0',
                'Your Video Name',
                'The name of your video'
            );

             $form->addDropdown(
                'localvideos_' . $count . '_loc',
                'Location of the video',
                'This video can go in one of our sections, or in your own section at the top',
                $locations,
                0
            );

            $id = $form->addYesNo(
                        'localvideos_' . $count . '_6',
                        'Use Embed Code',
                        'If you select yes, you can paste in your own HTML embed code. Eg YouTube, Vimeo, etc.',
                        0
                    );

            $form->addClass( $id, 'embed_selector' );

            $form->openSection( '', 'localvideos_' . $count . '_6' );

            $form->addTextarea(
                'localvideos_' . $count . '_5',
                'Custom Embed Code',
                'You are able to embed your own code here. Such as YouTube, Vimeo, etc.'
            );

            $form->closeSection();

            $form->addTextbox(
                    'localvideos_' . $count . '_1',
                    'Small Video Thumbnail',
                    'The full URL of the small video thumbnail. 240px x 150px. Please include http://'
            );

            $id =  $form->addTextbox(
                    'localvideos_' . $count . '_2',
                    'Video URL',
                    'The full URL of the video. Please include http://'
                );

            if( $form->getVal( 'localvideos_' . $count . '_6' ) )
                $form->addClass( $id, 'wpm-hidden' );

                $id =  $form->addTextbox(
                        'localvideos_' . $count . '_3',
                        'Description Of Video',
                        'The description will appear as alt text for the thumbnail'
                    );
            if( $form->getVal( 'localvideos_' . $count . '_6' ) )
                $form->addClass( $id, 'wpm-hidden' );

                $id = $form->addTextbox(
                        'localvideos_' . $count . '_4',
                        'Large Thumbnail Image',
                        'Large Thumbnail image for video. Should same size as video. Please include http://'
                );

            if( $form->getVal( 'localvideos_' . $count . '_6' ) )
                $form->addClass( $id, 'wpm-hidden' );

            $count++;
        }

        $form->closeTab();

        $form->openTab( 'Embed Videos' );

        //Check if embedding was activated
        // Added version 2.3 by jhay-ar
        $embed_status = $this->is_embed_enabled( true );

        if($embed_status->status == 4){
            //if no current domain setup

            $form->html( '<h2>Embed Videos on Your Membership Pages</h2><p>You can embed videos into pages on your membership site using short codes. Please read our definitions of a <a href="http://www.videousermanuals.com/wordpress-user-manual/embed-videos/?utm_campaign=vum-plugin&utm_medium=plugin&utm_source=embed" target="_blank">membership site.</a></p><p><strong>Please be aware, you do not have permision to embed these videos on publicly available webpages and doing so will result in your license being revoke and your videos will stop working.</strong></p><p>Please note you can only embed the videos on one (1) membership domain.</p><p>To enable the videos for this domain, please check the box below.</p>');
            $form->html( '<p><label><input type="checkbox" name="enable_embed" class="opt_enable_embed" value="1" /> Enable embed videos for this <a href="http://www.videousermanuals.com/wordpress-user-manual/embed-videos/?utm_campaign=vum-plugin&utm_medium=plugin&utm_source=embed" target="_blank">membership site</a>.</label></p>' );

        }elseif($embed_status->status == 2){
            //If embedding was activated
            $form->html( '<div id="embed-all-section">' );
            $form->html( '<h2>Embed All Videos</h2><p>The videos that will appear in the "vum_embed all" shortcode are defined by the videos that will have selected in your Videos and Custom Videos list.</p><p>In order to change the videos that will be embedded, you must first configure your "Video" and "Custom Videos" tabs.</p><br /> ');
            $form->html( '</div>' );

            $form->html( '<div class="clear"></div><div class="short_code_position"><p><strong>To embed the videos to your membership page use this short code:</strong></p><div class="vum_embed_shortcode">[vum_embed all]</div></div>' );

            $form->html( '<h2>Embed Selected Videos Only</h2><p>To generate short codes to display individual videos or groups of videos tick this box and choose the videos you wish to display.</p><p><label><input type="checkbox" class="opt_embed_selected_videos" /> <strong>Selected Videos only</strong></label></p>
                            <div class="embed_selected_videos" style="display:none">');

                foreach( $this->formPrefs->sections as $section2 ) {

                    $key = strtolower(str_replace(' ','_',$section2->title) );

                    $form->addYesNo(
                                    'show_2_' . $key,
                                    'Show ' . $section2->title . ' Videos',
                                    ''
                            );

                    if( $section2->videos ) {
                        $vid = $form->openSection( '',  'show_2_' . $key );
                        $form->addClass( $vid, 'manual' );
                        foreach( $section2->videos as $video_item ) {
                                $form->addYesNo(
                                            'show_video_2_' . $video_item->video_id,
                                            'Show <em>' . $video_item->vidTitle . '</em>',
                                            ''
                                    );
                        }
                        $form->closeSection();
                    }

                }

            $form->html( '</div>' );

            // Embedded video player settings
            $form->html( '<div id="video-player-settings" style="clear:both">' );
            $form->html( '<h2>Video Player</h2>' );
            $form->html( '<p>
                By default the videos will be displayed as thumbnails and play in a popover window.  <br>
                If you want the videos displayed in the web page,  please check the box below and customise the size of the videos.</p>' );
            $form->addYesNo(
                'form_embed_video',
                'Embed player into page',
                '',
                '0'
            );
            $form->addTextbox(
                'form_embed_video_width',
                'Width',
                'px',
                '400'
            );
            $form->addTextbox(
                'form_embed_video_height',
                'Height',
                'px',
                '300'
            );
            $form->html( '<h2>Video Section Headings</h2>' );
             $form->html( '<p>Above the video will be the heading of the section it belongs to. Use the setting below to hide this heading</p>' );
            $form->addYesNo('embed_hide_video_heading', 'Hide video section heading', '', 0);
            $form->html( '</div>' );


            $form->html( '<div class="clear"></div><h2>Change Domains</h2><p>If you would like to use the embed feature on a different domain you must reset this through the <a href="http://www.videousermanuals.com/members-zone/" target="_blank">members zone</a> first.</p>');

        }else{

            $form->html( '<p>You can only use this feature on 1 domain and you have already set this up on a different domain.</p><p>If you would like to use the embed feature on this domain, you must reset this through the <a href="http://www.videousermanuals.com/members-zone/" target="_blank">members zone</a> first.</p>');
        }


        $form->closeTab();

        $form->openTab( 'Set Master Profile' );

        $form->html( '<p>If you would like to save theses settings as your "Master Profile" so you can reuse them on other sites, tick the box and click save. </p> ');
        $form->html( '<p> NB: This will overwrite your existing Master Profile, however it will not affect any sites you have previously setup with your Master Profile.</p>' );
        $form->html( '<label><input type="checkbox" name="set_master_profile" value="1" /> Set as master profile?</label><br />' );

        $form->closeTab();

        return $form;
    }

    function terms( $term = '' , $useTerm = true ) {
        // Always passed in as eng - don't keep doing.
        if($this->WPLang=='en' && $useTerm ) {
            return $term;
        }

        $prefs = get_option( 'wpm_o_form_prefs' );

        // If we find a term list in this lang, look further
        if( isset( $prefs->terms->{$this->WPLang} ) ) {

            if( $useTerm ) {
                $term_code = 'term_' . strtolower( str_replace( ' ', '_', $term ) );
            } else {
                $term_code = 'message_' . $term;
            }

            // IF that term exists, return that, else use the original (English)
            if( $prefs->terms->{$this->WPLang}->$term_code ) {
                return $prefs->terms->{$this->WPLang}->$term_code;
            }

        }

        return $term;
    }

    function admin() {

        // Define the form array
        $this->form = $this->defineForm();

        // If the custom menu name isn't set, they haven't saved anything yet.
        if( ! get_option('wpm_o_custom_menu_name') )
            $this->notice( 'You have to save your changes in order for the videos to appear' );

        if( isset( $_GET[ 'saved' ] ) )
            $this->notice( 'Settings saved.' );

        if( isset( $_GET[ 'master_profile' ] ) )
            $this->notice( 'Settings saved and your master profile has been updated' );

        // Display the form
        $this->form->display();

        self::load('admin');
    }

    function update_prefs() {

        if(!$this->serial) return array();

        $url                    = new stdClass;
        $url->user_id           = $this->serial;
        $url->wp_lang           = $this->WPLang;
        $url->plugin_version    = self::pluginver;
        $url->wp_version        = get_bloginfo('version');
        $url->lang              = get_option('wpm_o_lang');
        $url->url               = $this->domain;

        $url_params = '';

        foreach($url as $k=>$v) {

            if($v===FALSE) $v='0';

            $url_params .= $k . '=' . $v . '&';

        }

        /* Trim last character (a comma) from string */
        $url_params = substr_replace($url_params ,'',-1);

        $response = wp_remote_get( apply_filters( 'vum_prefs', self::prefs_url . $url_params ) );
        
        if ( is_wp_error($response) ) {
            update_option( 'wpm_o_form_prefs', 'error', false );
        } else {
            $wpm_prefs = json_decode( wp_remote_retrieve_body( $response ) );
            update_option( 'wpm_o_form_prefs', $wpm_prefs, false );
        }

        return $wpm_prefs;
    }


    function video_pages_settings($force = false) {

        if( $this->video_pages_settings ) return $this->video_pages_settings;

        $this->video_pages_sections_settings = get_option( 'wpm_pages_sections_config');
        $this->video_pages_settings = get_option( 'wpm_pages_config');

        if( ! $this->video_pages_settings || $force ) {

            //self::vum_domain
            $response = wp_remote_get( apply_filters( 'vum_pages_config', self::vum_domain . "wpm_pages_config.php") );
            
            if ( is_wp_error( $response ) ) {
                update_option( 'wpm_pages_config', 'error', false );
            } else {
                $config_json = json_decode( wp_remote_retrieve_body( $response ) );
                
                $onpage_config = json_decode( wp_remote_retrieve_body( $response ) );
                
                $this->video_pages_settings = (array) $onpage_config->videos;
                $this->video_pages_sections_settings = (array) $onpage_config->sections;
                update_option( 'wpm_pages_config', $this->video_pages_settings, false );
                update_option( 'wpm_pages_sections_config', $this->video_pages_sections_settings, false );
            }
        }
        return $this->video_pages_settings;

    }

    function pre_activation() {

        // Check there is a serial set.
        if(!$this->serial) {
            wp_redirect ( admin_url( 'admin.php?page=vum-activation' ) );
            die;
        }

    }

    /**
     * Used to display embeded videos.
     */
    function embed() {

        require_once( VUM_PLUGIN_DIR . 'views/embeded.php' );
        die;

    }

    /**
     * Display the VUM videos.
     */
    function display() {
        global $wpdb;

        $url                 = new stdClass;
        $url->user_id        = $this->serial;
        $url->plugin_version = self::pluginver;
        $url->wp_version     = get_bloginfo( 'version' );
        $url->lang           = get_option( 'wpm_o_lang' );
        $url->guttenburg = $this->guttenburg;
        $url->hce = $this->has_classic_editor;

        // Get all sections we ARE showing.
        $url->sectionsToShow = $this->sections_to_show();
        
        // Get all videos we are NOT showing
        $url->vidsToHide = $this->videos_to_hide();

        /* Trim last character (a comma) from string */
        $url_params = vum_get_api_params( $url );

        /* Local Videos */

        $show_local = get_option( 'wpm_o_num_local' );

        list($custom_local_videos, $local_videos, $show_local, $vum_video_embed_out) = $this->get_local_videos();


        require_once( VUM_PLUGIN_DIR . 'views/videos.php' );

    }

    /**
     *
     * Helper function to display a VUM video.
     *
     * @param $video_id
     * @param $video
     *
     * @return string/HTML
     */
    function display_vid( $video_id, $video, $is_pop_up = true ) {

        $src = stripslashes( get_option( 'wpm_o_localvideos_' . $video_id . '_5' ) );
        preg_match( '/width="(\d+)(px)?" height="(\d+)(px)?"/', $src, $matches );

        $width  = ( isset( $matches[1] ) ? intval( $matches[1] ) : get_option( 'wpm_o_local_video_width' ) );
        $height = ( isset( $matches[3] ) ? intval( $matches[3] ) : get_option( 'wpm_o_local_video_height' ) );

        if ( $video->doembed == '1' ) {
            $onclick = admin_url( 'admin.php?page=vum-embed' ) . '&wpmvid=' . $video_id . '&amp;video_thumb=' . $video->image . '&amp;width=' . ( $width + 25 ) . '&amp;height=' . $height . '\',\'welcome\',\'width=' . ( $width + 50 ) . ', height=' . ( $height + 50 ) . ',menubar=0,status=0,location=0,toolbar=0,scrollbars=0';
        } else {
            $onclick = vum_domain('video-player.php?video_url=' . $video->vid . '&amp;video_thumb=' . $video->image . '&amp;width=' . ( $width + 25 ) . '&amp;height=' . $height . '\',\'welcome\',\'width=' . ( $width + 50 ) . ', height=' . ( $height + 50 ) . ',menubar=0,status=0,location=0,toolbar=0,scrollbars=0');
        }
        
        if( $is_pop_up ) {
            $a_attribue = ' href="javascript:void(0)" onclick="window.open(\'' . $onclick . '\')"';
        } else {
            $a_attribue = ' href="' . $onclick . '" target="targetvum_player_frame"';
        }

        $var = '<div class="video-container" id="c_' . $video_id . '">';
        $var .= '<a'. $a_attribue . ' class="custom_vid">';
        $var .= '<img src="' . $video->thumb . '" alt="' . stripslashes( $video->name ) . '" width="240" height="150" /><br />';
        $var .= stripslashes( $video->name );
        $var .= '</a>';
        $var .= '</div>';

        return $var;
    }

    /** Outputs the code to display A VUM video as an embedded iframe */
    function display_vid_embedded( $video_id, $video ) {

        $src = stripslashes( get_option( 'wpm_o_localvideos_' . $video_id . '_5' ) );
        preg_match( '/width="(\d+)(px)?" height="(\d+)(px)?"/', $src, $matches );

        $width  = get_option( 'wpm_o_form_embed_video_width' );
        $height = get_option( 'wpm_o_form_embed_video_height' );

        $url_data = array(
            'vid' => $video->vid,
            'video_thumb' => vum_ssl_source( $video->thumb ),
            'width' => $width,
            'height' => $height,
            'plugins_ver' => self::pluginver,
            'hce' => $this->has_classic_editor,
            'wp_version' => 'default',
            'branding_logo' => vum_ssl_source( get_option( 'wpm_o_branding_logo' ) )
        );

        // the <iframe> embed URL
        $embed_url = apply_filters( 'vum_embed_iframe', vum_domain(self::iframe_frontend_embed_user ));
        $embed_url .= http_build_query( $url_data );

        ?>
        <iframe
            width="<?php echo esc_attr( $width ) ?>"
            height="<?php echo esc_attr( $height ) ?>"
            src="<?php echo esc_attr( $embed_url ) ?>"
            scrolling="no"
            allowfullscreen
            style="overflow:hidden;border:0">
        </iframe>
        <?php

    }

    /**
     * VUM EBook Admin Page
     */
    function ebook() {
        
        $popupsetting = get_option( 'wpm_o_change_popup_url', false );
        $wpm_urlvars = "lang:'" . get_option( 'wpm_o_lang' ) . "',wp_version:'" . get_bloginfo( 'version' ) . "', user_id:'" . get_option( 'wpm_o_user_id' ) . "',custom_ebook_img:'" . vum_ssl_source( get_option( 'wpm_o_custom_ebook_img' ) ) . "'";

        ?>
        <div id="manual-page" class="wrap">


            <h2 style="margin-bottom:8px">
                <?php
                if ( get_option( 'wpm_o_plugin_custom_logo' ) ) {
                    echo '<img src="' . vum_ssl_source( get_option( 'wpm_o_plugin_custom_logo' ) ) . '" alt="logo" style="vertical-align: -7px">&nbsp; ';
                }

                echo get_option( 'wpm_o_plugin_heading_user' ); ?>
            </h2>
            
            <?php if ( $popupsetting ) : ?>
                <?php include dirname( __FILE__ ) .'/views/backend-ebook.php'; ?>
            <?php else:?>
            
            <div id="ajax_msg"></div>
            <div id="ajax_content"></div>
            <script type="text/javascript">
                
                    jQuery.getJSON('<?php echo vum_domain("online-manual.php?jsoncallback=?")?>',
                        {<?php echo $wpm_urlvars;?>},
                        function (data, textStatus) {
                            jQuery('#ajax_content').append(data);
                        });
            </script>
            <?php endif; ?>
        </div>
    <?php
    }
    
    function video_shortcode($atts) {
	    
		extract(shortcode_atts(array(
				'ids' => ''
			), $atts));
		
		 $ids_array = array();
		
		//check if the domain is registered as embed domain
        if( ! $this->serial ) { return '<p>Videos not enabled.</p>';};

        $is_embed_enabled = $this->is_embed_enabled( true );

        if( $is_embed_enabled->status != 2 ) {
            return '<p>Videos not enabled.</p>';
        }
        
		if(!empty($ids))
			$ids_array = explode(",", $ids);
		
		$count = count( $this->embeded_videos );
		$this->set_shortcode_videos( $count,  $ids_array);
		
        return <<<xxxx
	            <div id="ajax_msg"></div>
	            <div id="embed_ajax_content$count"></div>
xxxx;
	}
	
	function set_shortcode_videos($index, $atts) {
		$this->embeded_videos[$index] = str_replace(" ", "", $atts);
	}
	
	function video_shortcode_footer_js($atts) {
        global $wpdb;

		if( sizeof( $this->embeded_videos ) == 0) return;
        
        $url                 = new stdClass;
        $url->plugin_version = self::pluginver;
        $url->guttenburg = $this->guttenburg;
        $url->hce = $this->has_classic_editor();
        $url->wp_version = 'default';
        $url->width = get_option( 'wpm_o_form_embed_video_width' );
        $url->height = get_option( 'wpm_o_form_embed_video_height' );
        
        $sectionsToShow = '';
        $vidsToHide = '';

		$ids_array = array();
        if( sizeof( $this->embeded_videos ) > 0) {
	        foreach( $this->embeded_videos as $v_index => $video_ids ) {
		        if( sizeof( $video_ids ) > 0 ) {
					foreach( $video_ids as $video_id ) {
						$ids_array[] =  $video_id;
			        }
		        }
	        }
        }
		
		$all_vids = ( count($ids_array) > 0) ? false : true ;

        $this->formPrefs = get_option( 'wpm_o_form_prefs' );
        
        // Get all sections we ARE showing.
        foreach( $this->formPrefs->sections as $section ) {

            $key = strtolower( str_replace(' ','_', $section->title ) );

            $sectionsToShow .= "\'" . $key . "\',";

           if( ! $all_vids ) {
                if( $section->videos ) {
                    // Get all videos not to show.
                    foreach( $section->videos as $video ) {

                        if( ! in_array( $video->video_id, $ids_array ) ) {
                            $vidsToHide .= $video->video_id . ',';
                        }
                    }
                }
            }

        }


        // Trim off last comma and put in URL array to pass to VUM.
        $url->sectionsToShow = substr_replace( $sectionsToShow, '', - 1 );

        // Trim off last comma and put in URL array to pass to VUM.
        $url->vidsToHide = substr_replace( $vidsToHide, '', - 1 );

        $url_params = vum_get_api_params( $url );

        // get the frontend json interface
        if ( get_option( 'wpm_o_form_embed_video' ) ) {
            $json_frontend_url = apply_filters( 'iframe_frontend_url', vum_domain( self::iframe_frontend_embed_url ) );
        } else {
            $json_frontend_url = apply_filters( 'iframe_frontend_url', vum_domain( self::iframe_frontend_url ) );
        }
        
        $popupsetting = get_option( 'wpm_o_change_popup_url', false );

        $popupsetting_output = '';

        if( $popupsetting && $popupsetting == '1') {

            $video_site_url = site_url( "/?vum_video_view=1" );
            $popupsetting_output .= "var form_obj = jQuery(this).find('form');
                          form_obj.attr( 'action',  '{$video_site_url}');";

        }
        
		$embed_hide_video_heading = get_option( 'wpm_o_embed_hide_video_heading', false );
		$additional_jquery = "";
        if($embed_hide_video_heading) {
		    $additional_jquery = 'jQuery("#ajax_content h2").remove();';
        }
        
        $ids = json_encode( $this->embeded_videos );
		//Display javascript to the footer see $this->video_shortcode_footer_js()
		echo <<<xxx
        <style type="text/css">
            .vum-video-container {margin-right: 20px;float: left;text-align: center;margin-bottom: 10px;}
            .vum-video-container a {color: #21759B;text-decoration: none;font-size: 12px;}
            .vum-video-container img {margin-bottom: 6px;}
            .vum-video-container input[type="image"]{ border: 0!important;} 
            .vum-video-container input[type="image"] { padding: 0; margin: 0; border: 0!important; }
            .vum-highlight{border: #FFFFBF solid 10px; background:#FFFFBF; font-weight: bold;border-top:0;border-bottom: 0; -webkit-border-bottom-right-radius: 4px;-webkit-border-bottom-left-radius: 4px;-moz-border-radius-bottomright: 4px;-moz-border-radius-bottomleft: 4px;border-bottom-right-radius: 4px;border-bottom-left-radius: 4px;}
            .vum-section iframe{ border: 0!important;}
        </style>
            <script type="text/javascript">
                var vum_count = 0;
                jQuery(document).ready(function() 
                {
                    
                    if(vum_count == 0){
                        vum_count++;
                        jQuery.getJSON('$json_frontend_url', { $url_params },
                            function(data, textStatus)
                            {
	                            var ids_wrapper = $ids;
	                            jQuery(data).find('div.vum-video-container').each(function(){
		                            console.log( jQuery(this).parent().find('h2').html() );
									var v_id = jQuery(this).attr('id').replace("v_", "");
									var video_html =  '<div id="' + jQuery(this).attr('id') + '" class="vum-video-container">' +
														jQuery(this).html() + '<div>';
                                    if( jQuery(ids_wrapper).length > 0 ){
                                        jQuery(ids_wrapper).each(function(wrapper_index, vids ) {               
                                            if( vids.length > 0 ){
                                                jQuery(vids).each(function(index, video ) {
                                                    if( v_id == video ) {
                                                        jQuery('#embed_ajax_content'+wrapper_index).append( video_html );
                                                    }
                                                });
                                            } else {
                                                jQuery('#embed_ajax_content'+wrapper_index).append( video_html );
                                            }
                                        });
                                    } else {
                                        jQuery('#embed_ajax_content0').append( video_html );
                                    }
	                            }); 
	                            
								{$additional_jquery}
                                var entireUrl = jQuery(location).attr('href');
                                var urlBits = entireUrl.split('#');
                                
                                jQuery(".vum-video-container").each(function()
                                {
                                    var link = jQuery(this).find("a");
                                    var divId = jQuery(this).attr('id');
                                    var newLink = urlBits[0] + '#' + divId ;
                                    link.attr("href", newLink );
                                    {$popupsetting_output}
                                });
            
                                if( window.location.hash )
                                {
                                    jQuery('html,body').animate({scrollTop:jQuery(window.location.hash).offset().top}, 500);
                                    jQuery( window.location.hash ).addClass('vum-highlight'); 
                                }
                        });
                    }
                });
        
            </script>
xxx;
		
    }
    
    function get_enabled_onpage_sections() {
        
        if( ! is_null( $this->_enabled_onpage_sections )) return $this->_enabled_onpage_sections;

        $screen = get_current_screen();
        $vum_sections = get_option("wpm_o_vum_sections");

        if( ! $vum_sections ) return false;

        $videos = $this->video_pages_settings();
        if( ! isset($this->video_pages_sections_settings[ $screen->id ]) ) return false;

        $page_sections = $this->video_pages_sections_settings[ $screen->id ];

        // Get all sections we ARE showing.
        $vum_sections = get_option("wpm_o_vum_sections");
        
        $visible_sections = array();
        if(is_array($vum_sections)) {
            $visible_sections = array_intersect( $page_sections, $vum_sections );
        }

        $this->_enabled_onpage_sections = $visible_sections;
        
        return $visible_sections;


    }

    function do_activate()
    {
        /* If Submit - lets check the serial */
        if( isset($_POST['serial']) ) {

            $json = json_encode( array(
                    'command'        => 'activate',
                    'license'        => trim( $_POST['serial'] ),
                    'domain'         => $this->domain,
                    'plugin_version' => self::pluginver,
                    'wpurl'          => get_bloginfo( 'wpurl' )
                )
            );

            /* Push the data to the activation to the server */
            $response = wp_remote_post( apply_filters( 'vum_activate', self::api_url ), array(
                    'method' => 'POST',
                    'body' => array( 'json' => $json ),
                )
            );

            if ( is_wp_error( $response ) ) {

                $this->activation_error = 'Sorry, we were unable to contact our activation server. Please try again soon.';

            } else {

                // Decode our JSON response from the API.
                $api_reply = json_decode( wp_remote_retrieve_body( $response ) );

                // If this serial belongs to a hosting company, set that in the DB, then carry on.
                if ( isset( $api_reply->is_host ) && $api_reply->is_host == 'true' ) {

                    // Note in the DB this is a host serial number.
                    update_option( 'wpm_o_host', true, false );
                }

                if ( isset( $api_reply->has_prefs ) && $api_reply->has_prefs == 'true' ) {

                    //  Add Serial
                    update_option( 'wpm_o_user_id', trim( $_POST['serial'] ), false );
                    $this->video_pages_settings(true);
                    
                    // We are going to apply the profile?
                    if ( isset( $_POST['applyProfile'] ) ) {

                        $response = wp_remote_get( apply_filters( 'vum_activate', self::activate_url . trim( $_POST['serial'] ) . '&apply=true' . '&url=' . $this->domain ) );

                        $body = wp_remote_retrieve_body( $response );

                        $settings = json_decode( $body );

                        foreach ( $settings as $s ) {

                            if ( ( $s->option_name == 'wpm_o_user_menu_restrict' ) && ( $s->option_value != '0' ) ) {
                                $s->option_value = get_current_user_id();
                            }

                            update_option( $s->option_name, $s->option_value, false );
                        }
                    }

                    wp_redirect( admin_url( 'admin.php?page=vum-options' ) );
                    exit;

                } elseif ( $api_reply->result == 'active' ) {

                    update_option( 'wpm_o_user_id', trim( $_POST['serial'] ), false );
                    wp_redirect( admin_url( 'admin.php?page=vum-options' ) );
                    exit;

                } elseif ( $api_reply->result == 'over-quota' ) {
                    $this->activation_error = 'Sorry, we were unable match this serial number as you have exeeded your license quota. <a target="_blank" href="http://www.videousermanuals.com/singlehelp/">Please contact us if you need assistance.</a>';

                } elseif ( $api_reply->result == 'expired' ) {
                    $this->activation_error = 'Sorry, It appears your license has expired. Please <a href="https://videousermanuals.desk.com/">contact us</a> if you require assistance. ';

                } else {
                    $this->activation_error = 'Sorry, we were unable match this serial number. Did you enter it correctly?';

                }
            }
        }
    }

    // checks whether CURL is installed
    function check_curl_installed() {
        return function_exists( 'curl_version' );
    }

    function activate() {

        if ( isset( $this->activation_error ) ) {
            $this->notice( $this->activation_error );
        }

        if ( is_multisite() ) {
            $this->notice( 'As this is a Multi-site installation, you will have to activate VUM for each site.' );
        }

        $display_activate_form = $this->check_dependencies();
        self::load( 'activate', array( 'display_activate_form' => $display_activate_form ) );

    }

    // Returns whether the plugin dependencies are satisfied
    function check_dependencies() {

        return $this->check_curl_installed();

    }

    // Display any error notices at admin_notices
    function error_notices() {

        // Ensure that curl is installed
        if ( !$this->check_curl_installed() ) {

            $this->error_notice(
                "Video User Manuals requires CURL to be installed on your server/site. <br>".
                "You will need to contact your host to resolve this issue."
            );

        }

    }

    function notice( $message = '' ) {
        echo '<div class="updated"><p>' . $message . '</p> </div>';
    }

    function error_notice( $message = '' ) {
        echo '<div class="error" style="margin:2em 0"><p>' . $message . '</p> </div>';
    }

    function load( $viewName = '', $vars=array() ) {
        extract( $vars );
        require_once( VUM_PLUGIN_DIR . 'views/' . $viewName . '.php' );
    }

    function help_tab() {

        if( ! $this->serial ) return;

        $enabled_sections = $this->get_enabled_onpage_sections();

        // REturn of no activated sections
        if( ! $enabled_sections ) return;


        $screen = get_current_screen();
        $vum_sections = get_option("wpm_o_vum_sections");
        $this->video_pages_settings();
        $video_sec = (array) $this->video_pages_settings;
            
        $ids_array = $video_sec[$screen->id];
        
        $url                 = new stdClass;
        $url->user_id        = $this->serial;
        $url->plugin_version = self::pluginver;
        $url->wp_version     = get_bloginfo( 'version' );
        $url->lang           = get_option( 'wpm_o_lang' );
        $url->guttenburg      = $this->guttenburg;
        
        $url->branding_img   = vum_ssl_source( get_option( 'wpm_o_branding_img' ) );
        $url->branding_logo  = vum_ssl_source( get_option( 'wpm_o_branding_logo' ) );
        $url->video_image    = vum_ssl_source( get_option( 'wpm_o_custom_vid_placeholder' ) );


        // Trim off last comma and put in URL array to pass to VUM.
        $url->sectionsToShow = implode(",", $enabled_sections);

        // Trim off last comma and put in URL array to pass to VUM.
        $url->vidsToShow = implode(",", $ids_array);

        
        $json_frontend_url = vum_domain('iframe-onpage.php?jsoncallback=?');
        
        wp_register_script( 'vum-admin-script', $this->pluginURL . '/js/admin.js' );
        wp_register_script( 'jquery-scrollbar', $this->pluginURL . '/js/jquery.scrollbar.min.js' );
        wp_enqueue_script( array('jquery', 'jquery-scrollbar' ) );
        
        $vum_params = json_encode( (array) $url );

        list($custom_local_videos, $local_videos, $show_local, $vum_video_embed_out) = $this->get_local_videos();
    
        $local_video_output = '';
    
        if( $show_local && isset($custom_local_videos) ):
            foreach($custom_local_videos as $video_id => $video ):
                $local_video_vid = addslashes($this->display_vid($video_id, $video, false));
                $local_video_output .= "jQuery('#section-{$video->loc}').append('{$local_video_vid}');";
            endforeach;
        endif;

        $content = <<<vvvv
        <div class="vum-onpage-wrapper"><h3>Video Tutorials</h3><div id="vum-videos"></div></div>
        <script type="text/javascript">
            jQuery(document).ready(function(){

                jQuery("#contextual-help-link-wrap").on("click", function(){
                    if( jQuery("#tab-panel-vum-videos #vum-videos").html() == "" ) {
                        
                        jQuery("<div />", {"id":"vum-modal"}).html('<span class="dashicons dashicons-no"></span>').appendTo(".contextual-help-tabs-wrap").hide();
                        jQuery("<div />", {"id":"vum-player-wrapper"}).html('<iframe src="" frameborder="0" allowfullscreen id="targetvum_player_frame" name="targetvum_player_frame" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%"></iframe>').appendTo("#vum-modal");
                      
                        jQuery.getJSON('{$json_frontend_url}', $vum_params, function(data, textStatus) {
                                jQuery('#tab-panel-vum-videos #vum-videos').append( data );
                                {$local_video_output}
                                jQuery('#tab-panel-vum-videos .vum-onpage-wrapper').scrollbar();
                                jQuery("#vum-modal").css("height", "100%");
                                var entireUrl = jQuery(location).attr('href');
                                var urlBits = entireUrl.split('#');
              
                                jQuery(".video-container").each(function() {
                                    var link = jQuery(this).find("a[class!='custom_vid']");
                                    var divId = jQuery(this).attr('id');
                                    var newLink = urlBits[0] + '#' + divId ;
                                    link.attr("href", newLink );
                                    var form_obj = jQuery(this).find('form');
                                    form_obj.attr({  "target" : "targetvum_player_frame"});
                                    jQuery(form_obj).on("submit", function(){
                                       jQuery("#vum-modal").show(); 
                                    }) 
                                    jQuery("a.custom_vid", this).on("click", function(){
                                       jQuery("#vum-modal").show(); 
                                    }) 
                                });

                        });
                    }

                    jQuery("#vum-modal .dashicons-no, .contextual-help-tabs li a").on("click", function(){
                        jQuery("#vum-modal").hide();
                        jQuery("#targetvum_player_frame").attr('src', 'about:blank');

                    });

                });

            });
        </script>
vvvv;
        $screen->add_help_tab( array(
                'id'       => 'vum-videos',
                'title'    => 'Video Tutorials',
                'content'  => $content

            )
        );
        
    }

    function update_plugin() {
        if ( isset( $_GET['plugin'] ) && $_GET['plugin'] == 'video-user-manuals' ) {
            add_action( 'admin_head', array( $this, 'hide_stuff' ) );
        }
    }

    function hide_stuff() {
        echo '<style> .fyi ul, .fyi h2, .star-holder, small{display:none} </style>';
    }

    function is_embed_enabled( $force = false ) {
        
        $vum_enable_embed_video = get_option( "vum_enable_embed_video", false);
        
        if( ! $vum_enable_embed_video || $force === true) {
            $response = $this->request( apply_filters( 'vum_embed', self::embed_domain ), array(
                            'action' => 'check_embed_status',
                            'serial' => $this->serial)
                        );
        
            if( ! is_wp_error( $response ) ) {
                $status =  json_decode($response);
                update_option( "vum_enable_embed_video", $status, false);
                return $status;
            }else{
                return false;
            }
        }

        return $vum_enable_embed_video;
    }
    function request($url = "", $arg = array()){

        if(empty($url)) return;

        $link =  "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $fields_string = "";
        foreach($arg as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_POST, count($arg));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_REFERER, $link);
        return curl_exec($ch);
    }

    function embed_vum_video_view(){
        if(is_admin()) return;
        if(!isset($_GET['vum_video_view'])) return;
        $file = ($_GET['vum_video_view'] == 2)? 'backend' : 'frontend';
        require_once(VUM_PLUGIN_DIR . "views/" . $file . "-pop-up-player.php");
        exit;
    }
    
    // Display the embedded view for the 
    function embed_vum_ebook_view() {

        if ( is_admin() ) return;
        if ( ! isset( $_GET['vum_ebook_view'] ) ) return;
        
        include dirname( __FILE__ ) . '/views/backend-ebook-pop-up.php';
        exit;
        
    }
    

    function sections_to_show() {
        global $wpdb;

        if( $this->sectionsToShow ) return $this->sectionsToShow;

        $sections = $wpdb->get_results( "select option_name, option_value from $wpdb->options where option_name not like 'wpm_o_show_video_%' and option_name like 'wpm_o_show_%'" );
        $sectionsToShow = '';

        // Get all sections we ARE showing.
        foreach ( $sections as $section ) {
            if ( $section->option_value == '1' ) {
                $sectionsToShow .= "\'" . str_replace( 'wpm_o_show_', '', $section->option_name ) . "\',";
            }
        }

        return $this->sectionsToShow = substr_replace( $sectionsToShow, '', - 1 );
    }

    function videos_to_hide() {
        global $wpdb;

        if( $this->videosToHide ) return $this->videosToHide;


        // Get all videos we are NOT showing
        $videos     = $wpdb->get_results( "select option_name, option_value from $wpdb->options where option_name like 'wpm_o_show_video_%' and option_value = 0" );
        $vidsToHide = '';
        foreach ( $videos as $video ) {
            $vidsToHide .= str_replace( 'wpm_o_show_video_', '', $video->option_name ) . ",";
        }

        $this->videosToHide =  substr_replace( $vidsToHide, '', - 1 );
        
        return $this->videosToHide;
    }

    function display_local_videos( $local_videos ){
        
        $return =  '<div id="manual-page" class="wrap"> <h2 style="margin-bottom:8px">';
        if( get_option('wpm_o_plugin_custom_logo') ) {
            $return .= '<img src="'. vum_ssl_source( get_option('wpm_o_plugin_custom_logo') ) .'" alt="logo" style="vertical-align: -7px">&nbsp; ';
        }
        
            $return .= get_option('wpm_o_plugin_heading_video') . '</h2>';

        // Intro Text
        if(get_option('wpm_o_intro_text') != '') {
            $return .= stripslashes(get_option('wpm_o_intro_text'));
        }

            $local_title  = get_option( 'wpm_o_local_title' );
            $show_local = get_option( 'wpm_o_num_local' );
            
        if( $show_local && $local_videos ):

            $return .= stripslashes( "<h2>$local_title</h2>" ); 

            foreach($local_videos as $video_id => $video ):
                
                $return .= $this->display_vid($video_id, $video);

            endforeach;

            $return .= '<br style="clear:both" />';

        endif;
        return $return;
    }

    function get_local_videos( $force = false ) {

        if( $this->get_local_videos && ! $force ) return $this->get_local_videos;

        $show_local = get_option( 'wpm_o_num_local' );
        $local_videos = array();
        $custom_local_videos = array();
        $vum_video_embed_out = "";

        if ( $show_local ) {

            $num_local    = get_option( 'wpm_o_num_local' );
            $local_title  = get_option( 'wpm_o_local_title' );
            $count        = 1;

            while ( $count <= $num_local ) {

                if ( get_option( 'wpm_o_localvideos_' . $count . '_loc' ) == 0 ) {

                    $local_videos[$count]          = new stdClass();
                    $local_videos[$count]->name    = get_option( 'wpm_o_localvideos_' . $count . '_0' );
                    $local_videos[$count]->thumb   = get_option( 'wpm_o_localvideos_' . $count . '_1' );
                    $local_videos[$count]->vid     = get_option( 'wpm_o_localvideos_' . $count . '_2' );
                    $local_videos[$count]->desc    = get_option( 'wpm_o_localvideos_' . $count . '_3' );
                    $local_videos[$count]->image   = get_option( 'wpm_o_localvideos_' . $count . '_4' );
                    $local_videos[$count]->embed   = get_option( 'wpm_o_localvideos_' . $count . '_5' );
                    $local_videos[$count]->doembed = get_option( 'wpm_o_localvideos_' . $count . '_6' );

                } else {

                    $custom_local_videos[$count]          = new stdClass();
                    $custom_local_videos[$count]->name    = get_option( 'wpm_o_localvideos_' . $count . '_0' );
                    $custom_local_videos[$count]->thumb   = get_option( 'wpm_o_localvideos_' . $count . '_1' );
                    $custom_local_videos[$count]->vid     = get_option( 'wpm_o_localvideos_' . $count . '_2' );
                    $custom_local_videos[$count]->desc    = get_option( 'wpm_o_localvideos_' . $count . '_3' );
                    $custom_local_videos[$count]->image   = get_option( 'wpm_o_localvideos_' . $count . '_4' );
                    $custom_local_videos[$count]->embed   = get_option( 'wpm_o_localvideos_' . $count . '_5' );
                    $custom_local_videos[$count]->doembed = get_option( 'wpm_o_localvideos_' . $count . '_6' );
                    $custom_local_videos[$count]->loc     = get_option( 'wpm_o_localvideos_' . $count . '_loc' );

                }

                $count ++;
            }

        
        }
        
        if( empty($ids) ) {
            $vum_video_embed_out .= '<div id="manual-page" class="wrap">';
            if( $show_local && $local_videos ):
            
                $vum_video_embed_out .= stripslashes( "<h2>$local_title</h2>" ); 
            
                foreach($local_videos as $video_id => $video ):
                    
                    $vum_video_embed_out .= $this->display_vid($video_id, $video);
            
                endforeach;
            
                $vum_video_embed_out .= '</div><br style="clear:both" />';
            
            endif;
        
        }

        $this->get_local_videos = array($custom_local_videos, $local_videos, $show_local, $vum_video_embed_out);
        
        return $this->get_local_videos;
    }

}

require_once( VUM_PLUGIN_DIR . 'updater.php' );

$updateVUM = new VUM_PluginUpdateChecker(
    'http://wordpress.videousermanuals.com/video-user-manuals/info.json',
    __FILE__,
    'video-user-manuals',
    12,
    'wpm_external_updater'
);

$vum = new Vum();