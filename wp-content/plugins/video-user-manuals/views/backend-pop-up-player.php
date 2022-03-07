<html>
<head>
    <title><?php echo get_option( 'wpm_o_plugin_heading_user' ); ?></title>
</head>
<body style="margin:0px;padding:0px;overflow:hidden">
 <iframe src="" frameborder="0" name="targetvum_player_frame" allowfullscreen style="overflow:hidden;height:100%;width:100%" height="100%" width="100%"></iframe>
	<form id="vum_player_frame"  runat="server" target="targetvum_player_frame" method="post" action="<?php echo vum_domain('player.php')?>">
		<input type="hidden" name="user_id" value="<?php echo $_POST['user_id']?>">
		<input type="hidden" name="wp_version" value="<?php echo $_POST['wp_version']?>">
		<input type="hidden" name="lang" value="<?php echo $_POST['lang']?>">
		<input type="hidden" name="branding_img" value="<?php echo vum_ssl_source( $_POST['branding_img'] )?>">
		<input type="hidden" name="vid" value="<?php echo $_POST['vid']?>">
		<input type="hidden" name="plugin_ver" value="<?php echo $_POST['plugin_ver']?>">
		<input type="hidden" name="subtitles_id" value="<?php echo $_POST['subtitles_id']?>">
		<input type="hidden" name="video_thumb" value="<?php echo $_POST['video_thumb']?>">
		<?php if( isset($_POST['hce']) ):?>
		<input type="hidden" name="hce" value="<?php echo $_POST['hce']?>">
		<?php endif?>
		<input type="hidden" name="video_thumb" value="<?php echo $_POST['video_thumb']?>">
		<input type="image" src="<?php echo $_POST['View']?>" name="View">
	</form>
<script type="text/javascript">
	document.getElementById('vum_player_frame').submit();
</script>
</body>
</html>