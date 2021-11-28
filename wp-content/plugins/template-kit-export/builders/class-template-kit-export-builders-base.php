<?php
/**
 * Template exporter base file.
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/builders
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Base class for any supported page builders.
 *
 * This class defines all features required for page bulders.
 *
 * @since      1.0.0
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/includes/builders
 * @author     Envato <->
 */
abstract class Template_Kit_Export_Builders_Base {

	/**
	 * This is an array of local temporary files that we need to add to the zip archive.
	 * Add to this array using add_file_to_zip(), and build the zip with build_zip_file().
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $files_to_zip The array that will contain the files to be zipped for exporting.
	 */
	private $files_to_zip = array();

	/**
	 * Get the name of our kit, comes from options.
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	public function get_kit_name() {
		return Template_Kit_Export_Options::get( 'kit_name' );
	}

	/**
	 * Get the version number of our kit, comes from options.
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	public function get_kit_version() {
		return Template_Kit_Export_Options::get( 'kit_version', '1.0.0' );
	}

	/**
	 * Get the type of page builder.
	 *
	 * @return   string.
	 * @since    1.0.0
	 */
	public function get_page_builder_type() {
		return 'todo';
	}

	/**
	 * Get a list of required plugins for the manifest.json file
	 *
	 * @since    1.0.0
	 * @return   array The plugin details (slug, name and version).
	 */
	public function get_required_plugins_for_manifest() {
		$required_plugins     = json_decode( Template_Kit_Export_Options::get( 'required_plugins', '[]' ), true );
		$required_plugin_data = array();
		foreach ( $required_plugins as $required_plugin ) {
			unset( $required_plugin['required'] );
			$required_plugin_data[] = $required_plugin;
		}
		return $required_plugin_data;
	}

