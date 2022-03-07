<?php

namespace WPDataAccess\Dashboard;

use  WPDataAccess\WPDA ;
abstract class WPDA_Widget
{
    const  WIDGET_ADD = 'WPDA_WIDGET_ADD' ;
    const  WIDGET_REFRESH = 'WPDA_WIDGET_REFRESH' ;
    protected static  $widget_sequence_nr = 0 ;
    protected  $column = 1 ;
    protected  $can_share = false ;
    protected  $has_layout = false ;
    protected  $has_setting = false ;
    protected  $can_refresh = false ;
    protected  $name = 'No name' ;
    protected  $title = 'No title' ;
    protected  $content = 'Loading...' ;
    protected  $wp_nonce = null ;
    protected  $widget_id = 0 ;
    protected  $position = 'append' ;
    protected  $state = null ;
    protected  $is_locked = false ;
    protected  $share = array(
        'post'  => 'true',
        'page'  => 'true',
        'embed' => 'block',
        'allow' => array(),
    ) ;
    public function __construct( $args = array() )
    {
        wp_enqueue_script( 'jquery-ui-widget' );
        if ( isset( $args['name'] ) ) {
            $this->name = $args['name'];
        }
        if ( isset( $args['column'] ) ) {
            $this->column = $args['column'];
        }
        if ( isset( $args['title'] ) ) {
            $this->title = $args['title'];
        }
        if ( isset( $args['content'] ) ) {
            $this->content = $args['content'];
        }
        if ( isset( $args['position'] ) && 'prepend' === $args['position'] ) {
            $this->position = 'prepend';
        }
        
        if ( isset( $args['widget_id'] ) ) {
            $this->widget_id = $args['widget_id'];
            // Used to add widgets via ajax
        } else {
            $this->widget_id = ++self::$widget_sequence_nr;
            // Used to add widgets on page load
        }
        
        if ( isset( $args['is_locked'] ) ) {
            $this->is_locked = true === $args['is_locked'] || 'true' === $args['is_locked'];
        }
        if ( isset( $args['share'] ) && isset(
            $args['share']['roles'],
            $args['share']['users'],
            $args['share']['post'],
            $args['share']['page'],
            $args['share']['embed'],
            $args['share']['allow']
        ) ) {
            $this->share = array(
                'roles' => $args['share']['roles'],
                'users' => $args['share']['users'],
                'post'  => $args['share']['post'],
                'page'  => $args['share']['page'],
                'embed' => $args['share']['embed'],
                'allow' => $args['share']['allow'],
            );
        }
        $this->state = ( isset( $args['state'] ) ? $args['state'] : 'new' );
        $this->wp_nonce = wp_create_nonce( static::WIDGET_REFRESH . WPDA::get_current_user_login() );
    }
    
    protected function container()
    {
        ob_start();
        ?>
			<script type="application/javascript" class="wpda-widget-<?php 
        echo  esc_attr( $this->widget_id ) ;
        ?>">
				jQuery(function() {
					var widget = `<?php 
        echo  $this->html() ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>`;

					jQuery("#wpda-dashboard-column-<?php 
        echo  esc_attr( $this->column ) ;
        ?>").<?php 
        echo  esc_attr( $this->position ) ;
        ?>(widget);
					jQuery("#wpda-widget-<?php 
        echo  esc_attr( $this->widget_id ) ;
        ?>").data("name", "<?php 
        echo  esc_attr( $this->name ) ;
        ?>" );

					jQuery("#wpda-widget-<?php 
        echo  esc_attr( $this->widget_id ) ;
        ?> .wpda-widget-close").on("click", function() {
						removePanelFromDashboard(jQuery(this).closest('.wpda-widget'));
					});
				});
			</script>
			<?php 
        $this->js();
        return ob_get_clean();
    }
    
    protected function html()
    {
        $share = '';
        $layout = '';
        $setting = '';
        $refresh = ( $this->can_refresh ? "<i class='fas fa-sync-alt wpda-widget-refresh wpda_tooltip' title='Refresh'></i> &nbsp;" : '' );
        $close = ( !$this->is_locked ? '<i class="fas fa-window-close wpda-widget-close wpda_tooltip" title="Close"></i>' : '' );
        $widget = <<<EOF
                <div id="wpda-widget-{$this->widget_id}" data-id="{$this->widget_id}" class="wpda-widget ui-widget">
                    <div class="wpda-widget-content">
                        <div class="ui-widget-header">
                            <span>{$this->name}</span>
                            <span class="icons">
\t\t\t\t\t\t\t\t{$share}
\t\t\t\t\t\t\t\t{$layout}
\t\t\t\t\t\t\t\t{$setting}
\t\t\t\t\t\t\t\t{$refresh}
\t\t\t\t\t\t\t\t{$close}
\t\t\t\t\t\t\t</span>
                        </div>
                        <div class="ui-widget-content">
                            {$this->content}
                        </div>
                    </div>
                </div>
EOF;
        return $widget;
    }
    
    protected static function check_cors( $widget )
    {
        
        if ( isset( $_POST['wpda_caller'] ) && 'embedded' === $_POST['wpda_caller'] ) {
            $share = ( isset( $widget['widgetShare'] ) ? $widget['widgetShare'] : null );
            
            if ( 'block' === $share['embed'] ) {
                WPDA::sent_header( 'application/json', '*' );
                echo  static::msg( 'ERROR', 'No access' ) ;
                // phpcs:ignore WordPress.Security.EscapeOutput
                wp_die();
            } else {
                
                if ( '*' === $share['embed'] ) {
                    WPDA::sent_header( 'application/json', '*' );
                    return true;
                } else {
                    // Access is already checked with sonce token
                    WPDA::sent_header( 'application/json', '*' );
                    return true;
                }
            
            }
        
        }
        
        return false;
    }
    
    protected abstract function js();
    
    // Method to add custom JavaScript code
    // Add widget to dashboard
    public function add()
    {
        echo  $this->container() ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        ?>
			<script type="application/javascript">
				jQuery(function() {
					increaseWidgetSequenceNr();
				});
			</script>
			<?php 
    }
    
    public static abstract function widget();
    
    public static function ajax_widget()
    {
        $wp_nonce = ( isset( $_POST['wp_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_nonce'] ) ) : '' );
        
        if ( !wp_verify_nonce( $wp_nonce, static::WIDGET_ADD . WPDA::get_current_user_login() ) ) {
            WPDA::sent_header( 'application/json' );
            echo  static::msg( 'ERROR', 'Token expired, please refresh page' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            wp_die();
        }
        
        static::widget();
    }
    
    public static abstract function refresh();
    
    public static function ajax_refresh()
    {
        $wp_nonce = ( isset( $_POST['wp_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_nonce'] ) ) : '' );
        
        if ( !wp_verify_nonce( $wp_nonce, static::WIDGET_REFRESH . WPDA::get_current_user_login() ) ) {
            WPDA::sent_header( 'application/json' );
            echo  static::msg( 'ERROR', 'Token expired, please refresh page' ) ;
            // phpcs:ignore WordPress.Security.EscapeOutput
            wp_die();
        }
        
        static::refresh();
    }
    
    protected static function msg( $status, $msg )
    {
        $error = array(
            'status' => $status,
            'msg'    => $msg,
        );
        return json_encode( $error );
    }

}