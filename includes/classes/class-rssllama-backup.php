<?php
/**
 * Rss Llama Backup Class
 *
 * Description. Import/Export rss from the database.
 *
 * @Link        http://wordpress.org/plugins/rss-llama/
 * @package     rss-llama
 * @since       2.0.0
 * License:     CopyHeart
 * License URI: https://oooorgle.com/copyheart
 */

namespace Rss_Llama;

/**
 * Class RssLlama_Backup.
 */
class RssLlama_Backup {

	/**
	 * CSV Delimiter.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var string
	 */
	public $separator;

	/**
	 * Filename prefix.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var string
	 */
	public $filename;

	/**
	 * Include categories in export.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @var bool
	 */
	public $export_cats;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $sep     - csv delimiter.
	 * @param bool   $expcats - Include categories in exports.
	 */
	public function __construct( $sep, $expcats ) {
		$this->separator   = $sep;
		$this->filename    = 'rss_llama';
		$this->export_cats = $expcats;
	}

	/**
	 * Allowed html lists.
	 *
	 * @since 3.0.0
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
	}

	/**
	 * Download the .csv export file.
	 * Prompt browser to save file.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function download_csv() {
		$date_now = gmdate( 'd-m-Y_His' );
		$csv_file = $this->generate_csv();
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false ); // Set browser to download file.
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $this->filename . '_' . $date_now . '.csv";' );
		header( 'Content-Transfer-Encoding: binary' );
		echo wp_kses_post( $csv_file );
		die();
	}

	/**
	 * Get data for .csv
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string $csv_output - rss data with columns.
	 */
	public function generate_csv() {
		global $wpdb;
		$csv_output = '';

		$values = $wpdb->get_results( // phpcs:ignore
			'SHOW COLUMNS FROM ' .
			$wpdb->prefix .
			'links'
		);

		// Create column headers.
		if ( count( $values ) > 0 ) {

			// Remove rss_id from header columns.
			unset( $values[0] );

			foreach ( $values as $row ) {
				$csv_output = $csv_output . $row->Field . $this->separator; // phpcs:ignore
			}

			// Add in link_category header column.
			$csv_output .= 'link_category' . $this->separator;

			// Trim trailing separator.
			$csv_output = substr( $csv_output, 0, -1 );
		}

		// Add new line for rss.
		$csv_output .= "\n";

		$values = $wpdb->get_results( // phpcs:ignore
			'SELECT * FROM '
			. $wpdb->prefix .
			'links',
			ARRAY_A
		);

		foreach ( $values as $value ) {

			// Get link categories from its ID.
			$link_term_ids = $this->get_link_categories( $value['link_id'] );

			// Remove the rss_id.
			unset( $value['link_id'] );

			// Index the array numerically.
			$csv_fields = array_values( $value );

			// String with field separator.
			$csv_string = stripslashes(
				implode(
					$this->separator,
					$csv_fields
				)
			);

			$csv_output .= $csv_string;
			$csv_output .= $this->separator . $link_term_ids;
			$csv_output .= "\n";
		}

		return $csv_output;
	}

