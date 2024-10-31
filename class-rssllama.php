<?php
/**
 * Plugin Name: Rss llama
 * Plugin URI:  http://wordpress.org/plugins/rss-llama/
 * Version:     2.0.0
 * Description: Categorize your rss feeds into a useful page.
 * Author:      oooorgle
 * Author URI:  https://oooorgle.com/plugins/wp/rss-llama/
 * Text Domain: rss-llama
 * Domain Path: /lang
 *
 * @package     rss-llama
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

namespace Rss_Llama;

// Deny access except through WordPress.
defined( 'ABSPATH' ) || die( 'Cannot access pages directly.' );

// Plugin name.
defined( 'RL_NAME' ) || define( 'RL_NAME', plugin_basename( __FILE__ ) );

// Plugin paths.
defined( 'RL_URL' ) || define( 'RL_URL', plugin_dir_url( __FILE__ ) );
defined( 'RL_PATH' ) || define( 'RL_PATH', plugin_dir_path( __FILE__ ) );

// Plugin version.
defined( 'RL_PLUGIN_VERSION' ) || define( 'RL_PLUGIN_VERSION', '2.0.0' );

/**
 * Begin RssLlama class.
 */
class RssLlama {

	/**
	 * Array of options for the Display tab.
	 *
	 * @since 1.0.0
	 * @var array
	 * @access public
	 */
	public $display_options;

	/**
	 * Array of options for the Sidebar tab.
	 *
	 * @since 1.0.0
	 * @var array
	 * @access public
	 */
	public $sidebar_options;

	/**
	 * Array of options for the Other tab.
	 *
	 * @since 2.0.0
	 * @var array
	 * @access public
	 */
	public $other_options;

	/**
	 * The message for success or failure of actions.
	 *
	 * @since 2.0.0
	 * @var string
	 * @access public
	 */
	public $msg;

