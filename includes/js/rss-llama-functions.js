/**
 * Rss Llama Functions
 *
 * Description: Main Javascript Functions
 *
 * @link        http://wordpress.org/plugins/rss-llama/
 * @package     rss-llama
 * @since       1.0
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

/*
 * Current localid.
 */
var rss_llama_cid = 0;

/*
 * Last localid.
 */
var rss_llama_lastid = jQuery( '.rss-llama-link' ).last().attr( 'localid' );

// Sidebar position left or right.
jQuery( '.rss-llama-sidebar' ).css( 'float', rss_llama_body.rss_llama_sidebarpos );

// Get random between two int.
function rss_llama_randomInt( min, max ) {
	return Math.floor( Math.random() * ( max - min + 1 ) + min );
}

/*
 * Apply styles and enable buttons.
 */
function rss_llama_document_loaded() {

	// Apply color to containers css.
	jQuery( '.rss-llama-loading, .rss-llama-heading, .rss-llama-heading a, .rss-llama-previous, .rss-llama-next, .rss-llama-top, .rss-llama-collapse' ).css( 'color', rss_llama_body.rss_llama_body_colorlight );

	// Apply background to containers css.
	jQuery( '.rss-llama-loading, .rss-llama-heading, .rss-llama-previous, .rss-llama-next, .rss-llama-top, .rss-llama-collapse' ).css( 'background', rss_llama_body.rss_llama_body_colordark );

	// Finished so enable buttons if they happen to be disabled.
	jQuery( '.rss-llama-button' ).rss_llama_enable();
}

/*
 * Populate first quote on page load.
 */
function rss_llama_populate_first() {

	// Reset current id to 0.
	rss_llama_cid = 0;

	// Last localid of new feeds array.
	rss_llama_lastid = jQuery( '.rss-llama-link' ).last().attr( 'localid' );

	// Populate the first initial random site, if enabled.
	if ( rss_llama_body.rss_llama_random_enabled ) {
		rss_llama_random_site = rss_llama_randomInt( rss_llama_cid, rss_llama_lastid );
		jQuery( '.rss-llama-link' ).eq( rss_llama_random_site ).click();
	}
}

// Load category page from button.
jQuery(
	document
).on(
	'click',
	'.rss-llama-button-categories button',
	function()
	{
		let id        = jQuery( this ).attr( 'id' );
		let catsecure = jQuery( this ).attr( '_wpnonce' );
		let data      = {
			_wpnonce: catsecure,
			action: 'plugin_category_callback',
			catbutton: id
		}

		jQuery.ajax(
			{
				type:'POST',
				data: data,
				success: function( html ) {
					jQuery( '.rss-llama-container' ).html( html );

					// Style page on load.
					rss_llama_document_loaded();

					// Load feed on page load.
					rss_llama_populate_first();
				}
			}
		);
	}
);

// Collapse or expand the sidebar click.
jQuery(
	document
).on(
	'click',
	'.rss-llama-collapse',
	function()
	{
		jQuery( '.rss-llama-sidebar' ).is( ':visible' ) ? jQuery( '.rss-llama-output' ).animate({ width: '95%' }, 'slow') : jQuery( '.rss-llama-output' ).animate({ width: '68%' }, 'fast');
		jQuery( '.rss-llama-sidebar' ).toggle( 'slide' );
	}
);

// Scroll to top on page load if not at top of page.
jQuery(
	window
).scroll(
	function ()
	{
		if ( jQuery( this ).scrollTop() > 200 ) {
			jQuery( '.rss-llama-top' ).fadeIn();
		} else {
			jQuery( '.rss-llama-top' ).fadeOut();
		}
	}
);

// Top of page click.
jQuery(
	document
).on(
	'click',
	'.rss-llama-top',
	function()
	{
		jQuery( 'html, body' ).animate( { scrollTop: 0 }, 1000 );
	}
);

// Next feed previous feed top of sidebar if enabled.
jQuery(
	document
).on(
	'click',
	'.rss-llama-button, .rss-llama-next, .rss-llama-previous, .rss-llama-link',
	function()
	{
		if ( rss_llama_body.rss_llama_scrolltop ) {
			jQuery( 'html, body' ).animate( { scrollTop: jQuery( '.rss-llama-sidebar' ).offset().top - 120 }, 1000 );
		}
	}
);

// Click RSS next site button.
jQuery(
	document
).on(
	'click',
	'.rss-llama-next',
	function()
	{
		// Increment local id for next element.
		++rss_llama_cid;

		// If at last in list, load last.
		if ( rss_llama_cid >= rss_llama_lastid ) {
			rss_llama_cid = rss_llama_lastid
		};

		// Click element.
		jQuery( '.rss-llama-link' ).eq( rss_llama_cid ).click();
	}
);

