<?php 
/** @file backend-ebook.php - backend view to open ebook when "change popup URL" option is set */

// Select version of manual icon / logo
$custom_ebook_img = trim( get_option( 'wpm_o_custom_ebook_img' ) );
if ( empty( $custom_ebook_img ) ) {
	$custom_ebook_img = '//vum.s3.amazonaws.com/wp/assets/manual-ebook-transparent.png';
}

$urlvars = array(
	'lang'             => get_option( 'wpm_o_lang' ),
	'wp_version'       => get_bloginfo( 'version' ),
	'user_id'          => get_option( 'wpm_o_user_id' ),
	'custom_ebook_img' => vum_ssl_source( get_option( 'wpm_o_custom_ebook_img' ) ),
);

$urlvars_encoded = http_build_query( $urlvars );
// var_dump( $urlvars_encoded );

?>
<a href="javascript:void(0)" id="ebook_link">
	<img src="<?php echo vum_ssl_source( $custom_ebook_img); ?>" alt="Click here to open manual" title="click here to open manual" />
</a>
<script type="text/javascript">
	document.getElementById( 'ebook_link' ).onclick = function () {
		window.open( 
			'<?php echo site_url( '?vum_ebook_view=1&' . $urlvars_encoded ) ?>',
			'welcome',
			'width=950,height=600,menubar=0,status=0,location=0,toolbar=0,scrollbars=0' 
		)
	};
</script>