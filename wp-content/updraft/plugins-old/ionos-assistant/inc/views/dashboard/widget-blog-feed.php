<ul>
	<?php if ( empty( $feed_items ) ) : ?>
		<li class="no-item">
            <?php _e( 'No items' ); ?>.
        </li>
	<?php else : ?>
		<?php // Loop through each feed item and display each item as a hyperlink. ?>
		<?php foreach ( $feed_items as $key => $item ) : ?>
			<?php /** @var SimplePie_Item $item  */ ?>
			<li class="feed-item">
				<a target="_blank" class="news-title" href="<?php echo esc_url( $item->get_permalink() ); ?>" title="<?php echo esc_html( $item->get_title() ); ?>">
					<?php echo esc_html( $item->get_title() ); ?>
				</a>
				<p>
					<a target="_blank" href="<?php echo esc_url( $item->get_permalink() ); ?>" title="<?php echo esc_html( $item->get_title() ); ?>">
						<?php echo wp_trim_words( $item->get_description(), 25, null ); ?>
					</a>
				</p>
			</li>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( ! empty( $more_url ) ): ?>
		<li class="more-items">
			<a target="_blank" class="button button-primary" href="<?php echo $more_url; ?>" title=""><?php _e( 'community_widget_link_label', 'ionos-assistant' ); ?></a>
		</li>
	<?php endif; ?>
</ul>