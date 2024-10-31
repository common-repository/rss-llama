<?php
/**
 * Rss Llama Import.
 *
 * Description. Import Rss from .csv or .json.
 *
 * @Link        http://wordpress.org/plugins/rss-llama/
 * @package     rss-llama
 * @since       2.0.0
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

namespace Rss_Llama;

if ( check_admin_referer( 'rss_llama_import_nonce', 'rss_llama_import_nonce' ) ) {

	if ( ! class_exists( 'RssLlama_Backup' ) ) {
		require_once RL_PATH . 'includes/classes/class-rssllama-backup.php';
	}

	$import_nonce = isset( $_POST['rss_llama_import_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rss_llama_import_nonce'] ) ) : '';
	$import       = new RssLlama_Backup( $this->other_options['rss_llama_export_delimiter'], $this->other_options['rss_llama_export_categories'] );
	$this->msg    = $this->message( 'Transaction completed: ' . $import->generate_import( $import_nonce ), 'yay' );
} else {
	$this->msg = $this->message( '', 'nonce' );
}
