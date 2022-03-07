<?php
	$logo_src = Ionos_Assistant_Branding::get_logo();
	$logo_alt = sprintf( __( 'by %s' ), Ionos_Assistant_Branding::get_brand_name() );
	$visual = Ionos_Assistant_Branding::get_visual( 1 );
?>
<div class="dashboard-column dashboard-column1 branded-wordpress-column">
    <div class="inside">
        <div class="branded-wordpress-img">
            <img src="<?php echo $visual; ?>" alt="WordPress" />
        </div>
        <?php if ( $logo_src ): ?>
            <img src="<?php echo $logo_src; ?>" alt="<?php echo $logo_alt; ?>" class="logo" />
		<?php endif; ?>
    </div>
</div>