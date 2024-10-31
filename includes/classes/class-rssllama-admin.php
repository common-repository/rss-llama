<?php
/**
 * Rss Llama Admin Class
 *
 * Description. Backend.
 *
 * @Link        http://wordpress.org/plugins/rss-llama/
 * @package     rss-llama
 * @since       2.0.0
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

namespace Rss_Llama;

/**
 * Begin Rss_Llama class.
 */
class RssLlama_Admin {

	/**
	 * Parent class.
	 *
	 * @since 2.0.0
	 * @var object
	 * @access private
	 */
	private $rl;

	/**
	 * The active admin tab we are curretly working with.
	 *
	 * @since 1.0.0
	 * @var string
	 * @access public
	 */
	public $rss_llama_active_tab;

	/**
	 * Current message.
	 *
	 * @since 2.0.0
	 * @var string
	 * @access public
	 */
	public $msg;

	/**
	 * Rss_Llama Admin class construct.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $nonce - Nonce.
	 */
	public function __construct( $nonce ) {

		// Instance of parent.
		$this->rl = new RssLlama();

		// Get clicked tab or set initial tab.
		if ( wp_verify_nonce( $nonce, 'rss_llama_nonce' ) ) {
			if ( isset( $_GET['tab'] ) ) {
				$this->rss_llama_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'display_options';
			} else {
				$this->rss_llama_active_tab = 'display_options';
			}
		}

		// Current message set in parent class.
		$this->msg = '';
	}