	/**
	 * Generates the import query string for importing from a json or csv file.
	 *
	 * @param string $nonce - Nonce.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function generate_import( $nonce ) {
		global $wpdb;
		$count        = 1;
		$errors       = 0;
		$allowed_html = $this->allowed_html( 'style' );

		if ( wp_verify_nonce( $nonce, 'rss_llama_import_nonce' ) ) {

			// Check that we have a table to write to.
			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "links'" ) === $wpdb->prefix . 'links' ) { // phpcs:ignore

				// Accepted file extensions.
				$exts = array(
					'json',
					'JSON',
					'CSV',
					'csv',
				);

				// Sanitize, unslash filename.
				$filename = isset( $_FILES['rss-llama-file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['rss-llama-file']['name'] ) ) : '';

				// Sanitize, unslash temp filename.
				$filetmp = isset( $_FILES['rss-llama-file']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['rss-llama-file']['tmp_name'] ) ) : '';

				// Files extension.
				$fileext = pathinfo( $filename, PATHINFO_EXTENSION );

				// File error.
				$fileerror = isset( $_FILES['rss-llama-file']['error'] ) ? absint( wp_unslash( $_FILES['rss-llama-file']['error'] ) ) : '';

				// Upload file or error.
				if ( $fileerror ) {

					// Translate error code.
					$thismsg = $this->error_messages( $fileerror );
					return wp_kses_post( $thismsg );
				}

				// Upload success.
				if ( UPLOAD_ERR_OK === $fileerror && is_uploaded_file( $filetmp ) ) {

					// Validate file extension.
					if ( ! in_array( $fileext, $exts, true ) ) {
						return 'The file type ' . esc_html( $fileext ) . ' is not supported.';
					}

					$json_data = file_get_contents( $filetmp ); // phpcs:ignore

					// Validate has data.
					if ( ! $json_data ) {
						return esc_html__( 'Unable to import because the file is empty.' );
					}

					// If json file. Decode objects into array.
					if ( 'json' === $fileext || 'JSON' === $fileext ) {
						$rss_json = json_decode( $json_data, true );

						// Validate the decode.
						if ( is_null( $rss_json ) ) {
							return wp_kses_post( $this->error_messages() );
						} else {
							foreach ( $rss_json as $rss => $data ) {

								// Check element count of each link.
								if ( count( $data ) !== 14 ) {
									$errors++;
									$result = esc_html__(
										'There was an error. Verification returned on record ',
										'rss-llama'
									) . absint(
										$count
									) . '.';

									return $result;
								}
								++$count;
							}

							// Sanitize each and insert unless error.
							if ( ! $errors ) {
								foreach ( $rss_json as $rss => $data ) {

									// Filter the rss for allowed html tags.
									if ( isset( $data['link_rss'] ) ) {
										$data['link_rss'] = wp_check_invalid_utf8( wp_unslash( $data['link_rss'] ) );
										$data['link_rss'] = wp_kses( trim( $data['link_rss'] ), $allowed_html );
									} else {

										// if no rss.
										$data['link_rss'] = '';
									}

									// Add to link array for insert.
									$data['link_url']         = htmlspecialchars( $data['link_url'] );
									$data['link_name']        = htmlspecialchars( $data['link_name'] );
									$data['link_image']       = htmlspecialchars( $data['link_image'] );
									$data['link_target']      = htmlspecialchars( $data['link_target'] );
									$data['link_description'] = $data['link_description'];
									$data['link_visible']     = htmlspecialchars( $data['link_visible'] );
									$data['link_owner']       = htmlspecialchars( $data['link_owner'] );
									$data['link_rating']      = htmlspecialchars( $data['link_rating'] );
									$data['link_updated']     = $data['link_updated'];
									$data['link_rel']         = htmlspecialchars( $data['link_rel'] );
									$data['link_notes']       = htmlspecialchars( $data['link_notes'] );
									$data['link_rss']         = htmlspecialchars( $data['link_rss'] );
									$data['link_category']    = $data['link_category'];
									$rss_json[ $rss ]         = $data;
								}

								// Import JSON data.
								$result = $this->rss_import( $rss_json );
							}
						}

						// If CSV file.
					} elseif ( 'csv' === $fileext || 'CSV' === $fileext ) {
						$header  = null;
						$rss_csv = array();
						$handle  = fopen( $filetmp, 'r' ); // phpcs:ignore

						if ( false !== $handle ) {
							while ( ( $row = fgetcsv( $handle, 2000, $this->separator ) ) !== false ) { // phpcs:ignore

								// Check element count of each link.
								if ( count( $row ) !== 13 ) {

									fclose( $handle ); // phpcs:ignore
									return esc_html__(
										'There was an error. Verification returned on line ',
										'rss-llama'
									) . absint(
										$count
									) . esc_html__(
										'. Be sure the csv delimiter in the options tab is set to match your file. Improper file structure such as incorrect columns and fields can cause the import to fail as well.',
										'rss-llama'
									);
								} else {

									// Combine our header and data. Assign first row data to header columns [header][row].
									if ( ! $header ) {
										$header = $row;
										for ( $i = 0; $i <= 12; $i++ ) {

											// CSV in utf8 might have BOM characters in the headers. Remove BOM characters.
											$header[ $i ] = preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $header[ $i ] );

											// Sanitize header.
											$header[ $i ] = sanitize_text_field( $header[ $i ] );
										}
									} else {

										for ( $i = 0; $i <= 12; $i++ ) {
											$allowed_html = $this->allowed_html( 'style' );

											// Filter the row for allowed html tags.
											if ( isset( $row[ $i ] ) ) {
												$row[ $i ] = wp_check_invalid_utf8( wp_unslash( $row[ $i ] ) );
												$row[ $i ] = wp_kses( trim( $row[ $i ] ), $allowed_html );
											} else {

												// if no data in row.
												$row[ $i ] = '';
											}
										}

										$rss_csv[] = array_combine( $header, $row );
									}
								}
								++$count;
							}
							fclose( $handle ); // phpcs:ignore

							// End Import CSV data.
							$result = $this->rss_import( $rss_csv );
						}
					}

					if ( ! $result ) {
						return esc_html__( 'Import failed. Please try again.', 'rss-llama' );
					} elseif ( 0 === $result ) {
						return esc_html__( 'No Links imported', 'rss-llama' );
					} else {

						// Translators: Number of rss imported.
						$importcount = esc_attr( _n( '%d link imported', '%d links imported', $result, 'rss-llama' ) );
						return sprintf( $importcount, $result );
					}

					return;
				}
			} else {
				return 'You do not have a links table.';
			}
		} else {
			return esc_html__( 'Security token mismatch, please reload the page and try again.', 'quotes-llama' );
		}
	}

	/**
	 * Get data for .json and create the file.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function generate_json() {
		global $wpdb;
		$date_now = gmdate( 'd-m-Y_His' );

		$rss = $wpdb->get_results( // phpcs:ignore
			'SELECT * FROM ' .
			$wpdb->prefix .
			'links',
			ARRAY_A
		);

		foreach ( $rss as $rsss => $data ) {

			// Get link categories from its ID.
			$link_term_ids = $this->get_link_categories( $data['link_id'] );

			unset( $data['link_id'] );
			$data['link_id']          = htmlspecialchars( $data['link_id'] );
			$data['link_url']         = htmlspecialchars( $data['link_url'] );
			$data['link_name']        = htmlspecialchars( $data['link_name'] );
			$data['link_image']       = htmlspecialchars( $data['link_image'] );
			$data['link_target']      = htmlspecialchars( $data['link_target'] );
			$data['link_description'] = $data['link_description'];
			$data['link_visible']     = htmlspecialchars( $data['link_visible'] );
			$data['link_owner']       = htmlspecialchars( $data['link_owner'] );
			$data['link_rating']      = htmlspecialchars( $data['link_rating'] );
			$data['link_updated']     = $data['link_updated'];
			$data['link_rel']         = htmlspecialchars( $data['link_rel'] );
			$data['link_notes']       = htmlspecialchars( $data['link_notes'] );
			$data['link_rss']         = htmlspecialchars( $data['link_rss'] );
			$data['link_category']    = $link_term_ids;
			$rss[ $rsss ]             = $data;
		}

		$json_output = wp_json_encode( $rss, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		header( 'Content-Type: text/json' );
		header( 'Content-Disposition: attachment; filename="' . $this->filename . '_' . $date_now . '.json";' );
		echo wp_kses_post( $json_output );
		die();
	}

	/**
	 * Get link categories from its term_id. Separates using dash (-).
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id - Id of link.
	 *
	 * return array $link_term_ids - Category term_ids the link belongs to.
	 */
	public function get_link_categories( $id ) {
		$link_term_ids = '';

		// If option allows.
		if ( $this->export_cats ) {
			$link_categories = wp_get_link_cats( $id );
			$link_term_ids   = implode( '-', $link_categories );
		}

		return $link_term_ids;
	}