	/**
	 * Build the zip to export.
	 *
	 * Everything starts here, building a zip for a template kit.
	 * This is called from our admin action when the user wishes to export their template kit as a zip file.
	 *
	 * @return   mixed
	 * @throws   Exception An error warning based on the error.
	 * @since    1.0.0
	 */
	public function build_zip() {

		if ( ! class_exists( '\ZipArchive' ) ) {
			throw new Exception( 'PHP is missing the ZipArchive extension, please enable through hosting provider' );
		}

		// Reset our list of files that we want to add to the zip:.
		$this->files_to_zip = array();

		// Grab the full template kit name (e.g. `Awesome Kit`).
		$template_kit_name = $this->get_kit_name();
		// Grab the author supplied kit version number (e.g. `1.0.0`)
		$template_kit_version = $this->get_kit_version();

		if ( ! strlen( trim( $template_kit_name ) ) ) {
			throw new Exception( 'Missing template kit name' );
		}

		// Start building up our manifest data. This data will be written to `manifest.json` in the zip file.
		$manifest_data = array(
			'manifest_version' => TEMPLATE_KIT_EXPORT_VERSION,
			'title'            => $template_kit_name,
			'page_builder'     => $this->get_page_builder_type(),
			'kit_version'      => $template_kit_version,
			'templates'        => array(),
			'required_plugins' => $this->get_required_plugins_for_manifest(),
		);

		// An array to keep track of all the temporary files we create, so we can clean up after ourselves at the end.
		$temporary_files_to_cleanup = array();

		// Get a list of all templates in this kit:.
		$templates = $this->get_all_templates_in_kit();
		// Start looping over each template in our kit.
		foreach ( $templates as $template ) {

			// We skip any entries that the user has chosen not to include in the zip file
			if ( ! $template['include_in_zip'] ) {
				continue;
			}

			// This gets the raw JSON data we're going to write to the zip file:.
			$exported_template_data = $this->get_template_export_data( $template );
			// Grab the filename/url for the template screenhot:.
			$exported_template_screenshot = $this->get_template_screenshot( $template['id'] );

			// Write our exported template data array to a local temporary file so our ZIP builder has a file to work from:.
			$temporary_json_data_file = wp_tempnam( $template['zip_filename'] );
			// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			file_put_contents( $temporary_json_data_file, wp_json_encode( $exported_template_data, JSON_PRETTY_PRINT ) );
			// phpcs:enable
			$temporary_files_to_cleanup[] = $temporary_json_data_file;

			// Add our JSON data to the zip file:.
			$this->add_file_to_zip( $template['zip_filename'], $temporary_json_data_file );
			// Add our screenshot to the zip file.
			$this->add_file_to_zip( $exported_template_screenshot['zip_filename'], $exported_template_screenshot['local_filename'] );

			// Get a list of section keys to check if the template is a section or page.
			$template_types          = $this->get_template_meta_fields();
			$template_types_sections = $template_types[0]['options']['section']['options'];

			// Set some default values.
			$category        = 'page';
			$section_or_page = 'section';
			if ( ! empty( $template['metadata']['template_type'] ) ) {
				if ( strpos( $template['metadata']['template_type'], 'page' ) !== false ) {
					$section_or_page = 'page';
				}
				if ( array_key_exists( $template['metadata']['template_type'], $template_types_sections ) ) {
					$category = 'section';
				}
			}

			// Add template and screenshot data to manifest file:.
			$manifest_data['templates'][] = $this->get_template_manifest_data(
				$template,
				array(
					'name'        => $template['name'],
					'screenshot'  => $exported_template_screenshot['zip_filename'],
					'source'      => $template['zip_filename'],
					'preview_url' => $template['preview_url'],
					'type'        => ! empty( $template['metadata']['elementor_library_type'] ) ? $template['metadata']['elementor_library_type'] : $section_or_page,
					'category'    => $category,
					'metadata'    => $template['metadata'],
				)
			);
		}

		// Add image metadata to the manifest.json file
		$manifest_data['images'] = $this->get_image_meta_data_for_manifest( $templates );

		// Create a temporary file to store our manifest.json data, and add it to the temp cleanup array:.
		$temporary_manifest_file_name = wp_tempnam( 'manifest.json' );
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		file_put_contents( $temporary_manifest_file_name, wp_json_encode( $manifest_data, JSON_PRETTY_PRINT ) );
		// phpcs:enable
		$temporary_files_to_cleanup[] = $temporary_manifest_file_name;

		// Add this manifest.json file to our zip:.
		$this->add_file_to_zip( 'manifest.json', $temporary_manifest_file_name );

		// Let the builder add any additional files, e.g. license files or readme files.
		$this->add_additional_files_to_zip();

		// Create a temporary file for our zip, and add this to the temporary cleanup array:.
		$zip_archive_filename         = 'template-kit-' . $this->sanitise_filename( $template_kit_name . '-' . $template_kit_version ) . '.zip';
		$temporary_zip_file_name      = wp_tempnam( $zip_archive_filename );
		$temporary_files_to_cleanup[] = $temporary_zip_file_name;

		// Start building the actual zip file:.
		$zip_archive = new ZipArchive();
		$zip_archive->open( $temporary_zip_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE );
		foreach ( $this->files_to_zip as $file_to_zip ) {
			$zip_archive->addFile( $file_to_zip['tmp_file'], $file_to_zip['name'] );
		}
		$zip_archive->close();

		// We get the raw zip data so we can return it from this function call.
		// This also lets this method clean up the temporary file after itself.

		// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$zip_file_data = file_get_contents( $temporary_zip_file_name );
		// phpcs:enable

		// Clean up temporary files created above:.
		foreach ( $temporary_files_to_cleanup as $temporary_file_to_cleanup ) {
			unlink( $temporary_file_to_cleanup );
		}

		// Return the desired filename (sent to browser) and zip data.
		return array(
			'filename' => $zip_archive_filename,
			'zip_data' => $zip_file_data,
		);
	}

	/**
	 * Helper function to sanitise a string as a safe filename.
	 *
	 * @param string $name The name of the filename.
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	public function sanitise_filename( $name ) {
		return preg_replace( '#[^a-z0-9.]+#', '-', strtolower( $name ) );
	}

	/**
	 * Allows page builders to override the template manifest data included in the zip
	 *
	 * @param $template
	 * @param $manifest_data
	 *
	 * @return   array
	 * @since    1.0.0
	 */
	public function get_template_manifest_data( $template, $manifest_data ) {
		return $manifest_data;
	}

	/**
	 * Add additional files to zip.
	 *
	 * Allows us to hook in and add additional files (e.g. license/readme) to the zip during build process.
	 *
	 * @since    1.0.0
	 */
	public function add_additional_files_to_zip() {
		// Allows the builder to add additional files to the zip if needed.
		// Use $this->>add_file_to_zip() .
	}