// Click RSS previous site button.
jQuery(
	document
).on(
	'click',
	'.rss-llama-previous',
	function()
	{
		// Decrement local id for previous element.
		--rss_llama_cid;

		if ( rss_llama_cid < 0 ) {

			// If at start of list load first.
			rss_llama_cid = 0
		};

		jQuery( '.rss-llama-link' ).eq( rss_llama_cid ).click();
	}
);

// Sidebar checkbox click.
jQuery(
	document
).on(
	'change',
	'.rss-llama-option',
	function()
	{
		// For checkbox id.
		let rss_llama_cookieid;

		// Checkbox option.
		$rss_llama_option = this.checked;

		// If checkbox checked, write cookie and uncheck.
		if ( ! $rss_llama_option ) {
			rss_llama_cookieid = jQuery( this ).attr( 'id' );

			jQuery.cookie(
				'rss_llama_' + rss_llama_cookieid,
				'exclude',
				{
					path: '/',
					expires: 180
				}
			);
		} else {

			// If not checked, remove cookie and check.
			rss_llama_cookieid = jQuery(
				this
			).attr(
				'id'
			);

			jQuery.removeCookie(
				'rss_llama_' + rss_llama_cookieid,
				{
					path: '/'
				}
			);
		}
	}
);

// Filter text change.
jQuery(
	'.rss-llama-filter'
).on(
	'input',
	function()
	{
		rss_llama_cookievalue = jQuery( '.rss-llama-filter' ).val();
		jQuery.cookie(
			'rss_llama_filter',
			rss_llama_cookievalue,
			{
				path: '/',
				expires: 180
			}
		);
	}
);

// Submit link click.
jQuery(
	document
).on(
	'click',
	'#rss_llama_refer_submit',
	function()
	{
		let http                     = jQuery( '#rss_llama_referral' ).val();
		let dot                      = http.indexOf( '.' );
		let rss_llama_refer_response = jQuery( '#rss_llama_refer_response' ).val();
		let rss_llama_refer_request  = jQuery( '#rss_llama_refer_request' ).val();

		if ( dot > 0 && ( http.substr( 0, 7 ) == 'http://' || http.substr( 0, 8 ) == 'https://' ) ) {
			let _wpnonce             = jQuery( '#_wpnonce' ).val();
			let rss_llama_refer_blog = jQuery( '#rss_llama_refer_blog' ).val();
			let rss_llama_referral   = jQuery( '#rss_llama_referral' ).val();
			let rss_llama_refer_text = jQuery( '#rss_llama_refer_text' ).val();
			let refer_data           = {
				rss_llama_referral: rss_llama_referral,
				_wpnonce: _wpnonce,
				rss_llama_refer_blog: rss_llama_refer_blog,
				rss_llama_refer_text: rss_llama_refer_text
			};

			jQuery.ajax(
				{
					type: 'POST',
					data: refer_data,
					success: function( response )
					{
						jQuery(
							'.rss-llama-response'
						).html(
							rss_llama_refer_response
						).fadeOut(
							3000,
							function()
							{
								jQuery( '#rss_llama_referral' ).val( '' );
								jQuery( '#rss_llama_refer_text' ).val( '' );
								jQuery(
									'.rss-llama-response'
								).html(
									rss_llama_refer_request
								).fadeIn(
									1000
								);
							}
						);
					}
				}
			);

			return false;
		}

		jQuery(
			'.rss-llama-response'
		).html(
			'Invalid URL'
		).fadeOut(
			3000,
			function()
			{
				jQuery( '#rss_llama_referral' ).val( '' );
				jQuery( '#rss_llama_refer_text' ).val( '' );
				jQuery(
					'.rss-llama-response'
				).html(
					rss_llama_refer_request
				).fadeIn(
					1000
				);
			}
		);

		return false;
	}
);

// Populate any filter terms.
if ( jQuery.cookie( 'rss_llama_filter' ) ) {
	jQuery( '.rss-llama-filter' ).val( jQuery.cookie( 'rss_llama_filter' ) );
} else {
	jQuery( '.rss-llama-filter' ).val( '' );
}

// Check exclude cookie for each site displayed.
jQuery(
	'.rss-llama-option:checked'
).each(
	function ()
	{
		let currentId = jQuery( this ).attr( 'id' );

		if ( jQuery.cookie( 'rss_llama_' + currentId ) == 'exclude' ) {
			jQuery( '#' + currentId ).attr( 'checked', false );
		}
	}
);

rss_llama_document_loaded();
rss_llama_populate_first();
