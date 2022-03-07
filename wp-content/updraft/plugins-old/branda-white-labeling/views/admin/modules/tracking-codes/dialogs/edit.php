<input type="hidden" name="branda[id]" value="<?php echo esc_attr( $id ); ?>" class="branda-tracking-codes-id" />
<div class="sui-tabs sui-tabs-flushed">
	<div data-tabs="">
		<div class="active"><?php esc_attr_e( 'General', 'ub' ); ?></div>
		<div><?php esc_attr_e( 'Location', 'ub' ); ?></div>
	</div>
	<div data-panes="">
		<div class="active">
			<div class="sui-form-field branda-general-active">
				<label class="sui-label"><?php esc_attr_e( 'Status', 'ub' ); ?></label>
				<div class="sui-side-tabs sui-tabs">
					<div class="sui-tabs-menu">
						<label class="sui-tab-item<?php echo 'off' === $active ? ' active' : ''; ?>">
							<input type="radio" data-name="branda[active]"
								   name="branda[<?php echo esc_attr( $id ); ?>][active]"
								   value="off" <?php checked( $active, 'off' ); ?>>

							<?php esc_attr_e( 'Inactive', 'ub' ); ?>
						</label>
						<label class="sui-tab-item<?php echo 'on' === $active ? ' active' : ''; ?>">
							<input type="radio" data-name="branda[active]"
								   name="branda[<?php echo esc_attr( $id ); ?>][active]"
								   value="on" <?php checked( $active, 'on' ); ?>>

							<?php esc_attr_e( 'Active', 'ub' ); ?>
						</label>
					</div>
				</div>
			</div>
			<div class="sui-form-field branda-general-title">
				<label for="branda-general-title-<?php echo esc_attr( $id ); ?>" class="sui-label"><?php esc_attr_e( 'Name', 'ub' ); ?></label>
				<input id="branda-general-title-<?php echo esc_attr( $id ); ?>" type="text" name="branda[title]" value="<?php echo esc_attr( $title ); ?>" aria-describedby="input-description" class="sui-form-control" placeholder="<?php esc_attr_e( 'E.g GA views tracking', 'ub' ); ?>" />
			</div>
			<div class="sui-form-field branda-general-code" data-id="<?php echo esc_attr( $id ); ?>">
				<label for="branda-general-code-<?php echo esc_attr( $id ); ?>" class="sui-label"><?php esc_attr_e( 'Tracking Code', 'ub' ); ?></label>
				<textarea id="branda-general-code-<?php echo esc_attr( $id ); ?>" name="branda[code]" class="sui-ace-editor ub_html_editor" rows="10" placeholder="<?php esc_attr_e( 'Paste your tracking code here…', 'ub' ); ?>"><?php echo $code; ?></textarea>
			</div>
		</div>
		<div>
			<div class="sui-form-field branda-location-place">
				<label class="sui-label"><?php esc_attr_e( 'Insert Position', 'ub' ); ?></label>
				<div class="sui-side-tabs sui-tabs">
					<div class="sui-tabs-menu">
						<label class="sui-tab-item<?php echo 'head' === $place ? ' active' : ''; ?>">
							<input type="radio" data-name="branda[place]"
								   name="branda[<?php echo esc_attr( $id ); ?>][place]"
								   value="head" <?php checked( $place, 'head' ); ?>>

							<?php esc_attr_e( 'Inside &lt;head&gt;', 'ub' ); ?>
						</label>
						<label class="sui-tab-item<?php echo 'body' === $place ? ' active' : ''; ?>">
							<input type="radio" data-name="branda[place]"
								   name="branda[<?php echo esc_attr( $id ); ?>][place]"
								   value="body" <?php checked( $place, 'body' ); ?>>

							<?php esc_attr_e( 'After &lt;body&gt;', 'ub' ); ?>
						</label>
						<label class="sui-tab-item<?php echo 'footer' === $place ? ' active' : ''; ?>">
							<input type="radio" data-name="branda[place]"
								   name="branda[<?php echo esc_attr( $id ); ?>][place]"
								   value="footer" <?php checked( $place, 'footer' ); ?>>

							<?php esc_attr_e( 'Before &lt;/body&gt;', 'ub' ); ?>
						</label>
					</div>
				</div>
			</div>
<?php
/*******************************
 *
 * LOCATION
 *******************************/
