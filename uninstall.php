<?php
/**
 * Rss Llama Un-install
 *
 * Description. Delete seettings and options.
 *
 * @Link        http://wordpress.org/plugins/rss-llama
 * @since       1.0.0
 * @package     rss-llama
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

namespace Rss_Llama;

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// If options still exist.
if ( get_option( 'rss-llama-settings-display' ) ) {
	delete_option( 'rss-llama-settings-display' );
	unregister_setting( 'rss-llama-settings-display', 'rss-llama-settings-display' );
}

if ( get_option( 'rss-llama-settings-sidebar' ) ) {
	delete_option( 'rss-llama-settings-sidebar' );
	unregister_setting( 'rss-llama-settings-sidebar', 'rss-llama-settings-sidebar' );
}