	/**
	 * Rss_Llama class construct.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		// Options arrays.
		$this->display_options = get_option( 'rss-llama-settings-display' );
		$this->sidebar_options = get_option( 'rss-llama-settings-sidebar' );
		$this->other_options   = get_option( 'rss-llama-settings-other' );
		$this->msg             = '';

		// Deactivation hook.
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		// Activation hook.
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
	}

	/**
	 * Register options arrays when activating plugin.
	 * one for each tab that has editable options, in the admin screen.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function activation() {
		$add_display_settings = array(
			'rss_llama_top'         => true,
			'rss_llama_nextprev'    => true,
			'rss_llama_collapse'    => true,
			'rss_llama_buttoncat'   => true,
			'rss_llama_buttoncount' => true,
			'rss_llama_refer'       => false,
			'rss_llama_excerpt'     => true,
			'rss_llama_scrolltop'   => true,
			'rss_llama_random'      => true,
			'rss_llama_filterwords' => false,
		);
		add_option( 'rss-llama-settings-display', $add_display_settings );

		$add_sidebar_settings = array(
			'rss_llama_sidebarpos'         => 'Left',
			'rss_llama_colordark'          => '#444',
			'rss_llama_colorlight'         => '#FFF',
			'rss_llama_refer_email'        => get_option( 'admin_email' ),
			'rss_llama_refer_requesttext'  => 'Submit Link',
			'rss_llama_refer_responsetext' => 'Link Sent!',
		);
		add_option( 'rss-llama-settings-sidebar', $add_sidebar_settings );

		$add_other_settings = array(
			'rss_llama_export_delimiter'  => '|',
			'admin_reset'                 => true,
			'display_errors'              => false,
			'rss_llama_export_categories' => true,
		);
		add_option( 'rss-llama-settings-other', $add_other_settings );
	}

	/**
	 * Load admin class.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function admin_load() {
		$export_nonce = isset( $_POST['rss_llama_export_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rss_llama_export_nonce'] ) ) : '';

		if ( wp_verify_nonce( $export_nonce, 'rss_llama_export_nonce' ) ) {

			// $_POST to Export rss to csv.
			if ( isset( $_POST['rss_llama_export_csv'] ) ) {
				include RL_PATH . 'includes/php/export-csv.php';
			}

			// $_POST to Export rss to json.
			if ( isset( $_POST['rss_llama_export_json'] ) ) {
				include RL_PATH . 'includes/php/export-json.php';
			}
		}

		// Admin page fields and tabs.
		if ( ! class_exists( 'RssLlama_Admin' ) ) {
			require_once 'includes/classes/class-rssllama-admin.php';
		}

		$rss_llama_nonce = wp_create_nonce( 'rss_llama_nonce' );
		$rl_admin        = new RssLlama_Admin( $rss_llama_nonce );
		$rl_admin->page_fields();
	}

	/**
	 * Apply admin page css.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function admin_scripts() {

		// Back-end CSS.
		wp_enqueue_style( 'rss_llama_admin_css', RL_URL . 'includes/css/rss-llama-admin.css', RL_PLUGIN_VERSION, 'all' );

		// rss-llama-wafer.js is a rename of jquery.cookie.js. It causes a conflict with Installs of Mod_Security to have cookie in a filename.
		wp_enqueue_script( 'rss_llama_cookie', RL_URL . 'includes/js/rss-llama-wafer.js', array( 'jquery' ), RL_PLUGIN_VERSION, false );

		// Rewrite rules.
		wp_enqueue_script( 'rss_llama_rewrite', RL_URL . 'includes/js/rss-llama-rewrite-rules.js', array( 'jquery' ), RL_PLUGIN_VERSION, false );

		// Plugin functions.
		wp_enqueue_script( 'rss_llama_body', RL_URL . 'includes/js/rss-llama-functions.js', array( 'jquery' ), RL_PLUGIN_VERSION, true );

		// var path to admin-ajax.php.
		wp_localize_script( 'rss_llama_summary', 'rss_llama_AJAX', array( 'rss_llama_ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		// if our javascript variables are empty make them 0.
		if ( isset( $this->display_options['rss_llama_random'] ) ) {
			$rss_llama_random = $this->display_options['rss_llama_random'];
		} else {
			$rss_llama_random = 0;
		}

		if ( isset( $this->display_options['rss_llama_scrolltop'] ) ) {
			$rss_llama_scrolltop = $this->display_options['rss_llama_scrolltop'];
		} else {
			$rss_llama_scrolltop = 0;
		}

		if ( isset( $this->display_options['rss_llama_excerpt'] ) ) {
			$rss_llama_excerpt = $this->display_options['rss_llama_excerpt'];
		} else {
			$rss_llama_excerpt = 0;
		}

		$rss_llama_body = array(
			'rss_llama_sidebarpos'      => $this->sidebar_options['rss_llama_sidebarpos'], // var for sidebar position.
			'rss_llama_random_enabled'  => $rss_llama_random, // var for random site enabled.
			'rss_llama_scrolltop'       => $rss_llama_scrolltop, // var for scroll to top icon.
			'rss_llama_body_colorlight' => $this->sidebar_options['rss_llama_colorlight'], // var for page light color.
			'rss_llama_body_colordark'  => $this->sidebar_options['rss_llama_colordark'], // var for page dark color.
		);

		// Plugin functions.
		wp_localize_script( 'rss_llama_body', 'rss_llama_body', $rss_llama_body );
	}

	/**
	 * Allowed html lists.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $type - Which set of allowed tags.
	 *
	 * @return array - Allowed html entities.
	 */
	public function allowed_html( $type ) {

		if ( 'style' === $type ) {
			$allowed_html = array(
				'a'      => array(
					'href'   => true,
					'title'  => true,
					'target' => true,
					'class'  => true,
					'rel'    => true,
				),
				'img'    => array(
					'alt' => true,
					'src' => true,
				),
				'br'     => array(
					'clear' => true,
				),
				'b'      => array(),
				'del'    => array(),
				'mark'   => array(),
				'strong' => array(),
				'small'  => array(),
				'em'     => array(),
				'i'      => array(),
				'sub'    => array(),
				'sup'    => array(),
				'u'      => array(),
			);
			return $allowed_html;
		}

		if ( 'image' === $type ) {
			$allowed_html = array(
				'img' => array(
					'alt'   => true,
					'width' => true,
					'title' => true,
					'src'   => true,
				),
			);
			return $allowed_html;
		}

		if ( 'column' === $type ) {
			$allowed_html = array(
				'a'      => array(
					'href'    => true,
					'title'   => true,
					'target'  => true,
					'rel'     => true,
					'onclick' => true,
				),
				'div'    => array(
					'class' => true,
				),
				'th'     => array(
					'id'    => true,
					'class' => true,
					'scope' => true,
					'style' => true,
				),
				'img'    => array(
					'alt'   => true,
					'width' => true,
					'title' => true,
					'src'   => true,
				),
				'label'  => array(
					'for'   => true,
					'class' => true,
				),
				'input'  => array(
					'type'  => true,
					'name'  => true,
					'value' => true,
				),
				'span'   => array(
					'class' => true,
				),
				'br'     => array(
					'clear' => true,
				),
				'b'      => array(),
				'del'    => array(),
				'mark'   => array(),
				'strong' => array(),
				'small'  => array(),
				'em'     => array(),
				'i'      => array(),
				'sub'    => array(),
				'sup'    => array(),
				'u'      => array(),
			);
			return $allowed_html;
		}

		if ( 'div' === $type ) {
			$allowed_html = array(
				'div' => array( 'class' => true ),
				'p'   => array(),
			);
			return $allowed_html;
		}

		if ( 'span' === $type ) {
			$allowed_html = array(
				'span' => array(
					'class' => true,
				),
			);
			return $allowed_html;
		}

		if ( 'option' === $type ) {
			$allowed_html = array(
				'option' => array(
					'value'    => true,
					'selected' => true,
					'disabled' => true,
					'hidden'   => true,
				),
			);
			return $allowed_html;
		}

		if ( 'qform' === $type ) {
			$allowed_html = array(
				'p'        => array(
					'class' => true,
				),
				'a'        => array(
					'href' => true,
				),
				'br'       => array(),
				'span'     => array(
					'class' => true,
				),
				'fieldset' => array(
					'class' => true,
				),
				'legend'   => array(),
				'ul'       => array(
					'id' => true,
				),
				'li'       => array(),
				'table'    => array(
					'class'       => true,
					'cellpadding' => true,
					'cellspacing' => true,
					'width'       => true,
				),
				'tbody'    => array(),
				'tr'       => array(
					'class' => true,
				),
				'th'       => array(
					'style'  => true,
					'scope'  => true,
					'valign' => true,
					'label'  => true,
				),
				'td'       => array(
					'style'    => true,
					'name'     => true,
					'textarea' => true,
					'rows'     => true,
					'cols'     => true,
					'id'       => true,
				),
				'textarea' => array(
					'id'    => true,
					'name'  => true,
					'style' => true,
					'rows'  => true,
					'cols'  => true,
				),
				'form'     => array(
					'name'   => true,
					'method' => true,
					'action' => true,
				),
				'label'    => array(
					'for' => true,
				),
				'input'    => array(
					'type'        => true,
					'name'        => true,
					'value'       => true,
					'class'       => true,
					'placeholder' => true,
					'size'        => true,
					'id'          => true,
					'list'        => true,
					'checked'     => true,
				),
				'button'   => array(
					'class' => true,
					'input' => true,
					'type'  => true,
					'id'    => true,
					'name'  => true,
				),
				'img'      => array(
					'src' => true,
					'alt' => true,
				),
				'option'   => array(
					'value'    => true,
					'selected' => true,
					'disabled' => true,
					'hidden'   => true,
				),
				'select'   => array(
					'id'       => true,
					'name'     => true,
					'multiple' => true,
					'size'     => true,
				),
			);
			return $allowed_html;
		}

		if ( 'quote' === $type ) {
			$allowed_html = array(
				'a'     => array(
					'href'  => true,
					'title' => true,
					'class' => true,
					'rel'   => true,
				),
				'div'   => array(
					'class' => true,
					'style' => true,
				),
				'input' => array(
					'class' => true,
					'type'  => true,
					'value' => true,
				),
				'img'   => array(
					'src'    => true,
					'id'     => true,
					'hspace' => true,
					'align'  => true,
				),
				'br'    => array(
					'clear' => true,
				),
				'hr'    => array(),
			);
			return $allowed_html;
		}

		if ( 'paginate' === $type ) {
			$allowed_html = array(
				'a'     => array(
					'href'  => true,
					'title' => true,
					'class' => true,
				),
				'div'   => array(
					'class' => true,
				),
				'span'  => array(
					'class' => true,
				),
				'input' => array(
					'class' => true,
					'id'    => true,
					'title' => true,
					'type'  => true,
					'name'  => true,
					'value' => true,
					'size'  => true,
				),
				'label' => array(
					'for'   => true,
					'class' => true,
				),
			);
			return $allowed_html;
		}

		if ( 'print' === $type ) {
			$allowed_html = array(
				'a'     => array(
					'href'  => true,
					'title' => true,
					'class' => true,
				),
				'div'   => array(
					'class' => true,
				),
				'th'    => array(
					'id'    => true,
					'class' => true,
					'scope' => true,
					'style' => true,
				),
				'label' => array(
					'for'   => true,
					'class' => true,
				),
				'input' => array(
					'class'    => true,
					'id'       => true,
					'title'    => true,
					'type'     => true,
					'scope'    => true,
					'style'    => true,
					'checkbox' => true,
				),
			);
			return $allowed_html;
		}
	}

	/**
	 * Mimic JS console.log
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param mixed $log - data set to log.
	 */
	public function console_log( $log ) {
		$js_code = 'console.log( ' . wp_json_encode( $log, JSON_HEX_TAG ) . ' );';
		$js_code = '<script>' . $js_code . '</script>';
		// output here via echo var dump etc.
	}