?>
			<div class="sui-form-field branda-location-filter">
				<label class="sui-label"><?php esc_attr_e( 'Location Filters', 'ub' ); ?></label>
				<div class="sui-side-tabs sui-tabs">
					<div class="sui-tabs-menu">
						<label class="sui-tab-item<?php echo 'off' === $filter ? ' active' : ''; ?>">
							<input type="radio" data-name="branda[filter]"
								   name="branda[<?php echo esc_attr( $id ); ?>][filter]"
								   value="off" <?php checked( $filter, 'off' ); ?>>

							<?php esc_attr_e( 'Disable', 'ub' ); ?>
						</label>
						<label class="sui-tab-item<?php echo 'on' === $filter ? ' active' : ''; ?>">
							<input type="radio" data-name="branda[filter]"
								   name="branda[<?php echo esc_attr( $id ); ?>][filter]"
								   value="on" <?php checked( $filter, 'on' ); ?>
								   data-name="filter" data-tab-menu="branda-tracking-codes-filter-status-on">

							<?php esc_attr_e( 'Enable', 'ub' ); ?>
						</label>
					</div>
					<div class="sui-tabs-content">
						<div class="sui-tab-boxed<?php echo 'on' === $filter ? ' active' : ''; ?>" data-tab-content="branda-tracking-codes-filter-status-on">
							<div class="sui-form-field branda-location-users">
								<label for="branda-location-users-<?php echo esc_attr( $id ); ?>" class="sui-label"><?php esc_attr_e( 'Users', 'ub' ); ?></label>
								<span class="sui-description"><?php esc_attr_e( 'You can choose logged status and/or user role.', 'ub' ); ?></span>
								<select name="branda[users]" multiple="multiple" class="sui-select branda-<?php echo esc_attr( $module ); ?>-filter-users" >
<?php
foreach ( $data_users as $value => $label ) {
	$extra    = '';
	$selected = is_array( $users ) && in_array( $value, $users ) ? ' selected="selected"' : '';
	if ( is_array( $users ) && in_array( 'anonymous', $users ) && 'anonymous' !== $value ) {
		$selected = '';
		$extra    = ' disabled="disabled"';
	}

	printf(
		'<option value="%s"%s%s>%s</option>',
		esc_attr( $value ),
		$selected,
		$extra,
		esc_html( $label )
	);
}
?>
								</select>
							</div>
<?php
/*******************************
 *
 * AUTHORS
 *******************************/
?>
							<div class="sui-form-field branda-Location-authors">
								<label for="branda-location-authors-<?php echo esc_attr( $id ); ?>" class="sui-label"><?php esc_attr_e( 'Authors', 'ub' ); ?></label>
								<span class="sui-description"><?php esc_attr_e( 'This filter will be used only on single entry.', 'ub' ); ?></span>
								<select name="branda[authors]" class="sui-select" multiple="multiple">
<?php
foreach ( $data_authors as $value => $label ) {
	printf(
		'<option value="%s"%s>%s</option>',
		esc_attr( $value ),
		is_array( $authors ) && in_array( $value, $authors ) ? ' selected="selected"' : '',
		esc_html( $label )
	);
}
?>
								</select>
							</div>
<?php
/*******************************
 *
 * CONTENT TYPE
 *******************************/
?>
							<div class="sui-form-field branda-Location-archives">
								<label for="branda-location-archives-<?php echo esc_attr( $id ); ?>" class="sui-label"><?php esc_attr_e( 'Content Type', 'ub' ); ?></label>
								<select name="branda[archives]" class="sui-select sui-select sui-select" multiple="multiple">
								<?php
								foreach ( $data_archives as $value => $label ) {
									printf(
										'<option value="%s"%s>%s</option>',
										esc_attr( $value ),
										is_array( $archives ) && in_array( $value, $archives ) ? ' selected="selected"' : '',
										esc_html( $label )
									);
								}
								?>
								</select>
								<span class="sui-description"><?php esc_attr_e( 'You can choose to add the code to certain content types.', 'ub' ); ?></span>
							</div>
<?php
/*******************************
 *
 * SITES
 *******************************/
if ( $is_network_admin ) {
	?>
							<div class="sui-form-field branda-location-sites">
								<label for="branda-location-sites-<?php echo esc_attr( $id ); ?>" class="sui-label"><?php esc_attr_e( 'Sites', 'ub' ); ?></label>
								<span class="sui-description"><?php esc_attr_e( 'This filter will be used only on single entry.', 'ub' ); ?></span>
								<select name="branda[sites]" class="sui-select" multiple="multiple">
	<?php
	foreach ( $data_sites as $site ) {
		printf(
			'<option value="%s"%s>%s - %s</option>',
			esc_attr( $site['id'] ),
			is_array( $sites ) && in_array( $site['id'], $sites ) ? ' selected="selected"' : '',
			esc_html( $site['title'] ),
			esc_html( $site['subtitle'] )
		);
	}
	?>
								</select>
							</div>
<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