	/**
	 * Get all templates in our CPT.
	 *
	 * Returns an array of all templates in our CPT. Used in the frontend and
	 * also in the zip building process.
	 *
	 * @since    1.0.0
	 */
	public function get_all_templates_in_kit( $only_include_final_zip_templates = false ) {
		// First we get any Templates that are created in our custom CPT area:
		$posts     = get_posts(
			array(
				'post_type'   => Template_Kit_Export_CPT::CPT_SLUG,
				'numberposts' => - 1,
			)
		);
		$templates = array();
		foreach ( $posts as $post ) {
			$template_meta_data = get_post_meta( $post->ID, 'envato_tk_post_meta', true );
			if ( ! is_array( $template_meta_data ) ) {
				$template_meta_data = array();
			}
			if ( $only_include_final_zip_templates && empty( $template_meta_data['include_in_zip'] ) ) {
				continue;
			}
			$templates[] = array(
				'id'             => $post->ID,
				'name'           => $post->post_title,
				'zip_filename'   => 'templates/' . $this->sanitise_filename( $post->post_title ) . '.json',
				'include_in_zip' => ! empty( $template_meta_data['include_in_zip'] ),
				'metadata'       => $template_meta_data,
				'preview_url'    => get_permalink( $post->ID ),
				'order'          => $post->menu_order,
			);
		}

		uasort(
			$templates,
			function( $a, $b ) {
				return $a['order'] > $b['order'];
			}
		);

		return $templates;
	}

	/**
	 * Get the actual page builder data that we want to save in the zip.
	 *
	 * Returns an array of all templates in our CPT. Used in the frontend and
	 * also in the zip building process.
	 *
	 * @param array $template The array of template data that is to be exported.
	 *
	 * @return   array
	 * @throws   Exception Throws a short error message.
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_template_export_data( $template ) {
		throw new Exception( 'Export not defined for template ' . $template['id'] );
	}

	/**
	 * Get screenshot.
	 *
	 * Get screenshot information for a particular template id. This just reaches
	 * into the WordPress post attachment.
	 *
	 * @param int $template_id The id of the template that is being exported.
	 *
	 * @return array
	 * @throws   Exception Throws a short error message.
	 * @since    1.0.0
	 */
	public function get_template_screenshot( $template_id ) {
		$attachment_id = get_post_thumbnail_id( $template_id );
		if ( ! $attachment_id ) {
			throw new Exception( 'No attachment for template ' . $template_id );
		}
		// Get the file name of the feature image data. We try to get the "tk_preview" sized thumbnail first:
		$attached_info = image_get_intermediate_size( $attachment_id, 'tk_preview' );
		if ( $attached_info && ! empty( $attached_info['path'] ) ) {
			// We have successfully found a "tk_preview" sized image for this attachment.
			// Build out the full file path for this attachment so we can read its exif data and zip it up.
			$upload_directory = wp_upload_dir();
			// Do a little string append to get the file path of the feature image.
			$attachment_filename = trailingslashit( $upload_directory['basedir'] ) . $attached_info['path'];
		} else {
			// We couldn't find tk_preview size, author likely uploaded exactly 600px wide thumb (as per guidelines)
			// Get the attached file path. This is the full image file path.
			$attachment_filename = get_attached_file( $attachment_id );
		}
		if ( ! $attachment_filename ) {
			throw new Exception( 'Could not find file for attachment ' . $attachment_id );
		}

		$screenshot_url  = get_the_post_thumbnail_url( $template_id );
		$screenshot_type = exif_imagetype( $attachment_filename );
		if ( IMAGETYPE_PNG === $screenshot_type ) {
			$screenshot_extension = 'png';
		} elseif ( IMAGETYPE_JPEG === $screenshot_type ) {
			$screenshot_extension = 'jpg';
		} else {
			throw new Exception( 'Unknown attachment type for ' . $attachment_filename );
		}
		$post_data = get_post( $template_id );

		return array(
			'local_filename' => $attachment_filename,
			'url'            => $screenshot_url,
			'zip_filename'   => 'screenshots/' . $this->sanitise_filename( $post_data->post_title ) . '.' . $screenshot_extension,
		);
	}

