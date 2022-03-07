<script type="text/javascript">
    // Used on Videos - little arrow to rotate 
    var wpm_form_action = jQuery("#wpm_form").attr("action");
    (function($){
        var _e = document.createElement("canvas").width
        $.fn.cssrotate = function(d) {
            return this.css({
                '-moz-transform':'rotate('+d+'deg)',
                '-webkit-transform':'rotate('+d+'deg)',
                '-o-transform':'rotate('+d+'deg)',
                '-ms-transform':'rotate('+d+'deg)'
            }).prop("rotate", _e ? d : null)
         };

         var $_fx_step_default = $.fx.step._default;

         $.fx.step._default = function (fx){
            if(fx.prop != "rotate")return $_fx_step_default(fx);
            if(typeof fx.elem.rotate == "undefined")fx.start = fx.elem.rotate = 0;
            $(fx.elem).cssrotate(fx.now)
         };
    })(jQuery);


    jQuery(document).ready(function(){

	//check if you active tab is in embed videos
	check_if_embed_activated();
    
    jQuery( "select[name='lang']" ).change( function() {
        
        var selected = jQuery(this).val();
        
        if( selected == 'pt' || selected == 'it' || selected == 'fr' || selected == 'de' || selected == 'es' ) {
            alert( "We no longer support this language. The videos will not be updated after WordPress 3.7." );
        }
        
    });
 
    // Watch the labels on the form.
    jQuery('label').click(function()
    {
    	labelID = jQuery(this).attr('for');
    	if (jQuery(this).hasClass('active')) {
    		jQuery('#'+labelID).slideUp();
    		jQuery(".horizontal-icon", this).animate({rotate:0},{duration:500})    		
    		jQuery(this).removeClass('active');
    	} else {
    		jQuery('#'+labelID).slideDown();
    		jQuery(".horizontal-icon", this).animate({rotate:90},{duration:500})
    		jQuery(this).addClass('active');    	
    	}
      
    });

    // Get array of section classes
    jQuery('.wpm_section').each(function() {

        // If the ID is blank, don't do anything.
        if( this.id != '' )
        {
            var divId = this.id;
            var showHideDiv = jQuery('#wpm_o_'+divId+' input:radio:checked').val();

            // If the section has the class manual - means hide it and dont use toggle
            if( jQuery( '#' + divId ).hasClass('manual') )
            {
                // Add expand icon to labels with manual class (videos)
				jQuery("label[for='"+divId+"']").prepend('<div class="icon-container">&nbsp;<img src="<?php echo plugins_url('images/horizontal-8.png', dirname(__FILE__)); ?>" alt="" class="horizontal-icon" /><img src="<?php echo plugins_url('images/vertical-8.png', dirname(__FILE__)); ?>" alt="" class="vertical-icon" /></div>');
                                
                jQuery('#'+divId).hide();

                jQuery('#wpm_o_'+divId+' input:radio').click(function()
                {
					if( jQuery('#wpm_o_'+divId+' input:radio:checked').val()  == '0' )
					{
                        jQuery('#'+divId).slideUp();
						jQuery("#"+divId+" input:radio[value=0]").each(function(){
							jQuery(this).attr('checked', true);
						}); 
                   }else{
						jQuery("#"+divId+" input:radio[value=1]").each(function(){
							jQuery(this).attr('checked', true);
						});
				   }
                });
            }
            else
            {
                // If radio is set to 0, ie no, hide the sub div.
                if(showHideDiv == 0 )
                {
                    jQuery('#'+divId).hide();
                }

                // If the yes/no changes, then toggle the view (slideup or down)
                jQuery('#wpm_o_'+divId+' input:radio').click(function()
                {
                    toggleView( divId );
                });
            }
        }
    });
	
	//selected videos for embed videos tab
    jQuery(".opt_embed_selected_videos").change(function() {
		jQuery("#tabs-3 .embed_selected_videos input:radio[value=0]").attr('checked', true);
		
		if(jQuery(this).is(":checked")){
			jQuery(".short_code_position").addClass("vum_embed_shortcode_wrapper");
			show_vum_shortcodes();
			jQuery(".embed_selected_videos").show();
		}else{
			jQuery(".short_code_position").removeClass("vum_embed_shortcode_wrapper");
			jQuery(".vum_embed_shortcode").html('[vum_embed all]');
			jQuery(".embed_selected_videos").hide();
		}
	});
	
	
    jQuery("#tabs-3 .wpm_input").on("click", "input:radio", function() {
		show_vum_shortcodes();
	});
	
	jQuery(".vum_embed_shortcode").on("click", function() {
		vumselect_all(this);
	});
	
    // Custom Videos Dropdown - submit form on change.
    jQuery("[name=num_local]").change(function() {
        jQuery('#wpm-waiting').show();
        jQuery('#wpm_form').submit();
    });

    // Enable Tabs
    jQuery("#tabs").tabs();
    // Update Hidden field with current tab open
    jQuery('#tabs').bind('tabsselect', function(event, ui) {
        jQuery('#return').val( ui.tab );
        e.preventDefault();
     });
     
	 jQuery("#tabs").on("tabsactivate", function(event, ui){
		 var index = ui.newTab.index();
		 if(index !== 0)
		 	jQuery("#frm_footer_reset").hide();
		 else
		 jQuery("#frm_footer_reset").show();
		 
		 var activeTabId = ui.newPanel.selector;
		 activeTabIdnohash = activeTabId.replace("#", "");
		 
		 //Add hash to the form to active the current tab after submitting
		 jQuery("#wpm_form").attr({"action": wpm_form_action + activeTabId });
		 
		 if(index == 3 && jQuery(".opt_enable_embed").length == 0)
		 	jQuery("#frm_footer").hide();
		 else
			 jQuery("#frm_footer").show();
	 });
	 
	function show_vum_shortcodes(){
		var allvideos = jQuery("#tabs-3 .manual .wpm_input input:radio[value=1]").length;
		var allcheckedvideos = jQuery("#tabs-3 .manual .wpm_input input:radio[value=1]:checked").length;
		var shortcode_video = "";
		
		var scode_video = ' ids="';
		jQuery("#tabs-3 .manual .wpm_input input:radio[value=1]:checked").each(function(){
			var vname = jQuery(this).attr('name');
			var res = vname.replace("show_video_2_", ""); 
			
		 scode_video += res +',';
			//console.log(vname);
		});
		shortcode_video = scode_video.substring(0,scode_video.length - 1);
		shortcode_video += '"';
		if(allcheckedvideos > 0 && jQuery(".opt_embed_selected_videos").is(":checked"))
		jQuery(".vum_embed_shortcode").html('[vum_embed '+shortcode_video + ']');
		else
		jQuery(".vum_embed_shortcode").html('No selected videos');
	}
	
	function check_if_embed_activated(){
		
		if(window.location.hash) {
			var hash = window.location.hash.substring(1);
				jQuery("#wpm_form").attr({"action": wpm_form_action + "#"+ hash });
			if(hash == 'tabs-3' && jQuery(".opt_enable_embed").length == 0 ){
				jQuery("#frm_footer_reset").hide();
				jQuery("#frm_footer").hide();
			}
		}
	}
    
    function toggleView( divId )
    {
        var showHideDiv = jQuery('#wpm_o_'+divId+' input:radio:checked').val();

        // If radio is set to 0, ie no, hide the sub div.
        if(showHideDiv == 0)
            jQuery('#'+divId).slideUp();
        else
            jQuery('#'+divId).slideDown();
    }
    
    // for embeds textboxes (Onload)
    
    jQuery('.embed_selector').each(function() {
        if( jQuery(this).is(':checked')) { 
            check_embed_divs( this.name, this.value );
        }
    });
    
    // For embeds textboxes (Onclick action)
    jQuery(".embed_selector").click(function(){
        var clicked_name = jQuery(this).attr("name");
        var clicked_val = jQuery(this).attr("value");
        check_embed_divs( clicked_name, clicked_val );
    });
    
    function check_embed_divs( clicked_name, clicked_val )
    {
        var split_arr = clicked_name.split('_');
        var field_id = split_arr[1];

        if( clicked_val == "1" )
         {
             jQuery('#localvideos_'+field_id+'_4').hide();
             jQuery('#localvideos_'+field_id+'_3').hide();
             jQuery('#localvideos_'+field_id+'_2').hide();
         }
         else
         {
             jQuery('#localvideos_'+field_id+'_4').show();
             jQuery('#localvideos_'+field_id+'_3').show();
             jQuery('#localvideos_'+field_id+'_2').show();
         }
    }
    
    function vumselect_all(el) {
        if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined") {
            var range = document.createRange();
            range.selectNodeContents(el);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        } else if (typeof document.selection != "undefined" && typeof document.body.createTextRange != "undefined") {
            var textRange = document.body.createTextRange();
            textRange.moveToElementText(el);
            textRange.select();
        }
    }

});
</script>

