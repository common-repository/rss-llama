/**
 * Rss Llama Single
 *
 * Description: Click event to get a single feed.
 *
 * @link        http://wordpress.org/plugins/rss-llama/
 * @package     rss-llama
 * @since       1.0
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

jQuery(
	document
).on(
	'click',
	'.rss-llama-link',
	function()
	{

		// Current single feed selection localid.
		rss_llama_cid = jQuery( this ).attr( 'localid' );

		// Display loading message.
		jQuery( '.rss-llama-output' ).html( '' );
		jQuery( '.rss-llama-loading' ).html( '<p>Loading: ' + jQuery( this ).attr( 'title' ) + '</p>' );
		jQuery( '.rss-llama-loading' ).fadeIn( 'slow' );

		// .ajax single feed variables.
		let rss_llama_data = {
			single: jQuery( this ).attr( 'title' ),
			description: jQuery( this ).attr( 'alt' ),
			site: jQuery( this ).attr( 'site' ),
			feed: jQuery( this ).attr( 'feed' ),
			image: jQuery( this ).attr( 'image' ),
			id: rss_llama_cid,
			_wpnonce: jQuery( this ).attr( '_wpnonce' )
		};

		jQuery.ajax(
			{
				type: 'POST',
				data: rss_llama_data,
				success: function( response ) {

					// Disable buttons until job finishes.
					jQuery( '.rss-llama-output' ).fadeOut(
						700,
						function() {

							// Disable buttons.
							jQuery( '.rss-llama-button' ).rss_llama_disable();

							// Html response and fade in.
							jQuery( '.rss-llama-output' ).html( response );
							jQuery( '.rss-llama-output' ).fadeIn( 700 );

							// Limit image size.
							jQuery( '.rss-llama-single-content img' ).css( 'height', 'auto' );
							jQuery( '.rss-llama-single-content img' ).css( 'width', 'auto' );
							jQuery( '.rss-llama-single-content img' ).css( 'max-width', '300px' );
							jQuery( '.rss-llama-single-content img' ).css( ' max-height', '300px' );

							// Apply color and background.
							jQuery( '.rss-llama-single-title a, .rss-llama-single-date' ).css( 'color', rss_llama_single.rss_llama_single_colorlight );
							jQuery( '.rss-llama-single-title' ).css( 'background', rss_llama_single.rss_llama_single_colordark );

							// Fadeout loading message.
							jQuery( '.rss-llama-loading' ).fadeOut( 'slow' );

							// Finished so enable buttons.
							jQuery( '.rss-llama-button' ).rss_llama_enable();
						}
					);

				}
			}
		)
	}
);
