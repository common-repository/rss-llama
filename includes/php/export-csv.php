<?php
/**
 * Rss Llama Export CSV.
 *
 * Description. Export rss to a .csv file.
 *
 * @Link        http://wordpress.org/plugins/rss-llama/
 * @package     rss-llama
 * @since       2.0.0
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

namespace Rss_Llama;

if ( check_admin_referer( 'rss_llama_export_nonce', 'rss_llama_export_nonce' ) ) {

	if ( ! class_exists( 'RssLlama_Backup' ) ) {
		require_once RL_PATH . 'includes/classes/class-rssllama-backup.php';
	}

	$export_csv = new RssLlama_Backup( $this->other_options['rss_llama_export_delimiter'], $this->other_options['rss_llama_export_categories'] );
	$export_csv->download_csv();

} else {
	$this->msg = $this->message( '', 'nonce' );
}