<script>

/** Video player embed options */
( function( $ ) {

	var embed_option = $( "input[name=form_embed_video]" );
	var height_elem = $( "input[name=form_embed_video_height]" );
	var width_elem = $( "input[name=form_embed_video_width]" );

	// IDs of each of the embed videos secltion area
	var section_ids = [
		'wpm_o_show_2_dashboard',
		'wpm_o_show_2_editor',
		'wpm_o_show_2_images',
		'wpm_o_show_2_pages',
		'wpm_o_show_2_media',
		'wpm_o_show_2_posts',
		'wpm_o_show_2_comments',
		'wpm_o_show_2_links',
		'wpm_o_show_2_profile',
		'wpm_o_show_2_widgets',
		'wpm_o_show_2_menus',
		'wpm_o_show_2_google_analyticator',
		'wpm_o_show_2_seo',
		'wpm_o_show_2_woocommerce',
		'wpm_o_show_2_woocommerce_products',
		'wpm_o_show_2_google_analytics_setup',
		'wpm_o_show_2_google_analytics_reports',
		'wpm_o_show_2_gravity_forms'
	];

	// returns the selected embed option
	function get_embed_option() {
		return $( "input[name=form_embed_video]:checked" );
	}

	// Returns whether page embeds are enabled
	function embed_enabled() {
		return get_embed_option().val() == '1';
	}

	// Updates the view depending on whether the iframe embed option is selected
	function update_view() {

		var embed_settings = $( "#video-embed-settings" );
		var embed_all_section = $( '#embed-all-section' );
		var selected_vids_only = $( 'input.opt_embed_selected_videos' );

		if ( embed_enabled() ) {

			selected_vids_only.hide();
			selected_vids_only.attr( {
				disabled:'disabled',
				checked:'checked'
			} );
			selected_vids_only.trigger( 'change' );

			embed_settings.show();
			embed_all_section.hide();

			// disable each of the bulk select options
			$.each( section_ids, function( i, val ) {
				$( '#'+ val + ' .wpm_form_item' ).hide();
			} )

		} else {

			selected_vids_only.removeAttr( 'disabled' );
			selected_vids_only.show();

			embed_settings.hide();
			embed_all_section.show();

			// disable each of the bulk select options
			$.each( section_ids, function( i, val ) {
				$( '#'+ val + ' .wpm_form_item' ).show();
			} )

		}

	}

	$( document ).ready( function() {

		update_view();

		height_elem.attr( { readonly:"readonly" } );

	} );

	// only allow one user to select one video for shortcode when embed enabled
	$( '.embed_selected_videos input[type=radio]' ).change( function() {
		var changed = $( this );

		// only if embed is enabled
		if ( !embed_enabled() ) {
			return;
		}

		$( '.embed_selected_videos input[type=radio]:checked' ).each( function() {

			if ( $( this ).val() == '1' && $( this ).attr( 'name' ) != changed.attr( 'name' ) ) {

				// uncheck others
				$( this ).removeAttr( 'checked' );

				// re-check the false option
				var name = $( this ).attr( 'name' );
				$( 'input[name='+ name +']' ).each( function() {
					if ( $( this ).val() == '0' ) {
						$( this ).prop( 'checked', true );
					}
				} );

			}

		} );

	} );

	embed_option.change( function( event ) {
		update_view();
	} );

	// auto set the height when changing hte video width
	width_elem.keyup( function( event ) {

		// keep the aspect ratio
		height_elem.val(
			Math.round( width_elem.val() * 10 / 16 ) // assumes a 10:16 ratio
		);

	} );

} )( jQuery );

</script>