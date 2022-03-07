<div id="assistant-german-market-panel" class="dashboard-column dashboard-column1 welcome-panel assistant-dashboard-panel">
    <a class="notice-dismiss" href="<?php echo esc_url( add_query_arg( array( 'close_german_market_panel' => 1 ) ) ); ?>"><?php _e( 'Dismiss' ); ?></a>
    
	<div class="inside">
		<figure class="full-width">
			<img src="<?php echo Ionos_Assistant::get_images_url( 'dashboard/wp-german-market.png' ) ?>" alt="German market + WooCommerce" />
		</figure>
		<div class="dashboard-panel-title woocommerce-message">
			<h2><?php _e( 'Legally compliant with your WooCommerce, in German and EU markets.', 'ionos-assistant' ); ?></h2>
            <a class="button button-primary install-plugin" data-plugin="german-market" href="#">
                <span class="setup"><?php _e( 'Setup German Market', 'ionos-assistant' ); ?></span>
                <span class="installed hidden"><?php _ex( 'Installed!', 'plugin' ); ?></span>
                <span class="failed hidden"><?php _e( 'Installation Failed' ); ?></span>
            </a>
            <a class="learn-more-link" target="_blank" href="https://www.youtube.com/watch?v=spYbS4MACzI">
                <?php _e( 'Learn more', 'ionos-assistant' ); ?>
            </a>
		</div>
	</div>
</div>