	/**
	 * Remove api setting when deactivating plugin
	 * and the options but only if enabled to remove.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function deactivation() {
		if ( isset( $this->other_options['admin_reset'] ) ) {
			delete_option( 'rss-llama-settings-display' );
			delete_option( 'rss-llama-settings-sidebar' );
			delete_option( 'rss-llama-settings-other' );
		}

		unregister_setting( 'rss-llama-settings-display', 'rss-llama-settings-display' );
		unregister_setting( 'rss-llama-settings-sidebar', 'rss-llama-settings-sidebar' );
		unregister_setting( 'rss-llama-settings-other', 'rss-llama-settings-other' );
	}

	/**
	 * Feed cache lifetime.
	 *
	 * @since 0.1
	 * @access public
	 *
	 * Return int - Time of lifetime in seconds.
	 */
	public function feed_cache_transient_lifetime() {
		return 3600; // 1 hour.
	}

	/**
	 * Filter link array for ones with rss and return updated array.
	 *
	 * @since 0.4
	 * @access public
	 *
	 * @param array $feeds - List of links data.
	 *
	 * return array $feeds - Updated array stripped of links with no feed.
	 */
	public function filter_links_for_rss( $feeds ) {
		$feedindex = 0;
		foreach ( $feeds as $feed ) {
			$rsslen = strlen( $feed->link_rss );

			if ( 0 === $rsslen ) {
				unset( $feeds[ $feedindex ] );
			}

			++$feedindex;
		}
		return $feeds;
	}

	/**
	 * Information about plugin.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $i - Field name to get.
	 *
	 * returns string - Field text.
	 */
	public function information( $i ) {
		$data = get_plugin_data( __FILE__ );
		$info = $data[ $i ];
		return $info;
	}

	/**
	 * Plugin init.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function init() {

		// Define i18n language folder.
		add_action( 'text_domain', array( $this, 'text_domain' ) );

		// Process any $_POSTs $_GETs $_REQUESTs for front and back.
		add_action( 'init', array( $this, 'plugin_posts' ) );

		// Summary no priv.
		add_action( 'wp_ajax_nopriv_plugin_summary_callback', array( $this, 'plugin_summary_callback' ) );

		// Summary priv.
		add_action( 'wp_ajax_plugin_summary_callback', array( $this, 'plugin_summary_callback' ) );

		// Category no priv.
		add_action( 'wp_ajax_nopriv_plugin_category_callback', array( $this, 'plugin_category_callback' ) );

		// Category priv.
		add_action( 'wp_ajax_plugin_category_callback', array( $this, 'plugin_category_callback' ) );

		// PAGE VIEW - only load if viewing the front page.
		if ( ! is_admin() ) {

			// Shortcodes for starting plugin.
			add_action( 'init', array( $this, 'shortcodes' ) );

			add_action( 'init', array( $this, 'shortcodes_post' ) );

			// load page scripts and styles.
			add_action( 'wp_enqueue_scripts', array( $this, 'plugin_scripts' ) );

			// Set the feed cache lifetime.
			add_filter( 'wp_feed_cache_transient_lifetime', array( $this, 'feed_cache_transient_lifetime' ) );
		} else {

			// Load admin scripts and styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

			// Manage link.
			if ( ! has_filter( 'plugin_action_links_' . RL_NAME ) ) {
				add_filter( 'plugin_action_links_' . RL_NAME, array( $this, 'manage_link' ) );
			}

			// Enable Links menu for installs after 3.5. Must be done on each page load. Not static.
			add_filter( 'pre_option_link_manager_enabled', '__return_true' );

			// Settings link.
			add_action( 'admin_menu', array( $this, 'settings_link' ) );

			// Admin page fields.
			if ( ! did_action( 'admin_init', array( $this, 'admin_load' ) ) ) {
				add_action( 'admin_init', array( $this, 'admin_load' ) );
			}
		}
	}

	/**
	 * Admin manage plugin link, admin panel -> plugins.
	 * prepend before | deactivate | edit
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $links - plugin settings default links.
	 *
	 * return array $links - updated array with manage link added.
	 */
	public function manage_link( $links ) {
		$admin_manage_link = '<a href = "admin.php?page=rss-llama">Manage</a>';
		array_unshift( $links, $admin_manage_link );
		return $links;
	}

	/**
	 * Success and Error messaging.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $msg    - The message to echo.
	 * @param string $yaynay - yay, nay, nonce.
	 *
	 * @return string - Html div with message.
	 */
	public function message( $msg, $yaynay ) {
		if ( 'yay' === $yaynay ) {
			return '<div class="updated rlmsg"><p>' . esc_html( $msg ) . '</p></div>';
		}

		if ( 'nay' === $yaynay ) {
			return '<div class="error rlmsg"><p>' . esc_html( $msg ) . '</p></div>';
		}

		if ( 'nonce' === $yaynay ) {
			return '<div class="error rlmsg"><p>' . esc_html__( 'Security token mismatch, please reload the page and try again.', 'quotes-llama' ) . '</p></div>';
		}
	}

