var tb_position;
var cardClasses = 'assistant-card animate';
var cardSelector = '.assistant-card.animate';

jQuery( document ).ready( function( $ ) {

	/**
	 * WP Thickbox tb_position() is being overriden by media-upload.js (known bug: https://core.trac.wordpress.org/ticket/39267)
	 * we fix this by writing our own tb_position() and take the occasion to customize some stuff
	 */
	tb_position = function() {
		var tb_window = $( '#TB_window' );
		var tb_inner = $( '#TB_ajaxContent' );
		var custom_tb_width = 700;
		var custom_tb_height = tb_inner.children( ':first' ).outerHeight( true );

		tb_window
			.addClass(
				'card-lightbox'
			).css( {
			marginLeft: '-' + parseInt( ( custom_tb_width / 2 ), 10 ) + 'px',
			marginTop: '-' + parseInt( ( custom_tb_height / 2 ), 10 ) + 'px',
			width: custom_tb_width + 'px'
		} );

		tb_inner
			.css( {
				width: custom_tb_width + 'px',
				height: 'auto'
			} );
	};

	/**
	 * Show first card with opening animation
	 *
	 * @param firstStep
	 */
	function cardFadeIn( firstStep ) {
		var card = $( cardSelector );
		var firstStepId = firstStep.attr( 'id' ).replace( 'card-', '' );

		card.attr( 'class', cardClasses + ' card-' + firstStepId )
			.css( { transform: 'rotateX(5deg) rotateY(5deg) rotateZ(0deg) scale(.91)' } )
			.addClass( 'morphing-first' );

		setTimeout( function() {
			$( cardSelector )
				.removeClass( 'morphing-first' )
				.css( { transform: 'rotateX(0deg) rotateY(0deg) rotateZ(0deg) scale(1)' } );
		}, 400 );

		firstStep.show();
	}

	/**
	 * Show a card with transition animation
	 *
	 * @param stepId
	 */
	function cardSwitch( stepId ) {
		var card = $( cardSelector );
		var nextStep = $( '#card-' + stepId );

		card.find( '.active' ).removeClass( 'active' );

		card.attr( 'class', cardClasses + ' card-' + stepId )
			.css( { transform: 'rotateX(-5deg) rotateY(5deg) rotateZ(0deg) scale(.91)' } )
			.addClass( 'morphing' );

		setTimeout( function() {
			card.removeClass( 'morphing' )
				.css( { transform: 'rotateX(0deg) rotateY(0deg) rotateZ(0deg) scale(1)' } );
		}, 200 );

		nextStep.addClass( 'active' );
	}

	/**
	 * Load the preview of a given theme
	 *
	 * @param type
	 * @param theme
	 */
	function loadPreview(type, theme) {
		var loadedClass = type + '-' + theme + '-loaded';

		if ( ! $( '#theme-preview-loader' ).hasClass( loadedClass ) ) {
			var url = ajax_assistant_object.ajaxurl;

			$.ajax( {
				type: 'POST',
				dataType: 'html',
				url: url,
				data: 'site_type=' + type + '&theme=' + theme + '&action=ajaxpreview',

				success: function( response ) {
					$( '#theme-preview-loader' )
						.removeClass()
						.addClass( loadedClass )
						.html( response );
				}
			} );
		}
	}

	/**
	 * Installation of the site type
	 * (selected theme + recommended plugins)
	 *
	 * @param action
	 */
	function startInstall( action ) {
		var form, data;
		var url = ajax_assistant_object.ajaxurl;

		// Display progress screen
		cardSwitch( 'install' );

		// Install from preview or from current thumbnail
		form = $( 'form.assistant-install-form-preview' );
		if ( ! form.length > 0) {
			form = $( action ).closest( 'form.assistant-install-form' );
		}
		if ( form.length > 0 ) {
			data = form.serialize() + '&action=ajaxinstall';

			$.ajax( {
				type: 'POST',
				dataType: 'json',
				url: url,
				data: data,

				success: function( response ) {
					window.location = response.data.referer;
				}
			} );
		}
	}

	// Open the site type menu (mobile)
	$( '.diys-sidebar-menu-btn' ).on( 'click', function( event ) {
		event.preventDefault();

		$( '.diys-sidebar-wrapper' ).toggleClass( 'open' );
	} );

	// Load the list of themes for each site type
	$( '.diys-sidebar-tabs a' ).on( 'click', function( event ) {
		event.preventDefault();

		$( '.diys-sidebar-wrapper' ).removeClass( 'open' );
		$( '.current-site-type' ).text( $( this ).text() );

		var type = $( this ).attr( 'id' ).replace( 'site-type-', '' );
		var url = ajax_assistant_object.ajaxurl;

		$( '.diys-sidebar-tabs li' ).removeClass( 'active' );
		$( this ).parent( 'li' ).addClass( 'active' );

		$( '.theme-list' ).removeClass( 'active' );
		$( '#themes-' + type ).addClass( 'active' );

		if ( ! $( '#themes-' + type + ' .theme-list-inner' ).hasClass( 'loaded' ) ) {
			$.ajax( {
				type: 'POST',
				dataType: 'html',
				url: url,
				data: 'site_type=' + type + '&action=ajaxload',

				success: function( response ) {
					$( '#themes-' + type + ' .theme-list-inner' )
						.addClass( 'loaded' )
						.html( response );
				}
			} );
		}
	} );

	// Open the first card (with the "active" class)
	var firstStep = $( cardSelector + ' .card-step.active' );
	if ( firstStep.length > 0 ) {
		cardFadeIn( firstStep );
	}

	// Pop open the card (using WP thickbox) in the Customizer
	$( window ).on( 'load', function() {
		var customizerCard = $( '#card-congrats-lightbox' );

		if ( customizerCard.length > 0 && typeof tb_show === 'function' ) {
			$( '#TB_window' ).remove();
			$( '#TB_overlay' ).remove();

			tb_show( '', '#TB_inline?inlineId=card-congrats-lightbox&modal=true', null );
		}
	} );

	// Trigger the card next action(s)
	var step = $( cardSelector + ' .card-step' );

	step.on( 'click', '[id^=goto-]', function( event ) {
		event.preventDefault();

		var nextStepId = $( this ).attr( 'id' ).replace( 'goto-', '' );
		cardSwitch( nextStepId );

		// Show the list of themes of the first site type
		if ( nextStepId === 'design' ) {
			if ( $( this ).data( 'site-type' ) ) {
				$( '.diys-sidebar-tabs a#site-type-' + $( this ).data( 'site-type' ) ).trigger( 'click' );
			} else {
				$( '.diys-sidebar-tabs a:first' ).trigger( 'click' );
			}
		}
		if ( nextStepId === 'preview' ) {
			loadPreview( $( this ).data( 'site-type' ), $( this ).data( 'theme' ) );
		}
	} );

	step.on( 'click', '.theme-btn', function( event ) {
		event.preventDefault();
		event.stopPropagation();

		startInstall( event.target );
	} );

	// Show the list of themes of the first site type if we got to the "design" step directly
	var currentUseCase = $( '.diys-sidebar-tabs .current a' );

	if ( ! currentUseCase.length ) {
		currentUseCase = $( '.diys-sidebar-tabs a:first' );
	}
	if ( currentUseCase.is( ':visible' ) ) {
		currentUseCase.trigger( 'click' );
	}

	// Show the preview of the theme if we got to the "preview" step directly
	const urlParams = new URLSearchParams(window.location.search);

	if ( urlParams.has('setup_type' ) && urlParams.has('setup_theme' ) ) {
		loadPreview( urlParams.get('setup_type' ), urlParams.get('setup_theme' ) );
	}

} );