var jq = jQuery.noConflict();

( function( $ ) {

	// Card animation on loading
	var card = $( '.assistant-card' );

	card.css( {
		transform: 'rotateX(5deg) rotateY(5deg) rotateZ(0deg) scale(.91)'
	} ).addClass( 'morphing-first' );

	setTimeout( function() {
		card.removeClass( 'morphing-first' ).css( {
			transform: 'rotateX(0deg) rotateY(0deg) rotateZ(0deg) scale(1)'
		} );
	}, 400 );

} )( jq );