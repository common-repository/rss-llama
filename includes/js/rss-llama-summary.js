/**
 * Rss Llama Summary.
 *
 * Description: Summary of feeds.
 *
 * @link        http://wordpress.org/plugins/rss-llama/
 * @package     rss-llama
 * @since       1.0
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

/*
 * How many feeds.
 */
var rss_llama_feed_count = 0;

/*
 * Summary button click action.
 */
function rss_llama_post( url, rss, name, id, scope, _wpnonce ) {
	var rss_llama_H;
	var rss_llama_Y;

	// Check cookie and return if excluded.
	if ( jQuery.cookie( 'rss_llama_' + id ) == 'exclude' ) {
		return;
	}

	// Increment feed count.
	++rss_llama_feed_count;

	// Filter keywords for _POST, or set to empty.
	if ( jQuery( '.rss-llama-filter' ).length ) {
		var rss_llama_filter = jQuery( '.rss-llama-filter' ).val();
	} else {
		var rss_llama_filter = '';
	}

	jQuery.post(
		// .ajax post summary.
		rss_llama_AJAX.rss_llama_ajaxurl,
		{	action: 'plugin_summary_callback',
			summary: url,
			rss: rss,
			name: name,
			scope: scope,
			filter: rss_llama_filter,
			_wpnonce: _wpnonce
		},
		function( rss_llama_response )
		{
			// Append feed data to display.
			jQuery( '.rss-llama-output' ).append( rss_llama_response );

			// If excerpt enabled, when hover summary link position excerpt to top/bottom away from mouse.
			if ( rss_llama_summary.rss_llama_excerpt ) {
				jQuery( 'a.rss-llama-excerpt' ).on(
					{
						mouseenter: function( e ) {
							rss_llama_H = window.innerHeight * .5;
							rss_llama_Y = e.pageY - jQuery( window ).scrollTop();

							if ( rss_llama_Y > rss_llama_H ) {
								jQuery( 'a.rss-llama-excerpt:hover span' ).css( 'bottom', '' );
								jQuery( 'a.rss-llama-excerpt:hover span' ).css( 'top', '0px' );
								jQuery( 'a.rss-llama-excerpt:hover span' ).css( 'color', rss_llama_summary.rss_llama_summary_colorlight );
								jQuery( 'a.rss-llama-excerpt:hover span' ).css( 'background', rss_llama_summary.rss_llama_summary_colordark );
							} else {
								jQuery( 'a.rss-llama-excerpt:hover span' ).css( 'top', '' );
								jQuery( 'a.rss-llama-excerpt:hover span' ).css( 'bottom', '3%' );
								jQuery( 'a.rss-llama-excerpt:hover span' ).css( 'color', rss_llama_summary.rss_llama_summary_colorlight );
								jQuery( 'a.rss-llama-excerpt:hover span' ).css( 'background', rss_llama_summary.rss_llama_summary_colordark );
							}
						}, mouseleave: function () {
							// Do nothing.
						}
					}
				);
			} else {

				// Excerpt disabled, hide.
				jQuery( 'a.rss-llama-excerpt span' ).css( 'display', 'none' );
			}
		}
	).done(
		function()
			{
				// Apply css to summary title.
				jQuery( '.rss-llama-summary-title' ).css( 'color', rss_llama_summary.rss_llama_summary_colorlight );
				jQuery( '.rss-llama-summary-title' ).css( 'background', rss_llama_summary.rss_llama_summary_colordark );

				// Apply css to excerpt hover.
				jQuery( 'a.rss-llama-excerpt' ).hover(
					function( e )
					{
						jQuery( this ).css( 'color', rss_llama_summary.rss_llama_summary_colorlight );
						jQuery( this ).css( 'background', rss_llama_summary.rss_llama_summary_colordark );
					},
					function( e )
					{
						jQuery( this ).css( 'color', '' );
						jQuery( this ).css( 'background', '' );
					}
				);

				// Decrement feed count.
				--rss_llama_feed_count;

				// Update loading status with # of feeds left.
				jQuery( '.rss-llama-loading' ).html( '<p>Loading Summary... ' + rss_llama_feed_count + ' remaining.</p>' );

			// If last feed then fade out loading status.
			if ( ! rss_llama_feed_count ) {
				jQuery( '.rss-llama-loading' ).fadeOut( 'slow' );

				// Finished so enable buttons.
				jQuery( '.rss-llama-button' ).rss_llama_enable();
			}
		}
	);
}

// Fetch summary of all sites... click event.
jQuery(
	document
).on(
	'click',
	'.rss-llama-button',
	function()
	{
		var rss_llama_url;
		var rss_llama_rss;
		var rss_llama_name;
		var rss_llama_id;

		if ( jQuery( '.rss-llama-option' ).filter( ':checked' ).length ) {

			// Disable buttons until job finishes.
			jQuery( '.rss-llama-button' ).rss_llama_disable();

			// Display.
			jQuery( '.rss-llama-output' ).html( '' );
			jQuery( '.rss-llama-loading' ).html( '<p>Loading Summary...</p>' );
			jQuery( '.rss-llama-loading' ).fadeIn( 'slow' );

			// Scope of summary, weekly, monthly, etc.
			let rss_llama_scope = jQuery( this ).attr( 'id' );

			// NONCE.
			let rss_llama_security = jQuery( this ).attr( '_wpnonce' );

			// Send each to rss_llama_post().
			jQuery.each(
				rss_llama_feeds,
				function( key, link_id, link_name, link_description, link_url, link_rss, link_image ) {
					rss_llama_url  = encodeURI( this.link_url );
					rss_llama_rss  = encodeURI( this.link_rss );
					rss_llama_name = this.link_name;
					rss_llama_id   = this.link_id;
					rss_llama_post( rss_llama_url, rss_llama_rss, rss_llama_name, rss_llama_id, rss_llama_scope, rss_llama_security );
				}
			);

			// Apply css to summary title.
			jQuery( '.rss-llama-summary-title' ).css( 'color', rss_llama_summary.rss_llama_summary_colorlight );
			jQuery( '.rss-llama-summary-title' ).css( 'background', rss_llama_summary.rss_llama_summary_colordark );
			jQuery( 'a.rss-llama-excerpt' ).hover(
				function( e )
				{
					jQuery( this ).css( 'color', rss_llama_summary.rss_llama_summary_colorlight );
					jQuery( this ).css( 'background', rss_llama_summary.rss_llama_summary_colordark );
				},
				function( e )
				{
					jQuery( this ).css( 'color', '' );
					jQuery( this ).css( 'background', '' );
				}
			);
		} else {

			// No feeds, all excluded.
			jQuery( '.rss-llama-loading' ).html( '<p>&#10004; Tik a checkbox for at one or more of your sites!</p>' );
			jQuery( '.rss-llama-loading' ).fadeOut( 5000 );
		}
	}
);

// Hide excerpt if scrolling... for touchscreens.
jQuery(
	window
).on(
	'scroll',
	function()
	{
		if ( jQuery( '.rss-llama-excerpt span' ).is( ':visible' ) ) {
			jQuery( '.rss-llama-excerpt span' ).hide( 500 );
		}
	}
);