	/**
	 * Save template options to post meta
	 *
	 * @param int     $template_id Post id generated by WP.
	 * @param mixed[] $template_options Options to be saved as post meta.
	 *
	 * @since    1.0.0
	 */
	public function save_template_options( $template_id, $template_options ) {
		// Loop over all possible meta fields and pull those values into the post meta array
		$template_fields = $this->get_template_meta_fields();
		$save_array      = get_post_meta( $template_id, 'envato_tk_post_meta', true );
		if ( ! is_array( $save_array ) ) {
			$save_array = array();
		}
		foreach ( $template_fields as $template_field ) {
			if ( isset( $template_options[ $template_field['name'] ] ) ) {
				$save_array[ $template_field['name'] ] = $template_options[ $template_field['name'] ];
			} else {
				$save_array[ $template_field['name'] ] = null;
			}
		}

		update_post_meta(
			$template_id,
			'envato_tk_post_meta',
			$save_array
		);

		if ( isset( $template_options['thumb_id'] ) ) {
			update_post_meta(
				$template_id,
				'_thumbnail_id',
				$template_options['thumb_id']
			);

			// This forces the creation of the tk_preview if it failed to be created by WP
			// due to the exact size the user uploaded. It then updated template meta.
			$get_thumb_id     = get_post_thumbnail_id( $template_id );
			$check_tk_preview = image_get_intermediate_size( $get_thumb_id, 'tk_preview' );
			if ( ! $check_tk_preview ) {
				$generated_tk_preview                            = image_make_intermediate_size( get_attached_file( $get_thumb_id ), TEMPLATE_KIT_EXPORT_THUMBNAIL_WIDTH, 0, true );
				$template_attachment_meta                        = wp_get_attachment_metadata( $get_thumb_id );
				$template_attachment_meta['sizes']['tk_preview'] = $generated_tk_preview;
				wp_update_attachment_metadata( $get_thumb_id, $template_attachment_meta );
			}
		}

		if ( isset( $template_options['name'] ) && isset( $template_options['position_id'] ) ) {
			$tk_post_update = array(
				'ID'         => $template_id,
				'post_title' => $template_options['name'],
				'menu_order' => $template_options['position_id'],
			);
			wp_update_post( $tk_post_update );
		}
	}

	/**
	 * Add file to zip.
	 *
	 * This lets us add a file to the pending zip array.
	 *
	 * @param string $filename The name of the file.
	 * @param string $temporary_filename The temporary name of the file.
	 *
	 * @since    1.0.0
	 */
	public function add_file_to_zip( $filename, $temporary_filename ) {
		$this->files_to_zip[] = array(
			'name'     => $filename,
			'tmp_file' => $temporary_filename,
		);
	}

	/**
	 * Finds all images
	 *
	 * Returns an array of all images used in this template kit, with associated metadata to help with kit review
	 *
	 * @return array list of images and metadata
	 * @since    1.0.0
	 */
	public function find_all_images() {
		return array();
	}

	/**
	 * Saves user image data against the template kit
	 *
	 * Called after the user saves data against an image during the export process.
	 *
	 * @param $image_id
	 * @param $user_data
	 *
	 * @since    1.0.0
	 */
	public function save_image_data( $image_id, $user_data ) {
		$image_src = wp_get_attachment_image_src( $image_id, 'tk_preview' );
		if ( $image_src && ! empty( $user_data ) && is_array( $user_data ) ) {
			// Ensure we're using a valid image id and have some data to save.
			update_post_meta( $image_id, 'tk_image_user_data', $user_data );
		}
	}

	/**
	 * This returns an array of fields that are used in Step 3 of the Export process,
	 * but also used in the media upload window. This allows us to share the same fields between both locations.
	 *
	 * @return array
	 */
	public function get_image_meta_fields() {
		return array(
			array(
				'name'    => 'image_source',
				'label'   => __( 'Image Source:', 'template-kit-export' ),
				'type'    => 'select',
				'options' => array(
					''                => '',
					'envato_elements' => __( 'Licensed From Envato Elements', 'template-kit-export' ),
					'self_created'    => __( 'Created Myself', 'template-kit-export' ),
					'cc0'             => __( 'CC0 or equivalent', 'template-kit-export' ),
					'unsure'          => __( 'Unsure (NOT allowed)', 'template-kit-export' ),
				),
			),
			array(
				'name'    => 'person_or_place',
				'label'   => __( 'Contains Person or Place?', 'template-kit-export' ),
				'type'    => 'select',
				'options' => array(
					''    => '',
					'yes' => __( 'Yes, image contains person or place', 'template-kit-export' ),
					'no'  => __( 'No', 'template-kit-export' ),
				),
			),
			array(
				'name'        => 'image_urls',
				'label'       => __( 'Source URLs', 'template-kit-export' ),
				'type'        => 'text',
				'placeholder' => '',
			),
		);
	}


