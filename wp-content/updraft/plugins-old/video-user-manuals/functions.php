<?php
function vum_get_api_params( $url ) {

	$url->lang           = get_option( 'wpm_o_lang' );
	$url->branding_img = vum_ssl_source( get_option( 'wpm_o_branding_img' ) );
	$url->branding_logo = vum_ssl_source( get_option( 'wpm_o_branding_logo' ) );
	$url->video_image  = vum_ssl_source( get_option( 'wpm_o_custom_vid_placeholder' ) );

	$url_params = '';

	foreach ( $url as $k => $v ) {
		
		if ( $v === FALSE ) $v = '0';

		$url_params .= $k . ':\'' . $v . '\',';

	}

	if (substr($url_params, -1, 1) == ',') $url_params = substr_replace( $url_params, '', - 1 );
	
	return $url_params;

}

function vum_domain( $url ) {

	return vum_ssl_source( Vum::vum_domain . $url );

}

function vum_ssl_source( $source = "" ) {

	if( is_ssl() ){
		$source = str_replace( 'http://', 'https://', $source );
	}

	return $source;
}