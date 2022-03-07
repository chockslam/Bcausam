<?php echo $this->display_local_videos( $local_videos ); ?>
    <div id="ajax_msg"></div>
    <div id="ajax_content"></div>
    <script type="text/javascript">
        
        jQuery(document).ready(function() 
        {
            jQuery.getJSON('<?php echo apply_filters( 'vum_iframe', self::iframe_url ); ?>', {<?php  echo $url_params;?>},
                function(data, textStatus)
                {
                    jQuery('#ajax_content').append(data);

<?php

if( $show_local && isset($custom_local_videos) ):

    foreach($custom_local_videos as $video_id => $video ): ?>
        jQuery('#section-<?php echo $video->loc;?>').append('<?php echo addslashes($this->display_vid($video_id, $video));?>');

<?php endforeach;

endif;

?>
                    var entireUrl = jQuery(location).attr('href');
                    var urlBits = entireUrl.split('#');
                    
                    jQuery(".video-container").each(function()
                    {
                        var link = jQuery(this).find("a");
                        var divId = jQuery(this).attr('id');
                        var newLink = urlBits[0] + '#' + divId ;
                        link.attr("href", newLink );
                        <?php
                        	$popupsetting = get_option( 'wpm_o_change_popup_url', false);
		                    if( $popupsetting && $popupsetting == '1'){?>
		                      var form_obj = jQuery(this).find("form");
	                      	  form_obj.attr( "action",  "<?php echo site_url("/?vum_video_view=2")?>");
	                      	 <?php
	                        }
	                        
                        ?>
                    });

                    if( window.location.hash )
                    {
                        jQuery('html,body').animate({scrollTop:jQuery(window.location.hash).offset().top}, 500);
                        
                        jQuery( window.location.hash ).addClass('vum-highlight'); 
                    }
            });
        });

    </script>
 