	/**
	 * This returns an array of fields that are used in Step 2 of the Export process,
	 *
	 * @return array
	 */
	public function get_template_meta_fields() {
		return array(
			array(
				'name'  => 'include_in_zip',
				'label' => __( 'Include Template in Export ZIP', 'template-kit-export' ),
				'type'  => 'checkbox',
			),
		);
	}

	/**
	 * Returns an array of errors for presentation to the user during export process.
	 *
	 * @return array
	 */
	public function detect_any_errors_with_template_kit() {
		$errors = array();

		// See if any missing global settings
		$kit_errors       = array();
		$kit_name         = Template_Kit_Export_Options::get( 'kit_name' );
		$page_builder     = Template_Kit_Export_Options::get( 'page_builder' );
		$required_plugins = json_decode( Template_Kit_Export_Options::get( 'required_plugins', '[]' ), true );
		if ( strlen( $kit_name ) < 5 ) {
			$kit_errors[] = 'Please enter a Template Kit name longer than 5 characters';
		}
		if ( ! $page_builder ) {
			$kit_errors[] = 'Please choose a valid Page Builder for this Template Kit';
		}
		if ( ! $required_plugins ) {
			$kit_errors[] = 'Please choose required plugins for this Template Kit';
		}
		if ( $kit_errors ) {
			$errors['kit'] = $kit_errors;
		}

		// See if there's any missing required fields on the template kits:
		$template_kits               = $this->get_all_templates_in_kit();
		$included_template_kit_count = 0;
		$template_errors             = array();
		foreach ( $template_kits as $template_kit ) {
			if ( $template_kit['include_in_zip'] ) {
				$included_template_kit_count ++;
				// Check for missing screenshots, only for the Template Kit export type.
				if ( Template_Kit_Export_Options::get( 'export_type' ) !== TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) {
					try {
						$screenshot = $this->get_template_screenshot( $template_kit['id'] );
					} catch ( Exception $e ) {
						$template_errors[] = 'Please include a screenshot for: ' . $template_kit['name'];
					}
				}
			}
		}
		if ( $included_template_kit_count < 3 ) {
			$template_errors[] = 'Please include at least 3 templates in this Template Kit';
		}
		if ( $template_errors ) {
			$errors['templates'] = $template_errors;
		}

		// See if there's any missing
		$all_images   = $this->find_all_images();
		$image_errors = array();
		foreach ( $all_images as $image ) {
			if ( empty( $image['user_data'] ) || empty( $image['user_data']['image_source'] ) || empty( $image['user_data']['person_or_place'] ) ) {
				$image_errors[] = 'Please provide details for image: ' . $image['filename'];
			} elseif ( 'yes' === $image['user_data']['person_or_place'] && 'envato_elements' !== $image['user_data']['image_source'] ) {
				$image_errors[] = 'Sorry we only allow personally identifiable images from Envato Elements: ' . $image['filename'];
			} elseif ( 'unsure' === $image['user_data']['image_source'] ) {
				$image_errors[] = 'Unknown is not allowed. Please specify a valid image source for: ' . $image['filename'];
			} elseif ( ( 'envato_elements' === $image['user_data']['image_source'] || 'cc0' === $image['user_data']['image_source'] ) && empty( $image['user_data']['image_urls'] ) ) {
				$image_errors[] = 'Please enter the source image URL for ' . $image['filename'];
			}
			if ( ! $image['filesize'] ) {
				$image_errors[] = 'Sorry we cannot read the file: ' . $image['filename'];
			} elseif ( $image['filesize'] > 1000000 ) {
				// Most WordPress installs default to 1MB upload limit
				$image_errors[] = 'This source image is too large (' . number_format( $image['filesize'] / 1048576, 2 ) . ' MB' . '). Reduce it to less than 1MB: ' . $image['filename'];
			}
			if ( ! $image['filename'] ) {
				$image_errors[] = 'Sorry we cannot find an image filename.';
			}
		}
		if ( $image_errors ) {
			$errors['images'] = $image_errors;
		}

		return $errors;
	}

