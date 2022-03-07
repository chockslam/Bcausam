function branda_images_add_already_used_sites() {
	var sites = [];
	jQuery( '.branda-images-subsites .simple-option-media').each( function() {
		var id = jQuery( this ).data('blog-id');
		if ( id ) {
			sites.push( id );
		}
	});
	return sites;
}

jQuery( document ).ready( function( $ ) {
	/**
	 * init set
	 */
	$('select.branda-images-quantity').each( function() {
		var parent = $(this).closest( '.sui-row');
		var quant = $('option:selected', $('select.branda-images-quantity', parent ) );
		if ( 0 < quant.length ) {
			quant = quant.val();
			parent.data( 'previous', quant );
			$( 'input[type=number]', parent ).prop( 'max', branda_images.quants[ quant ].max );
		}
	});
	/**
	 * handle site add
	 */
	$( '#branda-images-search' ).on( 'select2:select', function (e) {
		var data = e.params.data;
	})
	$( '.branda-images-subsite-add' ).on( 'click', function() {
		var target = $('.branda-images-subsites' );
		var subsite = $( '#branda-images-search' );
		var data = subsite.SUIselect2( 'data' );
		if ( 0 === data.length ) {
			return;
		}
		/**
		 * Add row
		 */
		var template = wp.template( 'branda-images-subsite' );
		data = {
			id: data[0].id,
			subtitle: data[0].subtitle,
			title: data[0].title,
		}
		$('>.sui-notice', target )
			.hide()
			.after( template( data ) )
		;
		/**
		 * Reset SUIselect2
		 */
		subsite.val( null ).trigger( 'change' );
		/**
		 * Handle images
		 */
		var container_id = '#branda-images-subsite-container-' + data.id;
		target = $( container_id + ' .images' );
		template = wp.template( 'simple-options-media' );
		data = {
			id: 'favicon',
			image_id: 'time-'+Math.random().toString(36).substring(7),
			section_key: 'subsites',
			value: '',
			image_src: '',
			file_name: '',
			disabled: '',
			container_class: ''
		};
		target.append( template( data ) );
		$( '.button-select-image', target ).on( 'click', function( event ) {
			ub_media_bind( this, event );
		});
		$( '.image-reset', target ).on( 'click', function() {
			ub_bind_reset_image( this );
			return false;
		});
		$( '.branda-images-delete', container_id ).on( 'click', function() {
			$(container_id).remove();
		});
	});

	/**
	 * Delete
	 */
	$( '.branda-images-delete' ).on( 'click', function () {
		var data = {
			action: 'branda_images_delete_subsite',
			_wpnonce: $(this).data('nonce'),
			id: $(this).data('id')
		};
		$.post( ajaxurl, data, function( response ) {
			if ( response.success ) {
				window.location.reload();
			} else {
				SUI.openFloatNotice( response.data.message );
			}
		});
	});

	$('.branda-images-quantity').on( 'change', function() {
		var parent = $(this).closest( '.sui-row');
		var select = $('select.branda-images-quantity', parent );
		var quant = $('option:selected', select ).val()
		var prev = parent.data( 'previous' );
		var amount = $('.branda-images-amount', parent );
		var value = parseInt( amount.val() );
		value *= branda_images.quants[ prev ].quant;
		value /= branda_images.quants[ quant ].quant;
		amount.val( Math.floor( value ) ).prop( 'max', branda_images.quants[ quant ].max );
		parent.data( 'previous', quant );
	});

});