	/**
	 * Scripts and styles.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function plugin_scripts() {

		// Plugin style sheet.
		wp_enqueue_style( 'rss_llama_style', RL_URL . 'includes/css/rss-llama-style.css', '1.0.0', 'all' );

		// rss-llama-wafer.js is a rename of jquery.cookie.js. It causes a conflict with Installs of Mod_Security to have cookie in a filename.
		wp_enqueue_script( 'rss_llama_cookie', RL_URL . 'includes/js/rss-llama-wafer.js', array( 'jquery' ), RL_PLUGIN_VERSION, false );

		// Rewrite rules.
		wp_enqueue_script( 'rss_llama_rewrite', RL_URL . 'includes/js/rss-llama-rewrite-rules.js', array( 'jquery' ), RL_PLUGIN_VERSION, false );

		// Single feed.
		wp_enqueue_script( 'rss_llama_single', RL_URL . 'includes/js/rss-llama-single.js', array( 'jquery' ), RL_PLUGIN_VERSION, true );

		// Plugin functions.
		wp_enqueue_script( 'rss_llama_body', RL_URL . 'includes/js/rss-llama-functions.js', array( 'jquery' ), RL_PLUGIN_VERSION, true );

		// Summary feeds.
		wp_enqueue_script( 'rss_llama_summary', RL_URL . 'includes/js/rss-llama-summary.js', array( 'jquery' ), RL_PLUGIN_VERSION, true );

		// var path to admin-ajax.php.
		wp_localize_script( 'rss_llama_summary', 'rss_llama_AJAX', array( 'rss_llama_ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		// if our javascript variables are empty make them 0.
		if ( isset( $this->display_options['rss_llama_random'] ) ) {
			$rss_llama_random = $this->display_options['rss_llama_random'];
		} else {
			$rss_llama_random = 0;
		}

		if ( isset( $this->display_options['rss_llama_scrolltop'] ) ) {
			$rss_llama_scrolltop = $this->display_options['rss_llama_scrolltop'];
		} else {
			$rss_llama_scrolltop = 0;
		}

		if ( isset( $this->display_options['rss_llama_excerpt'] ) ) {
			$rss_llama_excerpt = $this->display_options['rss_llama_excerpt'];
		} else {
			$rss_llama_excerpt = 0;
		}

		$rss_llama_body = array(
			'rss_llama_sidebarpos'      => $this->sidebar_options['rss_llama_sidebarpos'], // var for sidebar position.
			'rss_llama_random_enabled'  => $rss_llama_random, // var for random site enabled.
			'rss_llama_scrolltop'       => $rss_llama_scrolltop, // var for scroll to top icon.
			'rss_llama_body_colorlight' => $this->sidebar_options['rss_llama_colorlight'], // var for page light color.
			'rss_llama_body_colordark'  => $this->sidebar_options['rss_llama_colordark'], // var for page dark color.
		);

		// Plugin functions.
		wp_localize_script( 'rss_llama_body', 'rss_llama_body', $rss_llama_body );

		$rss_llama_single = array(
			'rss_llama_single_colorlight' => $this->sidebar_options['rss_llama_colorlight'], // var for single light color.
			'rss_llama_single_colordark'  => $this->sidebar_options['rss_llama_colordark'], // var for single dark color.
		);

		// Single feed.
		wp_localize_script( 'rss_llama_single', 'rss_llama_single', $rss_llama_single );

		$rss_llama_summary = array(
			'rss_llama_excerpt'            => $rss_llama_excerpt, // var for display excerpt.
			'rss_llama_summary_colorlight' => $this->sidebar_options['rss_llama_colorlight'], // var summary light color.
			'rss_llama_summary_colordark'  => $this->sidebar_options['rss_llama_colordark'], // var summary light dark.
		);

		// Summary feed.
		wp_localize_script( 'rss_llama_summary', 'rss_llama_summary', $rss_llama_summary );

		// Enable the admin dashicons set for the front-end.
		wp_enqueue_style( 'dashicons-style', get_stylesheet_uri(), array( 'dashicons' ), RL_PLUGIN_VERSION );
	}

	/**
	 * Process any $_POSTs $_GETs $_REQUESTs.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function plugin_posts() {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( isset( $_POST['single'] ) ) {
			$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';

			// Single feed.
			if ( wp_verify_nonce( $nonce, 'rss-llama-nonce-single' ) ) {
				$this->plugin_single_callback(
					isset( $_POST['single'] ) ? sanitize_text_field( wp_unslash( $_POST['single'] ) ) : '',
					isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '',
					isset( $_POST['site'] ) ? sanitize_text_field( wp_unslash( $_POST['site'] ) ) : '',
					isset( $_POST['feed'] ) ? esc_url_raw( wp_unslash( $_POST['feed'] ) ) : '',
					isset( $_POST['image'] ) ? esc_url_raw( wp_unslash( $_POST['image'] ) ) : '',
					isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '',
					isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : ''
				);
			}
			exit;
		}

		if ( isset( $_POST['rss_llama_referral'] ) ) {

			$addr     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			$agent    = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			$refer    = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
			$server   = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
			$blog     = isset( $_POST['rss_llama_refer_blog'] ) ? sanitize_text_field( wp_unslash( $_POST['rss_llama_refer_blog'] ) ) : '';
			$referral = isset( $_POST['rss_llama_referral'] ) ? esc_url_raw( wp_unslash( $_POST['rss_llama_referral'] ) ) : null;
			$text     = isset( $_POST['rss_llama_refer_text'] ) ? sanitize_text_field( wp_unslash( $_POST['rss_llama_refer_text'] ) ) : '';

			// Submit link.
			if ( wp_verify_nonce( $nonce, 'rss-llama-nonce-referral' ) ) {
				$ip        = preg_replace( '/[^0-9., ]/', '', $addr );
				$agent     = $agent;
				$referer   = $refer;
				$blog      = $blog;
				$to        = sanitize_email( $this->sidebar_options['rss_llama_refer_email'] );
				$subject   = esc_html__( 'Link referral', 'rss-llama' );
				$link      = esc_url_raw( $referral );
				$message   = $text;
				$headers   = array();
				$headers[] = 'X-Originating-IP: ' . $ip;
				$headers[] = 'X-Mailer: RSS llama (' . $server . ')';
				$headers[] = 'Content-type: text/plain; charset=' . get_bloginfo( 'charset' );

				mail(
					$to,
					$blog . ': ' . $subject,
					esc_html__( 'Submitted by:' ) .
					wp_kses_post( $ip . ' - ' . $agent ) . " \r\n" .
					esc_html__( 'Referred Link:' ) .
					wp_kses_post( $link ) . " \r\n" .
					esc_html__( 'Submitted from:' ) .
					wp_kses_post( $referer ) . " \r\n\r\n" .
					esc_html__( 'Description:' ) .
					wp_kses_post( $message ),
					implode( "\n", $headers )
				);
			}
			exit;
		}
	}

	/**
	 * Renders the html page view.
	 *
	 * @since 0.1
	 * @access private
	 *
	 * @param string $cat = Category to load.
	 */
	private function plugin_page( $cat = '' ) {
		global $wpdb;

		// Todays date.
		$today_date = gmdate( 'l' );

		// Yesterdays date.
		$yesterday_date = gmdate( 'l', strtotime( '-1 day' ) );

		// A local id for random feed and next/prev feed.
		$local_id = 0;

		// WordPress Links Manager table name.
		$table_name = $wpdb->prefix . 'links';

		// Count of rss links in database.
		$num = count( get_bookmarks() );

		// Current feeds in json.
		$jsfeeds = '';

		// Return string.
		$rl_return = '';

		// Any rss links? Check arguments for a link category.
		if ( $num < 1 ) {
			$rl_return .= esc_html__( 'There are no links with RSS Feeds! Add RSS feeds to your links to populate this page.', 'rss-llama' );
		} else {
			if ( ! $cat ) {
				$feeds = get_bookmarks(
					array(
						'orderby'        => 'name',
						'order'          => 'ASC',
						'hide_invisible' => 1,
					)
				);

				// Used to count categories.
				$allfeeds = $this->filter_links_for_rss( $feeds );
			} else {

				// Just category feeds.
				$feeds = get_bookmarks(
					array(
						'orderby'        => 'name',
						'order'          => 'ASC',
						'category_name'  => $cat,
						'hide_invisible' => 1,
					)
				);

				// All rss feeds used to count categories.
				$allfeeds = get_bookmarks(
					array(
						'orderby'        => 'name',
						'order'          => 'ASC',
						'hide_invisible' => 1,
					)
				);

				$allfeeds = $this->filter_links_for_rss( $allfeeds );
			}

			// Remove links that have no rss.
			$feeds = $this->filter_links_for_rss( $feeds );

			// Encode feed array into string but only if there is data.
			if ( count( $feeds ) > 0 ) {
				$jsfeeds = wp_json_encode( $feeds );
			}

			// Create array of first letter for index.
			$firstletterarr = array();

			foreach ( $feeds as $alpha ) {
				$index                      = strtoupper( substr( $alpha->link_name, 0, 1 ) );
				$firstletterarr[ $index ][] = $alpha;
			}

			// 'Loading...' div.
			$rl_return .= '<div class="rss-llama-loading"></div>';

			// Begin container div.
			$rl_return .= '<div class="rss-llama-container">';

			// Check if category buttons are enabled.
			if ( isset( $this->display_options['rss_llama_buttoncat'] ) ) {

				// Get list of link categories.
				$taxonomy   = 'link_category';
				$categories = get_terms( $taxonomy );

				// If we have categories, filter ones without any rss feeds.
				if ( $categories ) {
					$rl_return .= '<div class = "rss-llama-button-categories">';
					foreach ( $categories as $category ) {
						if ( $category->count > 0 ) {

							// Start category index.
							$categoryindex = 0;

							// Count matches of link category to category.
							$categorymatches = 0;
							foreach ( $allfeeds as $allfeed ) { // Check that this category has a link with an rss feed.
								$bookmark = get_bookmark( $allfeed->link_id ); // Get links bookmark data for its name, id, etc.
								foreach ( $bookmark->link_category as $linkcat ) { // Each link can have multiple categories.
									$linkcategory = get_term( $linkcat, 'link_category' ); // Get links category data.
									if ( $linkcategory->name === $category->name ) { // Count if we have any matches between link category name and category name.
										++$categorymatches; // Increment index if match found.
									}
								}
							}

							// Build button or remove category if none matched.
							if ( $categorymatches ) {
								$_wpnonce = wp_create_nonce( 'rss_llama_category' );
								if ( isset( $this->display_options['rss_llama_buttoncount'] ) ) {
									$button_count = ' <small> (' . esc_html( $categorymatches ) . ')</small>';
								} else {
									$button_count = '';
								}

								$rl_return .= '<button id = "' . esc_attr( $category->name ) . '" ' .
									'_wpnonce = "' . esc_attr( $_wpnonce ) . '" ' .
									'title = "' . esc_attr( $category->name ) . '" ' .
									'alt = "' . esc_attr( $category->name ) . '">';
								$rl_return .= esc_html( $category->name );
								$rl_return .= wp_kses_post( $button_count );
								$rl_return .= '</button></a>';
							} else {
								unset( $categories[ $categoryindex ] );
							}

							// Increment category array index.
							++$categoryindex;
						}
					}
					$rl_return .= '</div>';
				}
			}

			// Sidebar div.
			$rl_return .= '<div class = "rss-llama-sidebar">';

			// Referral form, if enabled.
			if ( isset( $this->display_options['rss_llama_refer'] ) ) {
				$rl_return .= '<div class = "rss-llama-heading rss-llama-border-small">';
				$rl_return .= '<div class = "rss-llama-response">';
				$rl_return .= esc_html( $this->sidebar_options['rss_llama_refer_requesttext'] );
				$rl_return .= '</div>';
				$rl_return .= '<form action = "">';
				$rl_return .= '<input type = "hidden" ' .
					'id = "_wpnonce" ' .
					'name = "_wpnonce" ' .
					'value ="' . esc_attr( wp_create_nonce( 'rss-llama-nonce-referral' ) ) . '">';
				$rl_return .= '<div class = "rss-llama-refer-fields">';
				$rl_return .= '<input type = "hidden" ' .
					'id = "rss_llama_refer_blog" ' .
					'name = "rss_llama_refer_blog" ' .
					'value = "' . esc_attr( get_option( 'blogname' ) ) . '">';
				$rl_return .= '<input type = "hidden" ' .
					'id = "rss_llama_refer_response" ' .
					'name = "rss_llama_refer_response" ' .
					'value = "' . esc_attr( $this->sidebar_options['rss_llama_refer_responsetext'] ) . '">';
				$rl_return .= '<input type = "hidden" ' .
					'id = "rss_llama_refer_request" ' .
					'name = "rss_llama_refer_request" ' .
					'value = "' . esc_attr( $this->sidebar_options['rss_llama_refer_requesttext'] ) . '">';
				$rl_return .= '<input type = "url" ' .
					'id = "rss_llama_referral" ' .
					'name = "rss_llama_referral" ' .
					'placeholder = "https://" ' .
					'required = "true" ' .
					'style = "display:inline; width:100%;" ' .
					'title = "' . esc_attr__( 'Submit Link', 'rss-llama' ) . '">';
				$rl_return .= '<br>';
				$rl_return .= '<textarea id = "rss_llama_refer_text" ' .
					'name = "rss_llama_refer_text" ' .
					'placeholder = "' . esc_attr__( 'Comment.', 'rss-llama' ) . '" ' .
					'required = "true" ' .
					'style = "display:inline; width:100%;"></textarea>';
				$rl_return .= '</div>';
				$rl_return .= '<input type = "submit" ' .
					'class = "rss-llama-refer-link" ' .
					'name = "rss_llama_refer_link" ' .
					'value = "&#9993;" ' .
					'title = "' . esc_attr__( 'Submit Link', 'rss-llama' ) . '" ' .
					'id = "rss_llama_refer_submit">';
				$rl_return .= '</form>';
				$rl_return .= '</div>';
			}

			$rl_return .= '<div class = "rss-llama-heading rss-llama-border-small">';
			$rl_return .= esc_html__( 'Summary', 'rss-llama' );

			$rss_llama_nonce_summary = wp_create_nonce( 'rss-llama-nonce-summary' );

			$rl_return .= '<div class = "rss-llama-button-div">';
			$rl_return .= '<button class = "rss-llama-button" ' .
				'_wpnonce = "' . esc_attr( $rss_llama_nonce_summary ) . '" ' .
				'title = "' . esc_attr__( 'Grab summary for the last few hours.', 'rss-llama' ) . '" ' .
				'id = "' . esc_attr__( 'hourly', 'rss-llama' ) . '">';
			$rl_return .= esc_html__( 'Hourly', 'rss-llama' );
			$rl_return .= '</button>';
			$rl_return .= '<button class = "rss-llama-button" ' .
				'_wpnonce = "' . esc_attr( $rss_llama_nonce_summary ) . '" ' .
				'title = "' . esc_attr( 'Grab summary for ' . $yesterday_date . ' - ' . $today_date ) . '" ' .
				'id = "' . esc_attr__( 'daily', 'rss-llama' ) . '">';
			$rl_return .= esc_html( $yesterday_date ) . ' - ' . esc_html( $today_date );
			$rl_return .= '</button>';
			$rl_return .= '<button class = "rss-llama-button" ' .
				'_wpnonce = "' . esc_attr( $rss_llama_nonce_summary ) . '" ' .
				'title = "' . esc_attr__( 'Grab summary for the last week.', 'rss-llama' ) . '" ' .
				'id = "' . esc_attr__( 'weekly', 'rss-llama' ) . '">';
			$rl_return .= esc_html__( 'Weekly', 'rss-llama' );
			$rl_return .= '</button>';
			$rl_return .= '<button class = "rss-llama-button" ' .
				'_wpnonce = "' . esc_attr( $rss_llama_nonce_summary ) . '" ' .
				'title = "' . esc_attr__( 'Grab summary for the last month.', 'rss-llama' ) . '" ' .
				'id = "' . esc_attr__( 'monthly', 'rss-llama' ) . '">';
			$rl_return .= esc_html__( 'Monthly', 'rss-llama' );
			$rl_return .= '</button>';

			// Summary filter textbox, if enabled.
			if ( isset( $this->display_options['rss_llama_filterwords'] ) ) {
				$rl_return .= '<div class = "rss-llama-filterbox">';
				$rl_return .= esc_html__( 'Summary Filter', 'rss-llama' );
				$rl_return .= '<textarea name = "rss_llama_filter" ' .
					'class = "rss-llama-filter" ' .
					'placeholder = "' . esc_html__( 'Filter word, phrase, term, etc.', 'rss-llama' ) . '">';
				$rl_return .= '</textarea>';
				$rl_return .= '</div>';
			}
			$rl_return .= '</div></div>';

			// Index links.
			$rl_return .= '<div class = "rss-llama-heading rss-llama-border-small">';
			$rl_return .= esc_html__( 'Index', 'rss-llama' );
			$rl_return .= '</div>';

			// Index links buttons, populated by appending below.
			$rl_return .= '<div class = "rss-llama-legend">';

			foreach ( $firstletterarr as $letter => $letter_data ) {
				$rl_return .= '<a href = "#' . esc_attr( $letter ) . '" title = "' . esc_attr( $letter ) . '"><button>' . esc_html( $letter ) . '</button></a>';
			}

			$rl_return .= '</div>';

			foreach ( $firstletterarr as $letter => $letter_data ) {

				// sidebar heading letter.
				$rl_return .= '<div class = "rss-llama-heading rss-llama-border-small">';
				$rl_return .= '<a name = "' . esc_html( $letter ) . '">' . esc_html( $letter ) . '</a>';
				$rl_return .= '</div>';

				// Links under each sidebar heading.
				foreach ( $letter_data as $row ) {
					$id          = $row->link_id;
					$title       = $row->link_name;
					$description = $row->link_description;
					$site_url    = $row->link_url;
					$feed_url    = $row->link_rss;
					$image       = $row->link_image;

					$rl_return .= '<div class = "rss-llama-li">';
					$rl_return .= '<li>';
					$rl_return .= '<input type = "checkbox" ' .
						'class = "rss-llama-option" ' .
						'checked = "checked" ' .
						'id = "' . esc_attr( $id ) . '" ' .
						'title = "' . esc_html__( 'Include ', 'rss-llama' ) . '" ' .
						esc_html( $title ) .
						esc_html__( ' in the summary.', 'rss-llama' ) . '">';

					$rl_return .= '<span site = "' . esc_url( $site_url ) . '" ' .
						'feed = "' . esc_url( $feed_url ) . '" ' .
						'image = "' . esc_url( $image ) . '" ' .
						'alt = "' . esc_attr( $description ) . '" ' .
						'class = "rss-llama-link" ' .
						'title = "' . esc_attr( $title ) . '" ' .
						'localid = "' . esc_attr( $local_id ) . '" ' .
						'_wpnonce = "' . esc_attr( wp_create_nonce( 'rss-llama-nonce-single' ) ) . '">';
					$rl_return .= esc_html( $title );
					$rl_return .= '</span>';
					$rl_return .= '</li>';
					$rl_return .= '</div>';
					// Local id for next feed.
					$local_id++;
				}
			}

			$rl_return .= '</div>';

			// Div where feed output is rendered.
			$rl_return .= '<div class = "rss-llama-output"></div>';
			$rl_return .= '</div>';
		}

		// NAVIGATION, Next, Prev, and Top icons, if enabled.
		$rl_return .= '<div class = "rss-llama-navcontainer">';

		// Display collapse icon, if enabled.
		if ( isset( $this->display_options['rss_llama_collapse'] ) ) {
				$rl_return .= '<div class = "rss-llama-navdiv">';
				$rl_return .= '<div class = "rss-llama-collapse" ' .
					'title = "' . esc_html__( 'Toggle sidebar', 'rss-llama' ) . '">';
				$rl_return .= '<span class = "dashicons-before dashicons-leftright"></span>';
				$rl_return .= '</div>';
				$rl_return .= '</div>';
		}

		// Display Next Previous icons, if enabled.
		if ( isset( $this->display_options['rss_llama_nextprev'] ) ) {
				$rl_return .= '<div class = "rss-llama-navdiv">';
				$rl_return .= '<div class = "rss-llama-previous" ' .
					'title = "' . esc_html__( 'Previous Feed', 'rss-llama' ) . '">';
				$rl_return .= '<span class = "dashicons-before dashicons-arrow-up-alt2"></span>';
				$rl_return .= '</div>';
				$rl_return .= '</div>';
				$rl_return .= '<div class = "rss-llama-navdiv">';
				$rl_return .= '<div class = "rss-llama-next" ' .
					'title = "' . esc_html__( 'Next Feed', 'rss-llama' ) . '">';
				$rl_return .= '<span class = "dashicons-before dashicons-arrow-down-alt2"></span>';
				$rl_return .= '</div>';
				$rl_return .= '</div>';
		}

		// Display Top of Page icon, if enabled.
		if ( isset( $this->display_options['rss_llama_top'] ) ) {
				$rl_return .= '<div class = "rss-llama-navdiv">';
				$rl_return .= '<div class = "rss-llama-top" ' .
					'title = "' . esc_html__( 'Top of Page', 'rss-llama' ) . '">';
				$rl_return .= '<span class = "dashicons-before dashicons-arrow-up-alt"></span>';
				$rl_return .= '</div>';
				$rl_return .= '</div>';
		}

		$rl_return .= '</div>';

		// JS Array of feeds for summaries.
		$rl_return .= '<script>';
		$rl_return .= 'var rss_llama_feeds = ' . wp_kses_post( $jsfeeds );
		$rl_return .= '</script>';

		return $rl_return;
	}

