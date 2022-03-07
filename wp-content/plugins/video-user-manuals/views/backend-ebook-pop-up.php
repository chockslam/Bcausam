<?php 
/** @file backend-ebook.php - Ebook popup view for when "change popup URL" option is set */

$lang = isset( $_GET[ 'lang' ] ) ? $_GET[ 'lang' ] : '';
$wp_version = isset( $_GET[ 'wp_version' ] ) ? $_GET[ 'wp_version' ] : '';
$user_id = isset( $_GET[ 'user_id' ] ) ? $_GET[ 'user_id' ] : '';

// The URL of the actual popup
$popup_url = vum_domain('manual.php?lang='. urlencode( $lang ) .'&wp_version='. urlencode( $wp_version ) .'&user_id=' . urlencode( $user_id ));

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>ebook</title>
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<style media="screen">
			html, body, iframe { padding:0;margin:0;outline:none;border:0; }
			html, body { overflow:hidden }
		</style>
	</head>
	<body>
		<iframe src="<?php echo esc_attr( $popup_url ) ?>" allowfullscreen></iframe>
		<script type="text/javascript">
			( function( $ ) {
				function resize() {
					var iframe = $( 'iframe' );
					iframe.height( $( window ).height() );
					iframe.width( $( window ).width() );
				}
				$( document ).ready( function() {
					resize();
				} );
				$( window ).resize( function () {
					resize();
				} );
			} )( jQuery );
		</script>
	</body>
</html>