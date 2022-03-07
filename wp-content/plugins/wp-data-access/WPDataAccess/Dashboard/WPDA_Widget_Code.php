<?php

namespace WPDataAccess\Dashboard;

use  WPDataAccess\WPDA ;
class WPDA_Widget_Code extends WPDA_Widget
{
    protected  $code_id ;
    public function __construct( $args = array() )
    {
        parent::__construct( $args );
        $this->can_share = true;
        
        if ( isset( $args['code_id'] ) ) {
            $this->code_id = $args['code_id'];
            
            if ( class_exists( 'Code_Manager\\Code_Manager' ) ) {
                $cm = new \Code_Manager\Code_Manager();
                $this->content = $cm->add_shortcode( array(
                    'id' => $this->code_id,
                ) );
            } else {
                $this->content = 'Code Manager not installed or not activated';
            }
        
        }
    
    }
    
    public function do_shortcode( $widget )
    {
        // Not implemented (use Code Manager short code)
    }
    
    public function do_embed( $widget, $target_element )
    {
    }
    
    protected function js( $is_backend = true )
    {
    }
    
    public static function widget()
    {
        $panel_name = ( isset( $_REQUEST['wpda_panel_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_panel_name'] ) ) : '' );
        // input var okay.;
        $panel_code_id = ( isset( $_REQUEST['wpda_panel_code_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_panel_code_id'] ) ) : '' );
        // input var okay.;
        $panel_column = ( isset( $_REQUEST['wpda_panel_column'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_panel_column'] ) ) : '1' );
        // input var okay.;
        $column_position = ( isset( $_REQUEST['wpda_column_position'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_column_position'] ) ) : 'prepend' );
        // input var okay.;
        $widget_sequence_nr = ( isset( $_REQUEST['wpda_widget_sequence_nr'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_widget_sequence_nr'] ) ) : '1' );
        // input var okay.;
        $wdg = new WPDA_Widget_Code( array(
            'name'      => $panel_name,
            'code_id'   => $panel_code_id,
            'column'    => $panel_column,
            'position'  => $column_position,
            'widget_id' => $widget_sequence_nr,
        ) );
        WPDA::sent_header( 'text/html; charset=UTF-8' );
        echo  $wdg->container() ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        wp_die();
    }
    
    public static function refresh()
    {
        echo  static::msg( 'ERROR', 'Method not available for this panel type' ) ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        wp_die();
    }

}