	/**
	 * Render admin options page.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function admin_page() {
		$allowed_html = $this->rl->allowed_html( 'div' );

		// Check for Link manager.
		if ( get_option( 'link_manager_enabled' ) ) {

			// User has permisson to view the admin page.
			if ( current_user_can( 'create_users' ) ) {

				// Display current message.
				echo wp_kses( $this->msg, $allowed_html );
				?>
				<div class = 'wrap'>
					<h2>
						<?php
						echo esc_html( $this->rl->plugin_information( 'Name' ) );
						echo ' - ' . esc_html( $this->rl->plugin_information( 'Version' ) );
						?>
					</h2>
						<h3><?php echo wp_kses_post( $this->rl->plugin_information( 'Description' ) ); ?></h3>
					<h2 class = 'nav-tab-wrapper'>
						<a href = '?page=rss-llama&tab=display_options'
							class = 'nav-tab <?php echo esc_attr( 'display_options' === $this->rss_llama_active_tab ? 'nav-tab-active' : '' ); ?>'>
							<?php esc_html_e( 'Display Options', 'rss-llama' ); ?>
						</a>
						<a href = '?page=rss-llama&tab=sidebar_options'
							class = 'nav-tab <?php echo 'sidebar_options' === $this->rss_llama_active_tab ? 'nav-tab-active' : ''; ?>'>
							<?php esc_html_e( 'Sidebar Options', 'rss-llama' ); ?>
						</a>
						<a href = '?page=rss-llama&tab=other_options'
							class = 'nav-tab <?php echo 'other_options' === $this->rss_llama_active_tab ? 'nav-tab-active' : ''; ?>'>
							<?php esc_html_e( 'Other Options', 'rss-llama' ); ?>
						</a>
						<a href = '?page=rss-llama&tab=manage_options'
							class = 'nav-tab <?php echo 'manage_options' === $this->rss_llama_active_tab ? 'nav-tab-active' : ''; ?>'>
							<?php esc_html_e( 'Manage Links', 'rss-llama' ); ?>
						</a>
						<a href = '?page=rss-llama&tab=short_codes'
							class = 'nav-tab <?php echo 'short_codes' === $this->rss_llama_active_tab ? 'nav-tab-active' : ''; ?>'>
							<?php esc_html_e( 'Shortcode', 'rss-llama' ); ?>
						</a>
					</h2> 
					<?php

					if ( 'display_options' === $this->rss_llama_active_tab ) {
						?>
						<!-- Using WordPress Settings API. -->
						<form action = 'options.php' method = 'POST'>
							<div class = 'admin-form'>
							<?php
								settings_fields( 'rss-llama-settings-display' );
								do_settings_sections( 'rss-llama' );
								submit_button( 'Save Display Options' );
							?>
							</div>
						</form>
						<?php
					}

					if ( 'sidebar_options' === $this->rss_llama_active_tab ) {
						?>
						<div class = 'admin-form'>
							<form action = 'options.php' method = 'POST'>
							<?php
								settings_fields( 'rss-llama-settings-sidebar' );
								do_settings_sections( 'rss-llama' );
								submit_button( 'Save Sidebar Options' );
							?>
							</form>
						</div>
						<?php
					}

					if ( 'other_options' === $this->rss_llama_active_tab ) {
						?>
						<div class = 'admin-form'>
							<form action = 'options.php' method = 'POST'>
							<?php
								settings_fields( 'rss-llama-settings-other' );
								do_settings_sections( 'rss-llama' );
								submit_button( 'Save Other Options' );
							?>
							</form>
						</div>
						<?php
					}

					if ( 'manage_options' === $this->rss_llama_active_tab ) {
						$allowed_html = $this->rl->allowed_html( 'qform' );
						?>
						<div class='rss-llama-inline'>
							<?php $admin_tabs_nonce = wp_create_nonce( 'rss_llama_admin_tabs' ); ?>

							<!-- Export rss. -->
							<form method='post' action='<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-admin/admin.php?page=rss-llama'> 
								<?php
									echo '<div class="rss-llama-admin-form"><h2><u>' . esc_html__( 'Export Links (Backup)', 'rss-llama' ) . '</u></h2></div>';
									echo '<p>' . esc_html__( 'Backup your Links to either .csv or .json formats.', 'rss-llama' ) . '</p>';
									wp_nonce_field( 'rss_llama_export_nonce', 'rss_llama_export_nonce' );
									submit_button( esc_html__( 'Export .csv', 'rss-llama' ), 'large', 'rss_llama_export_csv', false, array( 'rss_llama_export_csv' => 'rss' ) );
									echo '&nbsp';
									submit_button( esc_html__( 'Export .json', 'rss-llama' ), 'large', 'rss_llama_export_json', false, array( 'rss_llama_export_json' => 'rss' ) );
									echo '<p>' . esc_html__( 'The .csv delimiter can be set in the "other options" tab.', 'rss-llama' ) . '</p>';
								?>
							</form>

							<!-- Import rss -->
							<form name='' method='post' action='<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-admin/admin.php?page=rss-llama'  enctype='multipart/form-data'> 
								<?php
									wp_nonce_field( 'rss_llama_import_nonce', 'rss_llama_import_nonce' );
									echo '<div class="rss-llama-admin-form"><h2><u>' . esc_html__( 'Import Links (Restore)', 'rss-llama' ) . '</u></h2></div>';
									echo '<p>' . esc_html__( 'This import associates links to categories as they existed upon export.', 'rss-llama' ) . '</p>';
									echo '<p>' . esc_html__( 'This import does not create new categories.', 'rss-llama' ) . '</p>';
									echo '<p>' . esc_html__( 'Categories should already exist with matching terms and taxonomy.', 'rss-llama' ) . '</p>';
									echo '<p>' . esc_html__( 'You can exclude categories from the export in the other options tab.', 'rss-llama' ) . '</p>';
									echo '<p>' . esc_html__( 'Restore your Links from either .csv or .json formats. Browse for a file, then select the import button.', 'rss-llama' ) . '</p>';
									echo '<p><a href="' . esc_url( get_bloginfo( 'wpurl' ) ) . '/wp-admin/edit-tags.php?taxonomy=link_category">';
									esc_html_e( 'Manage Links Categories', 'rss-llama' );
									echo '</a></p>';
								?>
								<input type='file' class='button button-large' name='rss-llama-file' accept='.csv, .json'> 
								<?php
									submit_button( esc_html__( 'Import', 'rss-llama' ), 'secondary', 'rss_llama_import', true, array( 'rss_llama_import' => 'rss' ) );
								?>
							</form>
						</div>
						<?php
					}

					// Shortcodes tab.
					if ( 'short_codes' === $this->rss_llama_active_tab ) {
						?>
						<!-- Information and links. -->
						<div class = 'rss-llama-admin-container-left'>
						<span class="rss-llama-admin-form">
							<h2>
								<?php
								echo '<a href="' . esc_url( get_bloginfo( 'wpurl' ) ) . '/wp-admin/link-manager.php">';
								esc_html_e( 'Links Manager', 'rss-llama' );
								echo '</a>';
								?>
							</h2>
							<h2>
								<?php esc_html_e( 'To include this plugin in a block, page, or post:', 'rss-llama' ); ?>
							</h2>
							<p>
								<?php esc_html_e( 'All Links:', 'rss-llama' ); ?>
								<b><code>[rss-llama]</code></b>
								<br>
								<?php esc_html_e( 'Single Category:', 'rss-llama' ); ?>
								<b><code>[rss-llama cat='Category Name']</code></b>
							</p>
							<span class="rss-llama-admin-form">
								<h2>
								<?php esc_html_e( 'To include this plugin in a template file:', 'rss-llama' ); ?>
							</h2>
							<p>
								<?php esc_html_e( 'All Links:', 'rss-llama' ); ?>
								<b><code>do_shortcode( '[rss-llama]' );</code></b>
								<br>
								<?php esc_html_e( 'Single Category:', 'rss-llama' ); ?>
								<b><code>do_shortcode( '[rss-llama cat="Category Name"]' );</code></b>
							</p>
							<div class="rss-llama-admin-form">
								<h2>
									<?php echo esc_html( 'Support' ); ?>
								</h2>
							</div>
							<div class = 'rss-llama-admin-div'>
								<a href='https://wordpress.org/support/plugin/rss-llama/'
									target='_blank'
									title='<?php esc_attr_e( 'Support Forum', 'rss-llama' ); ?>'>
									<?php esc_html_e( 'Plugin Support Forum', 'rss-llama' ); ?>
								</a>
								<br>
								<a href='https://wordpress.org/support/view/plugin-reviews/rss-llama'
									target='_blank'
									title='<?php esc_attr_e( 'Rate the plugin / Write a review.', 'rss-llama' ); ?>'>
									<?php
									esc_html_e( ' Rate this plugin / Write a Review', 'rss-llama' );
									?>
								</a>
								<br>
								<a href="<?php echo esc_url( $this->rl->information( 'PluginURI' ) ); ?>"
									target="_blank"
									title="<?php echo esc_attr( $this->rl->information( 'Name' ) ); ?>">
									<?php echo esc_html( $this->rl->information( 'Name' ) ) . ' on WordPress'; ?>
								</a>
								<br>
								<a href='https://translate.wordpress.org/projects/wp-plugins/rss-llama/'
									target='_blank'
									title='<?php esc_attr_e( 'You can help translate this plugin into your language.', 'rss-llama' ); ?>'>
									<?php esc_html_e( 'Translate This Plugin', 'rss-llama' ); ?>
								</a>
								<br>
								<a href='https://oooorgle.com/copyheart/'
									target='_blank'
									title='<?php esc_attr_e( 'CopyHeart', 'rss-llama' ); ?>'>
									<?php esc_html_e( 'License: CopyHeart', 'rss-llama' ); ?>
								</a>
								<br>
								<a href="https://oooorgle.com/plugins/wp/rss-llama/"
									target="_blank"
									title="<?php esc_attr_e( 'Donate', 'rss-llama' ); ?>">
									<?php esc_html_e( 'Donations', 'rss-llama' ); ?>
								</a>
							</div>
						</div>
				<?php } ?>
				</div>
				<?php
			} else {
				echo wp_kses_post(
					$this->rl->message(
						esc_html__(
							'You do not have sufficient permissions to access this page.',
							'rss-llama'
						),
						'nay'
					)
				);
			}
		} else {
			$this->msg = $this->rl->message( esc_html__( 'Something went wrong!', 'rss-llama' ), 'nay' );

			// Display current message.
			echo wp_kses( $this->msg, $allowed_html );
			?>
			<div class = 'wrap'>
				<?php
				echo '<div class="rss-llama-admin-form"><h2><u>' . esc_html__( 'Links Manager Status', 'rss-llama' ) . '</u></h2></div>';
				echo '<p>' . esc_html__( 'RSS Llama was unable to enable the Links Manager.', 'rss-llama' ) . '</p>';
				?>
				<p>
					<?php
					esc_html_e( 'WordPress allows you to store a set of external links. This article describes more:', 'rss-llama' );
					?>
					<a href='https://codex.wordpress.org/Links_Manager' target='_blank'>
					<?php esc_html_e( 'About Links Manager', 'rss-llama' ); ?>
					</a>
				</p>
				<p>
					<?php
					esc_html_e( 'RSS Llama retrieves RSS feeds from the WordPress Links Manager. As of WordPress version 3.5 the WordPress Links Manager is hidden by default, but still remains fully functional when enabled. You could try the following plugin to enable the Links Manager: ', 'rss-llama' );
					?>
					<a href='https://wordpress.org/plugins/link-manager/' target='_blank' title='Links Manager'>
					<?php esc_html_e( 'Links Manager plugin.', 'rss-llama' ); ?> 
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 *  Display category button checkbox in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function buttoncat_callback() {
		?>
		<input type = 'checkbox'
			id = 'rss_llama_buttoncat'
			name = 'rss-llama-settings-display[rss_llama_buttoncat]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->display_options['rss_llama_buttoncat'] ) ) {
				echo 'checked';}
			?>
			>
		<label for = 'rss_llama_buttoncat'>
			<?php esc_html_e( 'Display category link buttons.', 'rss-llama' ); ?>
		</label>
		<?php
	}

	/**
	 *  Display category button count checkbox in admin options.
	 *
	 * @since 0.6.1
	 * @access public
	 */
	public function buttoncount_callback() {
		?>
		<input type = 'checkbox'
			id = 'rss_llama_buttoncount'
			name = 'rss-llama-settings-display[rss_llama_buttoncount]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->display_options['rss_llama_buttoncount'] ) ) {
				echo 'checked';}
			?>
			>
		<label for = 'rss_llama_buttoncount'>
			<?php esc_html_e( 'Display a count on each category button.', 'rss-llama' ); ?>
		</label>
		<?php
	}

	/**
	 *  Toggle sidebar in admin options.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function collapse_callback() {
		?>
		<input type = 'checkbox'
			id = 'rss_llama_collapse'
			name = 'rss-llama-settings-display[rss_llama_collapse]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->display_options['rss_llama_collapse'] ) ) {
				echo 'checked';}
			?>
			>
		<label for = 'rss_llama_collapse'>
			<?php esc_html_e( 'Toggle sidebar Icon.', 'rss-llama' ); ?>
		</label>
		<?php
	}

	/**
	 *  Background color in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function colordark_callback() {
		?>
		<input type = 'text'
			id = 'rss_llama_colordark'
			name = 'rss-llama-settings-sidebar[rss_llama_colordark]'
			value = '<?php echo esc_attr( $this->rl->sidebar_options['rss_llama_colordark'] ); ?>'>
		<label for = 'rss_llama_colordark'>
			<?php esc_html_e( 'Set this to your themes background color', 'rss-llama' ); ?>
		</label>
		<?php
	}

	/**
	 *  Foreground color in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function colorlight_callback() {
		?>
		<input type = 'text'
			id = 'rss_llama_colorlight'
			name = 'rss-llama-settings-sidebar[rss_llama_colorlight]'
			value = '<?php echo esc_attr( $this->rl->sidebar_options['rss_llama_colorlight'] ); ?>'>
		<label for = 'rss_llama_colorlight'>
			<?php esc_html_e( 'Set this to your themes foreground color', 'rss-llama' ); ?>
		</label>
		<?php
	}

	/**
	 *  Display feed errors in summaries.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function display_errors_callback() {
		?>
		<input type = 'checkbox'
			id = 'display_errors'
			name = 'rss-llama-settings-other[display_errors]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->other_options['display_errors'] ) ) {
				echo 'checked';
			}
			?>
			>
			<label for = 'display_errors'>
				<?php esc_html_e( 'Display feed errors in summaries.', 'rss-llama' ); ?>
			</label>
		<?php
	}

	/**
	 *  Summary Excerpt in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function excerpt_callback() {
		?>
		<input type = 'checkbox'
			id = 'rss_llama_excerpt'
			name = 'rss-llama-settings-display[rss_llama_excerpt]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->display_options['rss_llama_excerpt'] ) ) {
				echo 'checked';}
			?>
			>
		<label for = 'rss_llama_excerpt'>
			<?php esc_html_e( 'Display post excerpt when hovering a summary link.', 'rss-llama' ); ?>
		</label>
		<?php
	}

	/**
	 *  Include or exclude categories in exports.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function export_categories_callback() {
		?>
		<input type = 'checkbox'
			id = 'rss_llama_export_categories'
			name = 'rss-llama-settings-other[rss_llama_export_categories]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->other_options['rss_llama_export_categories'] ) ) {
				echo 'checked';
			}
			?>
			>
			<label for = 'rss_llama_export_categories'>
				<?php esc_html_e( 'Include categories in exports.', 'rss-llama' ); ?>
			</label>
		<?php
	}

	/**
	 * Options tab Export Delimiter.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function export_delimiter_callback() {
		?>
		<input type='text'
			id='rss_llama_export_delimiter'
			name='rss-llama-settings-other[rss_llama_export_delimiter]'
			value='<?php echo esc_attr( $this->rl->other_options['rss_llama_export_delimiter'] ); ?>'
			size='3'>
		<label for='rss_llama_export_delimiter'>
			<?php
			esc_html_e( '.csv delimiter.', 'rss-llama' );
			echo '<br>' . esc_html__( 'Field separator for importing and exporting Links in .csv format. Do not use dash (-) as it is used to separate your categories.', 'rss-llama' );
			?>
		</label>
		<?php
	}

	/**
	 *  Summary Filter in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function filterwords_callback() {
		?>
			<input type = 'checkbox'
				id = 'rss_llama_filterwords'
				name = 'rss-llama-settings-display[rss_llama_filterwords]'
				style = 'display: inline;'
				<?php
				if ( isset( $this->rl->display_options['rss_llama_filterwords'] ) ) {
					echo 'checked';}
				?>
				>
			<label for = 'rss_llama_filterwords'>
				<?php esc_html_e( 'Display the summary filter textbox.', 'rss-llama' ); ?>
			</label>
			<?php
	}

	/**
	 *  Next Prev icons in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function nextprev_callback() {
		?>
		<input type = 'checkbox'
			id = 'rss_llama_nextprev'
			name = 'rss-llama-settings-display[rss_llama_nextprev]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->display_options['rss_llama_nextprev'] ) ) {
				echo 'checked';}
			?>
			>
		<label for = 'rss_llama_nextprev'>
			<?php esc_html_e( 'Display next/previous feed icons.', 'rss-llama' ); ?>
		</label>
		<?php
	}

	/**
	 * Admin settings link, admin panel -> settings.
	 * This is where permission to manage the plugin is set as well.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function plugin_settings_link() {
		add_options_page( 'RSS llama', 'RSS llama', 'create_users', 'rss-llama', array( $this, 'admin_page' ) );
	}

	/**
	 * Setup admin page settings, sections, and fields.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function page_fields() {
		register_setting(
			'rss-llama-settings-display',
			'rss-llama-settings-display',
			array(
				$this,
				'sanitize',
			)
		);

		register_setting(
			'rss-llama-settings-sidebar',
			'rss-llama-settings-sidebar',
			array(
				$this,
				'sanitize',
			)
		);

		register_setting(
			'rss-llama-settings-other',
			'rss-llama-settings-other',
			array(
				$this,
				'sanitize',
			)
		);

		// Display.
		if ( 'display_options' === $this->rss_llama_active_tab ) {
			add_settings_section(
				'display',
				'',
				array( $this, 'tab_display_callback' ),
				'rss-llama',
			);
		}

		// Sidebar.
		if ( 'sidebar_options' === $this->rss_llama_active_tab ) {
			add_settings_section(
				'sidebar',
				'',
				array( $this, 'tab_sidebar_callback' ),
				'rss-llama',
			);
		}

		// Other.
		if ( 'other_options' === $this->rss_llama_active_tab ) {
			add_settings_section(
				'other',
				'',
				array( $this, 'tab_other_callback' ),
				'rss-llama',
			);
		}
	}

	/**
	 *  Random Feed in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function random_callback() {
		?>
		<input type = 'checkbox'
			id = 'rss_llama_random'
			name = 'rss-llama-settings-display[rss_llama_random]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->display_options['rss_llama_random'] ) ) {
				echo 'checked';}
			?>
			>
		<label for = 'rss_llama_random'>
			<?php esc_html_e( 'Display a random feed when first loading the page.', 'rss-llama' ); ?>
		</label>
		<?php
	}

	/**
	 *  Submit link form in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function refer_callback() {
		?>
			<input type = 'checkbox'
				id = 'rss_llama_refer'
				name = 'rss-llama-settings-display[rss_llama_refer]'
				style = 'display: inline;'
				<?php
				if ( isset( $this->rl->display_options['rss_llama_refer'] ) ) {
					echo 'checked';}
				?>
				>
			<label for = 'rss_llama_refer'>
				<?php esc_html_e( 'Display a field where others can refer links by email.', 'rss-llama' ); ?>
			</label>
			<?php
	}

	/**
	 *  Submit link email to in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function refer_email_callback() {
		?>
		<input type = 'email'
			id = 'rss_llama_refer_email'
			name = 'rss-llama-settings-sidebar[rss_llama_refer_email]'
			value = '<?php echo esc_attr( $this->rl->sidebar_options['rss_llama_refer_email'] ); ?>'
			required = 'true'>
			<?php
	}

	/**
	 *  Submit link text in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function refer_requesttext_callback() {
		?>
		<input type = 'text'
			id = 'rss_llama_refer_requesttext'
			name = 'rss-llama-settings-sidebar[rss_llama_refer_requesttext]'
			value = '<?php echo esc_attr( $this->rl->sidebar_options['rss_llama_refer_requesttext'] ); ?>'
			required = 'true'>
			<?php
	}

	/**
	 *  Submit link response text in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function refer_responsetext_callback() {
		?>
		<input type = 'text'
			id = 'rss_llama_refer_responsetext'
			name = 'rss-llama-settings-sidebar[rss_llama_refer_responsetext]'
			value = '<?php echo esc_attr( $this->rl->sidebar_options['rss_llama_refer_responsetext'] ); ?>'
			required = 'true'>
			<?php
	}

	/**
	 *  Reset options checkbox in manage options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function reset_callback() {
		?>
		<input type = 'checkbox'
			id = 'admin_reset'
			name = 'rss-llama-settings-other[admin_reset]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->other_options['admin_reset'] ) ) {
				echo 'checked';
			}
			?>
			>
			<label for = 'admin_reset'>
				<?php esc_html_e( 'Reset plugin options to their defaults when deactivating this plugin.', 'rss-llama' ); ?>
			</label>
		<?php
	}

	/**
	 * Admin form sanitization.
	 *
	 * @since 0.1
	 * @access public
	 *
	 * @param array $input - Option values we want to update.
	 *
	 * return array $new_input - Sanitized options array.
	 */
	public function sanitize( $input ) {
		if ( ! $input ) {
			return;
		}

		// Initialize the new array that will hold the sanitize values.
		$new_input = array();

		// Loop through the input and sanitize each of the values.
		foreach ( $input as $key => $val ) {
			$new_input[ $key ] = ( isset( $input[ $key ] ) ) ?
			sanitize_text_field( $val ) : '';
		}

		return $new_input;
	}

	/**
	 *  Scroll to Top Links in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function scrolltop_callback() {
		?>
		<input type = 'checkbox'
			id = 'rss_llama_scrolltop'
			name = 'rss-llama-settings-display[rss_llama_scrolltop]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->display_options['rss_llama_scrolltop'] ) ) {
				echo 'checked';}
			?>
			>
		<label for = 'rss_llama_scrolltop'>
			<?php esc_html_e( 'Scroll to the top when clicking a link.', 'rss-llama' ); ?>
		</label>
		<?php
	}

	/**
	 *  Display tab.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function tab_display_callback() {
		add_settings_field(
			'rss_llama_top',
			esc_html__( 'Top of Page', 'rss-llama' ) .
			' (&#9757;) ' .
			esc_html__( 'Icon', 'rss-llama' ),
			array( $this, 'top_callback' ),
			'rss-llama',
			'display'
		);

		add_settings_field(
			'rss_llama_nextprev',
			esc_html__( 'Next', 'rss-llama' ) . ' (&#10226;) ' .
			esc_html__( 'and Previous', 'rss-llama' ) . ' (&#8635;) ' .
			esc_html__( 'Icons', 'rss-llama' ),
			array( $this, 'nextprev_callback' ),
			'rss-llama',
			'display'
		);

		add_settings_field(
			'rss_llama_collapse',
			esc_html__( 'Toggle Sidebar', 'rss-llama' ) .
			' (&#8644;) ' .
			esc_html__( 'Icon', 'rss-llama' ),
			array( $this, 'collapse_callback' ),
			'rss-llama',
			'display'
		);

		add_settings_field(
			'rss_llama_buttoncat',
			esc_html__( 'Category Buttons', 'rss-llama' ),
			array( $this, 'buttoncat_callback' ),
			'rss-llama',
			'display'
		);

		add_settings_field(
			'rss_llama_buttoncount',
			esc_html__( 'Category Buttons Count', 'rss-llama' ),
			array( $this, 'buttoncount_callback' ),
			'rss-llama',
			'display'
		);

		add_settings_field(
			'rss_llama_refer',
			esc_html__( 'Submit Link Form', 'rss-llama' ),
			array( $this, 'refer_callback' ),
			'rss-llama',
			'display'
		);

		add_settings_field(
			'rss_llama_scrolltop',
			esc_html__( 'Links Scroll to Top', 'rss-llama' ),
			array( $this, 'scrolltop_callback' ),
			'rss-llama',
			'display'
		);

		add_settings_field(
			'rss_llama_excerpt',
			esc_html__( 'Summary Excerpt', 'rss-llama' ),
			array( $this, 'excerpt_callback' ),
			'rss-llama',
			'display'
		);

		add_settings_field(
			'rss_llama_random',
			esc_html__( 'Random Feed', 'rss-llama' ),
			array( $this, 'random_callback' ),
			'rss-llama',
			'display'
		);

		add_settings_field(
			'rss_llama_filterwords',
			esc_html__( 'Summary Filter', 'rss-llama' ),
			array( $this, 'filterwords_callback' ),
			'rss-llama',
			'display'
		);
	}

	/**
	 *  Sidebar tab.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function tab_sidebar_callback() {

		// Sidebar options settings fields.
		if ( 'sidebar_options' === $this->rss_llama_active_tab ) {
			add_settings_field(
				'rss_llama_sidebarpos',
				esc_html__( 'Sidebar Position', 'rss-llama' ),
				array( $this, 'sidebarpos_callback' ),
				'rss-llama',
				'sidebar'
			);

			add_settings_field(
				'rss_llama_colordark',
				esc_html__( 'Background Color', 'rss-llama' ),
				array( $this, 'colordark_callback' ),
				'rss-llama',
				'sidebar'
			);

			add_settings_field(
				'rss_llama_colorlight',
				esc_html__( 'Foreground Color', 'rss-llama' ),
				array( $this, 'colorlight_callback' ),
				'rss-llama',
				'sidebar'
			);

			add_settings_field(
				'rss_llama_refer_email',
				esc_html__( 'Submit Link Email', 'rss-llama' ),
				array( $this, 'refer_email_callback' ),
				'rss-llama',
				'sidebar'
			);

			add_settings_field(
				'rss_llama_refer_requesttext',
				esc_html__( 'Submit Link Text' ),
				array( $this, 'refer_requesttext_callback' ),
				'rss-llama',
				'sidebar'
			);

			add_settings_field(
				'rss_llama_refer_responsetext',
				esc_html__( 'Submit Link Sent Text', 'rss-llama' ),
				array( $this, 'refer_responsetext_callback' ),
				'rss-llama',
				'sidebar'
			);
		}
	}

	/**
	 *  Other tab.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function tab_other_callback() {

		// Other options settings fields.
		if ( 'other_options' === $this->rss_llama_active_tab ) {

			// Default settings.
			add_settings_field(
				'admin_reset',
				esc_html__( 'Reset When Deactivating', 'rss-llama' ),
				array( $this, 'reset_callback' ),
				'rss-llama',
				'other'
			);

			// CSV delimiter.
			add_settings_field(
				'export_delimiter',
				esc_html__( 'CSV Delimiter', 'rss-llama' ),
				array( $this, 'export_delimiter_callback' ),
				'rss-llama',
				'other'
			);

			// Display feed errors.
			add_settings_field(
				'display_errors',
				esc_html__( 'Display Errors and Filters', 'rss-llama' ),
				array( $this, 'display_errors_callback' ),
				'rss-llama',
				'other'
			);

			// Include categories on export.
			add_settings_field(
				'rss_llama_export_categories',
				esc_html__( 'Export Categories', 'rss-llama' ),
				array( $this, 'export_categories_callback' ),
				'rss-llama',
				'other'
			);
		}
	}

	/**
	 *  Sidebar position in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function sidebarpos_callback() {
		if ( 'Right' === $this->rl->sidebar_options['rss_llama_sidebarpos'] ) {
			?>
			<select name = 'rss-llama-settings-sidebar[rss_llama_sidebarpos]'
			id = 'rss_llama_sidebarpos'>
				<option value = 'Right' selected>
					<?php esc_html_e( 'Right', 'rss-llama' ); ?>
				</option>
				<option value = 'Left'>
					<?php esc_html_e( 'Left', 'rss-llama' ); ?>
				</option>
			</select>
			<?php
		} else {
			?>
			<select name = 'rss-llama-settings-sidebar[rss_llama_sidebarpos]'
				id = 'rss_llama_sidebarpos'>
				<option value = 'Right'>
					<?php esc_html_e( 'Right', 'rss-llama' ); ?>
				</option>
				<option value = 'Left' selected>
					<?php esc_html_e( 'Left', 'rss-llama' ); ?>
				</option>
			</select>
			<?php
		}
	}

	/**
	 *  Top of Page icon in admin options.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function top_callback() {
		?>
		<input type = 'checkbox'
			id = 'rss_llama_top'
			name = 'rss-llama-settings-display[rss_llama_top]'
			style = 'display: inline;'
			<?php
			if ( isset( $this->rl->display_options['rss_llama_top'] ) ) {
				echo 'checked';}
			?>
			>
		<label for = 'rss_llama_top'>
			<?php esc_html_e( 'Display top of the page link icon.', 'rss-llama' ); ?>
		</label>
		<?php
	}
}
