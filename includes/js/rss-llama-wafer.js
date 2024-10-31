/**
 * Rss Llama Cookie.
 *
 * Description: jQuery Cookie Plugin v1.4.1 https://github.com/carhartl/jquery-cookie
 *
 * @link        http://wordpress.org/plugins/rss-llama/
 * @package     rss-llama
 * @since       1.0
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

(function (factory)
	{
	if (typeof define === 'function' && define.amd) {
		define( ['jquery'], factory ); // AMD.
	} else if (typeof exports === 'object') {
		factory( require( 'jquery' ) ); // CommonJS.
	} else {
		factory( jQuery ); // Browser globals.
	}
}(function ($)
		{
		var pluses = /\+/g;
	function encode(s)
		{
		return config.raw ? s : encodeURIComponent( s );
	}

	function decode(s)
		{
		return config.raw ? s : decodeURIComponent( s );
	}

	function stringifyCookieValue(value)
		{
		return encode( config.json ? JSON.stringify( value ) : String( value ) );
	}

	// This is a quoted cookie as according to RFC2068, unescape...
	function parseCookieValue(s)
		{
		if (s.indexOf( '"' ) === 0) {
			s = s.slice( 1, -1 ).replace( /\\"/g, '"' ).replace( /\\\\/g, '\\' );
		}

		// Replace server-side written pluses with spaces. If we can't decode or parse the cookie, ignore it, it's unusable.
		try {
			s = decodeURIComponent( s.replace( pluses, ' ' ) );
			return config.json ? JSON.parse( s ) : s;
		} catch (e) {
		}
	}

	function read(s, converter)
		{
		let value = config.raw ? s : parseCookieValue( s );
		return $.isFunction( converter ) ? converter( value ) : value;
	}

		let config = $.cookie = function (key, value, options)
			{
			if (value !== undefined && ! $.isFunction( value )) {
				options = $.extend( {}, config.defaults, options );

				if (typeof options.expires === 'number') { // Write.
					let days = options.expires, t = options.expires = new Date();
					t.setTime( +t + days * 864e+5 );
				}

				// use expires attribute, max-age is not supported by IE.
				return (document.cookie = [
					encode( key ), '=', stringifyCookieValue( value ),
					options.expires ? '; expires=' + options.expires.toUTCString() : '',
					options.path ? '; path=' + options.path : '',
					options.domain ? '; domain=' + options.domain : '',
					options.secure ? '; secure' : ''
				].join( '' ));
			}

			// Read.
			let result = key ? undefined : {};

				// Prevent for loop assign an empty array if no cookies. Prevent odd result when calling $.cookie().
				let cookies = document.cookie ? document.cookie.split( '; ' ) : [];

			for (let i = 0, l = cookies.length; i < l; i++) {
				let parts  = cookies[i].split( '=' );
				let name   = decode( parts.shift() );
				let cookie = parts.join( '=' );

				if (key && key === name) {

					// If second argument (value) is a function it's a converter...
					result = read( cookie, value );
					break;
				}

				// Prevent storing a cookie that we couldn't decode.
				if ( ! key && (cookie = read( cookie )) !== undefined) {
					result[name] = cookie;
				}
			}

			return result;
		};

		config.defaults = {};

		// Must not alter options, thus extending a fresh object...
		$.removeCookie = function (key, options)
			{
			if ($.cookie( key ) === undefined) {
				return false;
			}

			$.cookie( key, '', $.extend( {}, options, { expires: -1 } ) );
			return ! $.cookie( key );
		};
}
	)
);