	/**
	 * Information about plugin from this plugin file ( info at top ).
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @param string $i - Data to retrieve.
	 *
	 * return string $info - Text from plugin data.
	 */
	public function plugin_information( $i ) {
		$data = get_plugin_data( __FILE__ );
		$info = $data[ $i ];
		return $info;
	}

	/**
	 * Ajax success callback for rss-llama-single.js
	 * Gets feed data for a single feed and displays each post.
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @param string $title - Title of feed site..
	 * @param string $description - Description of feed site.
	 * @param string $site - URL of feed site.
	 * @param string $feed - URL of feed.
	 * @param string $image - URL of image.
	 * @param int    $id - Position in links list.
	 * @param string $nonce - security token.
	 */
	public function plugin_single_callback( $title, $description, $site, $feed, $image, $id, $nonce ) {
		if ( isset( $this->other_options['display_errors'] ) ) {
			$display_errors = $this->other_options['display_errors'];
		} else {
			$display_errors = 0;
		}

		if ( ! wp_verify_nonce( $nonce, 'rss-llama-nonce-single' ) ) {
			die( esc_html( $title ) . ' security token mismatch/expired. Refresh this page to try again.<br>' );
		}

		?>
		<div class = 'rss-llama-single-container'>

			<!-- Print the site title. -->
			<div class = 'rss-llama-single-header'>
				<img src = '<?php echo esc_url( $image ); ?>' border = '0'>
				<h2><?php echo esc_html( $title ); ?></h2>
				<p>
					<?php echo esc_html( $description ); ?><br><?php esc_html_e( 'Website:', 'rss-llama' ); ?>
					<a target = '_blank'
						href = '<?php echo esc_url( $site ); ?>'>
						<?php echo esc_html( $site ); ?>
					</a>
					<br><?php esc_html_e( 'RSS:', 'rss-llama' ); ?>
					<a target = '_blank'
						href = '<?php echo esc_url( $feed ); ?>'>
						<?php echo esc_html( $feed ); ?>
					</a>
				</p>
			</div>
			<?php
			// Get feed data.
			$rss = fetch_feed( $feed );

			// If feed returns an error and displaying errors in options.
			if ( is_wp_error( $rss ) ) {
				if ( $display_errors ) {
					echo esc_html( $rss->get_error_message() ) . '<br><br>';
				}
				return;
			}

			// If feed returns but has no get_item_quantity function.
			if ( ! method_exists( $rss, 'get_item_quantity' ) ) {
				if ( $display_errors ) {
					echo esc_html( 'Feed does not provide a get_item_quantity function.' ) . '<br><br>';
				}
				return;
			}

			// Feed quantity so get items.
			$maxitems  = $rss->get_item_quantity();
			$rss_items = $rss->get_items( 0, $maxitems );

			// If no items.
			if ( ! $rss_items ) {
				if ( $display_errors ) {
					?>
					<div class = 'rss-llama-single'>
						<?php esc_html_e( 'No items in this feed.', 'rss-llama' ); ?>
						<br>
					</div>
					<?php
				}
				return;
			}

			// Print the feed data.
			foreach ( $rss_items as $item ) {
				$item_description = $item->get_description() ? $item->get_description() : '';
				$item_permalink   = $item->get_permalink() ? $item->get_permalink() : '';
				$item_title       = $item->get_title() ? $item->get_title() : '';

				?>
				<div class = 'rss-llama-single rss-llama-border-medium'>
					<div class = 'rss-llama-single-title'>
						<a href = '<?php echo esc_url( $item_permalink ); ?>'
							target = '_blank'
							title = '<?php echo esc_attr( $item_title ); ?>'>
							<?php echo esc_html( $item_title ); ?>
						</a>
						<div class = 'rss-llama-single-date'>
							<?php esc_html_e( 'Posted', 'rss-llama' ); ?>
							<?php echo esc_html( $item->get_date( 'j F, Y' ) ); ?>
							<?php esc_html_e( 'at', 'rss-llama' ); ?>
							<?php echo esc_html( $item->get_date( 'h:i:s a' ) ); ?>
						</div>
					</div>
					<div class = 'rss-llama-single-content'>
						<?php echo wp_kses_post( $item_description ) . ' '; ?>
						<a href = '<?php echo esc_url( $item_permalink ); ?>'
							title = '<?php esc_attr_e( 'View the Post: ' ); ?><?php echo esc_attr( $item_title ); ?>'
							target = '_blank'>
							<span class = 'dashicons-before dashicons-admin-page'></span>
						</a>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * Ajax success callback for rss-llama-summary.js.
	 * Gets feed data for feed for given scope.
	 * Displays summary of posts by title with option to hover over the title to view the description.
	 * Keyword filtering to omit posts with unwanted terms.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function plugin_summary_callback() {
		$summary    = isset( $_REQUEST['summary'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['summary'] ) ) : '';
		$rssfeed    = isset( $_REQUEST['rss'] ) ? esc_url_raw( wp_unslash( $_REQUEST['rss'] ) ) : '';
		$name       = isset( $_REQUEST['name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['name'] ) ) : '';
		$scope      = isset( $_REQUEST['scope'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['scope'] ) ) : '';
		$keywords   = isset( $_REQUEST['filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['filter'] ) ) : '';
		$_wpnonce   = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		$today_date = strtotime( gmdate( 'j F Y' ) );

		if ( ! wp_verify_nonce( $_wpnonce, 'rss-llama-nonce-summary' ) ) {
			die( esc_html( $name ) . ' security token mismatch/expired. Refresh this page to try again.<br>' );
		}

		if ( isset( $this->other_options['display_errors'] ) ) {
			$display_errors = $this->other_options['display_errors'];
		} else {
			$display_errors = 0;
		}

		// Scope of summary.
		switch ( $scope ) {
			case 'hourly':
				$scope_date = strtotime( gmdate( 'j F Y g:i:s', strtotime( '-1 hours' ) ) );
				$today_date = strtotime( gmdate( 'j F Y g:i:s' ) );
				break;
			case 'daily':
				$scope_date = strtotime( gmdate( 'j F Y', strtotime( '-1 day' ) ) );
				break;
			case 'weekly':
				$scope_date = strtotime( gmdate( 'j F Y', strtotime( '-7 day' ) ) );
				break;
			case 'monthly':
				$scope_date = strtotime( gmdate( 'j F Y', strtotime( '-30 day' ) ) );
				break;
		}

		// Get this feeds data.
		$rss = fetch_feed( $rssfeed );

		// If feed returns an error.
		if ( is_wp_error( $rss ) ) {
			if ( $display_errors ) {
				?>
				<div class = 'rss-llama-summary-container'>
					<a class = 'rss-llama-summary-title'
						href = '<?php echo esc_url( $summary ); ?>'
						target = '_blank'
						title = '<?php echo esc_attr( $name ); ?>'
						alt = '<?php echo esc_attr( $summary ); ?>'>
						<?php echo esc_html( $name ); ?>
					</a>
					<a class = 'rss-llama-excerpt'
						href = '<?php echo esc_url( $summary ); ?>'
						target = '_blank'>
						<?php esc_html_e( '(Feed Error) - ' ); ?>
						<?php echo esc_html( $rss->get_error_message() ); ?>
						<span>
							<?php echo esc_html( $rss->get_error_message() ); ?>
						</span>
					</a>
				</div>
				<?php
			}
			die();
		}

		// If feed returns but has no get_item_quantity function.
		if ( ! method_exists( $rss, 'get_item_quantity' ) ) {
			if ( $display_errors ) {
				echo esc_html( 'Feed does not provide a get_item_quantity function.' ) . '<br><br>';
			}
			die();
		}

		// Feed quantity so get items.
		$maxitems  = $rss->get_item_quantity();
		$rss_items = $rss->get_items( 0, $maxitems );

		// If no items.
		if ( ! $rss_items ) {
			if ( $display_errors ) {
				?>
				<div class = 'rss-llama-summary-container rss-llama-border-medium'>
					<a href = '<?php echo esc_url( $summary ); ?>'
						target = '_blank'
						title = '<?php echo esc_attr( $name ); ?>'>
						<?php esc_html( $name ); ?>
					</a> -
					<a href = '<?php echo esc_url( $rss ); ?>'
						target = '_blank'>
						<?php esc_html_e( 'No items in this feed.', 'rss-llama' ); ?>
					</a>
				</div>
				<?php
			}
			die();
		}

		// Do we have any posts for this site.
		$post_count = 0;

		// We need only one post to continue.
		foreach ( $rss_items as $item ) {

			// item_date, if start is hourly then add in g:i:s.
			if ( 'hourly' === $scope ) {
				$item_date = $item->get_date( 'j F Y g:i:s' );
			} else {
				$item_date = $item->get_date( 'j F Y' );
			}
			$post_date = strtotime( $item_date );

			if ( $post_date === $today_date ) {
				$post_count++;
				break;
			} elseif ( $post_date >= $scope_date ) {
				$post_count++;
				break;
			}
		}

		// If posts, build output, separate yesterday from today and highlight today's date.
		if ( $post_count > 0 ) {
			?>
			<!-- Begin summary-container. -->
			<div class = 'rss-llama-summary-container rss-llama-border-small'>
				<a class = 'rss-llama-summary-title'
					href = '<?php echo esc_url( $summary ); ?>'
					target = '_blank'
					title = '<?php echo esc_attr( $name ); ?>'
					alt = '<?php echo esc_attr( $summary ); ?>'>
					<?php echo esc_html( $name ); ?>
				</a>
				<?php
				// Turn filter string into array.
				$filter      = explode( ',', $keywords );
				$filtercount = count( $filter );

				// Post data.
				foreach ( $rss_items as $item ) {

					// Format description.
					$desc  = $item->get_description();
					$desc  = wp_strip_all_tags( $desc );
					$desc  = substr( $desc, 0, 1000 );
					$desc  = trim( $desc );
					$title = $item->get_title();

					// If description is empty then omit any excerpt span.
					if ( $desc ) {
						$excerpt_span = '<span class="rss-llama-border-small">' . $desc . '</span>';
					} else {
						$excerpt_span = '';
					}

					// Filter keywords, if enabled and not empty or just whitespaces.
					if ( '' !== $keywords ) {
						$filterthis = 'off';

						// Check title and description for each filter keyword.
						for ( $i = 0; $i < $filtercount; $i++ ) {

							// Trim whitespace and convert to lowercase.
							$trimfilter = trim( strtolower( $filter[ $i ] ) );

							// If null or ending in comma.
							if ( null === $trimfilter ) {
								break;
							}

							// Search title and description.
							$strpostitle = strpos( strtolower( $title ), $trimfilter );
							$strposdesc  = strpos( strtolower( $desc ), $trimfilter );
							if ( false !== $strpostitle || 0 === $strpostitle || false !== $strposdesc || 0 === $strposdesc ) {
								$filterthis = 'on';

								if ( $display_errors ) {
									?>
									<a class = 'rss-llama-excerpt'
										href = '<?php echo esc_url( $item->get_permalink() ); ?>'
										target = '_blank'
										site = '<?php echo esc_attr( $name ); ?>'
										date = '<?php echo esc_attr( $item_date ); ?>'>
										<?php echo wp_kses_post( '<small>Filtered content - (<strike>' . $trimfilter . '</strike>)</small> - ' ); ?>
										<?php echo esc_html( $title ); ?>
										<?php echo wp_kses_post( $excerpt_span ); ?>
									</a>
									<?php
								}

								// Break so not to duplicate if other filters present.
								break;
							}
						}

						// If filtering then continue to next feed.
						if ( 'on' === $filterthis ) {
							continue;
						}
					}

					// $post_date, If start is hourly then include time g:i:s.
					if ( 'hourly' === $scope ) {
						$item_date = $item->get_date( 'j F Y g:i:s' );
					} else {
						$item_date = $item->get_date( 'j F Y' );
					}

					$post_date = strtotime( $item_date );

					// Separate todays from the rest by making it italic.
					if ( $post_date === $today_date ) {
						?>
						<a class = 'rss-llama-excerpt'
							href = '<?php echo esc_url( $item->get_permalink() ); ?>'
							target = '_blank'
							site = '<?php echo esc_attr( $name ); ?>'
							date = '<?php echo esc_attr( $item_date ); ?>'>
							<i><?php echo esc_html( gmdate( 'M j', $post_date ) ); ?></i> -
							<?php echo esc_html( $title ); ?>
							<?php echo wp_kses_post( $excerpt_span ); ?>
						</a>
						<?php
					} elseif ( $post_date >= $scope_date ) {
						?>
						<a class = 'rss-llama-excerpt'
							href = '<?php echo esc_url( $item->get_permalink() ); ?>'
							target = '_blank'
							site = '<?php echo esc_attr( $name ); ?>'
							date = '<?php echo esc_attr( $item_date ); ?>'>
							<?php echo esc_html( gmdate( 'M j', $post_date ) ); ?> -
							<?php echo esc_html( $title ); ?>
							<?php echo wp_kses_post( $excerpt_span ); ?>
						</a>
						<?php
					}
				}
				?>
			</div>
			<?php
			// Reset for next.
			$post_count = 0;
		}
		die();
	}

	/**
	 * Ajax success callback for button category links.
	 *
	 * @since 0.4
	 * @access public
	 *
	 * @param string $catbutton - button category.
	 */
	public function plugin_category_callback( $catbutton ) {
		$_wpnonce  = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		$catbutton = strtolower( $catbutton );

		if ( $catbutton && $_wpnonce ) {

			if ( wp_verify_nonce( $_wpnonce, 'rss_llama_category' ) ) {
				echo do_shortcode( '[rss-llama cat="' . $catbutton . '"]' );
				die();
			}

			die( 'Category security token mismatch. Please reload the page and try again.' );
		}
	}

	/**
	 * Plugin starts here.
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @param array $atts - Shortcode arguments like cat = ''.
	 */
	public function rss_llama_start( $atts ) {
		$att_array = shortcode_atts( array( 'cat' => '' ), $atts );

		if ( $att_array['cat'] ) {
			$category = $att_array['cat'];
			return $this->plugin_page( $category );
		} else {
			return $this->plugin_page();
		}
	}

	/**
	 * Admin settings link, admin panel -> settings.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function settings_link() {

		// Include Admin class.
		if ( ! class_exists( 'RssLlama_Admin' ) ) {
			require_once 'includes/classes/class-rssllama-admin.php';
		}
		$rss_llama_nonce = wp_create_nonce( 'rss_llama_nonce' );
		$rl_admin        = new RssLlama_Admin( $rss_llama_nonce );
		$rl_admin->plugin_settings_link();

		$import_nonce = isset( $_POST['rss_llama_import_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rss_llama_import_nonce'] ) ) : '';
		if ( wp_verify_nonce( $import_nonce, 'rss_llama_import_nonce' ) ) {

			// $_POST to Import rss.
			if ( isset( $_POST['rss_llama_import'] ) ) {
				include RL_PATH . 'includes/php/import.php';
			}
		}

		// Message for display in back-end.
		$rl_admin->msg = $this->msg;
	}

	/**
	 * Shortcodes.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function shortcodes() {
		add_shortcode( 'rss-llama', array( $this, 'rss_llama_start' ) );
	}

	/**
	 * Loads category from button.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function shortcodes_post() {
		$_wpnonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( wp_verify_nonce( $_wpnonce, 'rss_llama_category' ) ) {
			$catbutton = isset( $_POST['catbutton'] ) ? sanitize_text_field( wp_unslash( $_POST['catbutton'] ) ) : '';

			if ( $catbutton ) {
				$this->plugin_category_callback( $catbutton );
			}
		}
	}

	/**
	 * Define i18n language folder.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function text_domain() {
		load_plugin_textdomain( 'rss-llama', false, RL_URL . 'lang' );
	}
}

// Start the plugin in namespace Rss_Llama.
$rss_llama = new \Rss_Llama\RssLlama();
add_action( 'plugins_loaded', array( $rss_llama, 'init' ) );
?>