	/**
	 * Returns data about template kit images for the manifest.json file
	 *
	 * @param $templates
	 *
	 * @return array
	 */
	public function get_image_meta_data_for_manifest( $templates ) {

		// This gets a list of all the images included in the template kit. Same as step 3 of the wizard:
		$all_images = $this->find_all_images();
		// This gets all the additional meta fields (image source, license, comment, etc..) same as Setep 3 of the wizard:
		$image_fields = $this->get_image_meta_fields();
		// Initialize an array that we can add all our image metadata to, this gets returned for the manifest file
		$all_image_metadata = array();

		// Loop over all available images to start building out the metadata:
		foreach ( $all_images as $image ) {
			// Find out which templates this image is used on. Filter by only templates that are 'include_in_zip'
			// We do this so we can show the template name and the JSON zip file name for the template
			$image_templates = array();
			foreach ( $image['used_on_templates'] as $template_id => $template_name ) {
				foreach ( $templates as $template ) {
					if ( $template['include_in_zip'] && $template['id'] === $template_id ) {
						// We've found which image this template is used on. Include its path in the manifest file
						$image_templates[] = array(
							'source' => $template['zip_filename'],
							'name'   => $template['name'],
						);
					}
				}
			}
			if ( $image_templates ) {
				// If we've found some templates that this image is used on we continue adding data to the manifest array.
				// Here we include the image filename, a public thumbnail URL so the reviewers can view it quickly, and our array of templates:
				$image_metadata = array(
					'filename'      => $image['filename'],
					'thumbnail_url' => $image['thumbnail_url'],
					'templates'     => $image_templates,
					'filesize'      => $image['filesize'],
					'dimensions'    => $image['dimensions'],
				);
				// We also include any additional image metadata (such as license etc..) that is captured from the user:
				foreach ( $image_fields as $image_field ) {
					if ( isset( $image['user_data'][ $image_field['name'] ] ) ) {
						$image_metadata[ $image_field['name'] ] = $image['user_data'][ $image_field['name'] ];
					}
				}
				// Finally we append our image metadata onto the big array of metadata that will make its way down to the manifest.json file:
				$all_image_metadata[] = $image_metadata;
			}
		}

		return $all_image_metadata;
	}

	/**
	 * Check if there are any site health issues in the context of a page builder.
	 *
	 * @return array
	 */
	public function get_site_health() {
		$health = array(
			'errors'  => array(),
			'success' => array(),
		);
		return $health;
	}


	/**
	 * Generates some markup that can be copied onto the ThemeForest item page.
	 *
	 * @param $market string
	 *
	 * @return string
	 */
	public function generate_item_page_markup( $market ) {
		$all_images = $this->find_all_images();
		// extract any envato elements images from all our images
		$envato_elements_images = array_filter(
			$all_images,
			function( $image ) {
				return ! empty( $image['user_data'] ) && ! empty( $image['user_data']['image_source'] ) && ! empty( $image['user_data']['image_urls'] ) && $image['user_data']['image_source'] === 'envato_elements';
			}
		);
		if ( $envato_elements_images ) {
			$output  = 'This Template Kit uses demo images from Envato Elements. You will need to license these images from Envato Elements to use them on your website, or you can substitute them with your own.';
			$output .= ( $market === 'elements' ) ? "\n" : "<br/><br/>\n";
			// Start a list item of images:
			$output    .= ( $market === 'elements' ) ? '' : "<ul>\n";
			$image_urls = array();
			foreach ( $envato_elements_images as $image ) {
				$image_urls[] = $image['user_data']['image_urls'];
			}
			foreach ( array_unique( $image_urls ) as $image_url ) {
				$output .= ( $market === 'elements' ) ? '* ' : '<li>';
				$output .= $image_url;
				$output .= ( $market === 'elements' ) ? "\n" : "</li>\n";
			}
			// End the list:
			$output .= ( $market === 'elements' ) ? "\n" : "</ul>\n";
			return $output;
		} else {
			return '(no Envato Elements images found, not generating default markup)';
		}
	}
}
