/**
 * Rss Llama Re-write.
 *
 * Description: Enables and disables buttons while feeds are being fetched etc.
 *
 * @link        http://wordpress.org/plugins/rss-llama/
 * @package     rss-llama
 * @since       1.0
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

// Disable buttons while job finishes.
jQuery.fn.rss_llama_disable = function()
{
	return this.each(
		function()
		{
			if ( typeof this.disabled != 'undefined' ) {
				this.disabled = true;
			}
		}
	);
}

// Enable buttons once job finishes.
jQuery.fn.rss_llama_enable = function()
{
	return this.each(
		function()
		{
			if ( typeof this.disabled != 'undefined' ) {
				this.disabled = false;
			}
		}
	);
}