	/**
	 * Import rss from array provided by either .csv or .json formats.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $rss_data - Array of rss data.
	 *
	 * @return mixed - int results count, or error string.
	 */
	public function rss_import( $rss_data = array() ) {
		global $wpdb;
		$link_cat_ids = 0;

		// Check that we have import data.
		if ( ! $rss_data ) {
			return 0;
		}

		foreach ( $rss_data as $rss ) {
			$replace = str_replace( '-', ',', $rss['link_category'] );
			$cats    = explode( ',', $replace );

			$linkdata = array(
				'link_url'         => $rss['link_url'],
				'link_name'        => $rss['link_name'],
				'link_image'       => $rss['link_image'],
				'link_target'      => $rss['link_target'],
				'link_description' => $rss['link_description'],
				'link_visible'     => $rss['link_visible'],
				'link_owner'       => $rss['link_owner'],
				'link_rating'      => $rss['link_rating'],
				'link_updated'     => $rss['link_updated'],
				'link_rel'         => $rss['link_rel'],
				'link_notes'       => $rss['link_notes'],
				'link_rss'         => $rss['link_rss'],
				'link_category'    => $cats,
			);

			// Insert link using wp_insert_link and return count.
			$link_cat_id = wp_insert_link( $linkdata, true );
			if ( $link_cat_id ) {
				$link_cat_ids++;
			}
		}

		return esc_attr( $link_cat_ids );
	}

	/**
	 * Handle error messages for file uploads and JSON decodes.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $error_code - The error code encountered.
	 *
	 * @return string - Description of the error.
	 */
	public function error_messages( $error_code = 'none' ) {

		// If no error code provided it is a JSON error.
		if ( 'none' === $error_code ) {
			if ( ! function_exists( 'json_last_error_msg' ) ) {
				/**
				 * Handle error messages for validating JSON file data.
				 *
				 * @since 1.0.0
				 * @access public
				 *
				 * @return string - Description of the error.
				 */
				function json_last_error_msg() {
					static $e_r_r_o_r_s = array(
						JSON_ERROR_NONE           => 'No error.',
						JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded in the JSON file.',
						JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON).',
						JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded JSON file.',
						JSON_ERROR_SYNTAX         => 'Import failed. Syntax error in the JSON file.',
						JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded JSON file.',
					);
					$error              = json_last_error();
					return isset( $e_r_r_o_r_s[ $error ] ) ? $e_r_r_o_r_s[ $error ] : 'Unknown error.';
				}
			}

			return json_last_error_msg();
		}

		switch ( $error_code ) {
			case UPLOAD_ERR_INI_SIZE:
				$message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = 'The uploaded file was only partially uploaded..';
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = 'No file was uploaded.';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = 'Missing a temporary folder.';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = 'Failed to write file to disk.';
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = 'File upload stopped by extension.';
				break;
			default:
				$message = 'File upload stopped by an unrecognized error.';
				break;
		}

		return $message;
	}
}
