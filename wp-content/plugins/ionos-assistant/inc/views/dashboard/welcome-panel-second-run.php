<?php
    $blog_url = Ionos_Assistant_Config::get( 'blog_{market}', 'links', 'blog_US' );
    $cp_applications_url = Ionos_Assistant_Config::get( 'control_panel_applications_{market}', 'links' );
?>

<div id="assistant-help-panel" class="dashboard-column dashboard-column4 welcome-panel assistant-dashboard-panel">
	<div class="dashboard-row">
		<?php Ionos_Assistant_View::load_template( 'dashboard/branded-wp-column' ); ?>
        <div class="dashboard-column dashboard-column1 assistant-wordpress-help">
            <div class="inside">
                <h2><?php _e( 'Your WordPress is now ready to get going.', 'ionos-assistant' ); ?></h2>
                
                <div class="assistant-links">
                    <div class="assistant-links-start">
                        <h3><?php _e( 'Next Steps', 'ionos-assistant' ); ?></h3>
                        <ul>
                            <?php if ( 'page' == get_option( 'show_on_front' ) && ! get_option( 'page_for_posts' ) ) : ?>
                                <li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page">'.__( 'Edit your front page', 'ionos-assistant' ).'</a>', get_edit_post_link( get_option( 'page_on_front' ) ) ); ?></li>
                                <li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">'.__( 'Add additional pages' ).'</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
                            <?php elseif ( 'page' == get_option( 'show_on_front' ) ) : ?>
                                <li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page">'.__( 'Edit your front page' ).'</a>', get_edit_post_link( get_option( 'page_on_front' ) ) ); ?></li>
                                <li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">'.__( 'Add additional pages' ).'</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
                                <li><?php printf( '<a href="%s" class="welcome-icon welcome-write-blog">'.__( 'Add a blog post' ).'</a>', admin_url( 'post-new.php' ) ); ?></li>
                            <?php else : ?>
                                <li><?php printf( '<a href="%s" class="welcome-icon welcome-write-blog">'.__( 'Write your first blog post', 'ionos-assistant' ).'</a>', admin_url( 'post-new.php' ) ); ?></li>
                                <li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">'.__( 'Add an About page', 'ionos-assistant' ).'</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
                            <?php endif; ?>
                            <li><?php printf( '<a href="%s" class="welcome-icon welcome-view-site">'.__( 'View your site' ).'</a>', home_url( '/' ) ); ?></li>
                        </ul>
                    </div>
                    <div class="assistant-links-advanced">
                        <h3><?php _e( 'More Actions' ); ?></h3>
                        <ul>
                            <?php if ( $blog_url ): ?>
                                <li>
                                    <a href="<?php echo $blog_url; ?>" target="_blank" class="welcome-icon welcome-learn-more">
                                        <?php esc_html_e( 'first_steps_community_link', 'ionos-assistant' ); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a href="<?php echo wp_customize_url(); ?>" class="welcome-icon dashicons-admin-appearance">
                                    <?php _e( 'customize_theme_in_widget', 'ionos-assistant' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="welcome-icon dashicons-admin-plugins">
                                    <?php esc_html_e( 'dashboard_widget_plugins', 'ionos-assistant' ); ?>
                                </a>
                            </li>
                            <?php if ( ! empty( $is_product_domain ) && $is_product_domain === true && ! empty( $cp_applications_url ) ) : ?>
                                <li><?php printf( '<a href="%s" target="_blank" class="welcome-icon dashicons-admin-links">'.__( 'dashboard_change_domain', 'ionos-assistant' ).'</a>', $cp_applications_url ); ?></li>
                            <?php elseif ( ! is_ssl() && ! empty( $cp_applications_url ) ) : ?>
                                <li><?php printf( '<a href="%s" target="_blank" class="welcome-icon dashicons-lock">'.__( 'dashboard_activate_ssl', 'ionos-assistant' ).'</a>', $cp_applications_url ); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>
