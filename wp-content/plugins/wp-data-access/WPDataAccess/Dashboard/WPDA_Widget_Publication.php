<?php

namespace WPDataAccess\Dashboard;

use  WPDataAccess\Data_Tables\WPDA_Data_Tables ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model ;
use  WPDataAccess\WPDA ;
class WPDA_Widget_Publication extends WPDA_Widget
{
    protected  $pub_id ;
    protected  $pub_content ;
    protected  $table_name ;
    protected  $search_header ;
    public function __construct( $args = array() )
    {
        parent::__construct( $args );
        $this->can_share = true;
        $this->can_refresh = true;
        
        if ( isset( $args['pub_id'] ) ) {
            $this->pub_id = $args['pub_id'];
            $embedding = isset( $args['embedding'] ) && true === $args['embedding'];
            $wpda_data_tables = new WPDA_Data_Tables();
            $this->pub_content = $wpda_data_tables->show(
                $this->pub_id,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                true,
                $embedding
            );
            $wpda_publication = WPDA_Publisher_Model::get_publication( $this->pub_id );
            $this->table_name = $wpda_publication['0']['pub_table_name'];
            try {
                $json = json_decode( $wpda_publication['0']['pub_table_options_advanced'] );
                if ( isset( $json->wpda_searchbox ) && ('header' === $json->wpda_searchbox || 'both' === $json->wpda_searchbox) ) {
                    $this->search_header = true;
                }
            } catch ( \Exception $e ) {
                $this->search_header = false;
            }
        }
    
    }
    
    public function do_shortcode( $widget )
    {
        // Not implemented (use Data Publisher short code)
    }
    
    public function do_embed( $widget, $target_element )
    {
    }
    
    protected function js( $is_backend = true )
    {
        ?>
			<script type="application/javascript">
				jQuery(function() {
					jQuery("#wpda-widget-<?php 
        echo  esc_attr( $this->widget_id ) ;
        ?>").find(".wpda-widget-refresh").on("click", function(e, action = null) {
						if (action==="refresh") {
							jQuery("#<?php 
        echo  esc_attr( $this->table_name ) . esc_attr( $this->pub_id ) ;
        ?>").DataTable().ajax.json();
						} else {
							jQuery("#<?php 
        echo  esc_attr( $this->table_name ) . esc_attr( $this->pub_id ) ;
        ?>").DataTable().draw("page");
						}
						jQuery("#<?php 
        echo  esc_attr( $this->table_name ) . esc_attr( $this->pub_id ) ;
        ?>").DataTable().responsive.recalc();
						<?php 
        
        if ( $this->search_header && $is_backend ) {
            ?>
							post_publication_widget("<?php 
            echo  esc_attr( $this->table_name ) ;
            ?>", "<?php 
            echo  esc_attr( $this->pub_id ) ;
            ?>");
							<?php 
        }
        
        ?>
					});
				});
			</script>
			<?php 
    }
    
    protected function container()
    {
        ob_start();
        echo  parent::container() ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        $post_publication = ( $this->search_header ? "post_publication_widget('{$this->table_name}', '{$this->pub_id}');" : '' );
        $post_content = "\n\t\t\t\t<div id='wpda-panel-pub-id-{$this->pub_id}' style='display: block'>\n\t\t\t\t\t{$this->pub_content}\n\t\t\t\t</div>\n\t\t\t\t<script type='application/javascript'>\n\t\t\t\tjQuery(function() {\n\t\t\t\t\tsetTimeout(waitUntilWidgetIsLoaded_{$this->pub_id}, 1000);\n\t\t\t\t\tfunction waitUntilWidgetIsLoaded_{$this->pub_id}() {\n\t\t\t\t\t\tif (jQuery('#wpda-widget-{$this->widget_id} div.ui-widget-content').length>0) {\n\t\t\t\t\t\t\tjQuery('#wpda-widget-{$this->widget_id} div.ui-widget-content').html('');\n\t\t\t\t\t\t\tjQuery('#wpda-panel-pub-id-{$this->pub_id}').appendTo(jQuery('#wpda-widget-{$this->widget_id} div.ui-widget-content'));\n\t\t\t\t\t\t\tjQuery('#{$this->table_name}{$this->pub_id}').DataTable().responsive.recalc();\n\t\t\t\t\t\t\t{$post_publication}\n\t\t\t\t\t\t\t// console.log('WP Data Access publication libraries loaded...');\n\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\tsetTimeout(waitUntilWidgetIsLoaded_{$this->pub_id}, 1000);\n\t\t\t\t\t\t\tconsole.log('Waiting for WP Data Access publication libraries to be loaded...');\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\t\t\t\t});\n\t\t\t\t</script>\n\t\t\t";
        echo  $post_content ;
        // phpcs:ignore WordPress.Security.EscapeOutput
        return ob_get_clean();
    }
    
    public static function widget()
    {
        $panel_name = ( isset( $_REQUEST['wpda_panel_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_panel_name'] ) ) : '' );
        // input var okay.;
        $panel_pub_id = ( isset( $_REQUEST['wpda_panel_pub_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_panel_pub_id'] ) ) : '' );
        // input var okay.;
        $panel_column = ( isset( $_REQUEST['wpda_panel_column'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_panel_column'] ) ) : '1' );
        // input var okay.;
        $column_position = ( isset( $_REQUEST['wpda_column_position'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_column_position'] ) ) : 'prepend' );
        // input var okay.;
        $widget_sequence_nr = ( isset( $_REQUEST['wpda_widget_sequence_nr'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpda_widget_sequence_nr'] ) ) : '1' );
        // input var okay.;
        $wdg = new WPDA_Widget_Publication( array(
            'name'      => $panel_name,
            'pub_id'    => $panel_pub_id,
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