<?php
/**
 * Elementor template exporter.
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
 * Gutenberg template exporter.
 *
 * This class handles exporting from Elementor.
 *
 * @since      1.0.0
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/builders
 * @author     Envato <->
 */
class Template_Kit_Export_Builders_Elementor extends Template_Kit_Export_Builders_Base {

	/**
	 * Get the type of page builder.
	 *
	 * @since    1.0.0
	 * @return   string The page builder that is being used.
	 */
	public function get_page_builder_type() {
		return 'elementor';
	}

	/**
	 * Get the data to export.
	 *
	 * @since    1.0.0
	 * @param    array $template The array of template data that is to be exported.
	 * @return   string The page builder that is being used.
	 */
	public function get_template_export_data( $template ) {
		// check this template is actually an ELementor template.
		$template_id = $template['id'];

		$source = \Elementor\Plugin::$instance->templates_manager->get_source( 'local' );

		$template_data = $source->get_data(
			array(
				'template_id' => $template_id,
			)
		);

		if ( empty( $template_data['content'] ) ) {
			// Kit styles have empty content, so we allow for exporting them here.
			// return new WP_Error( 'empty_template', 'The template is empty' );
		}

		$template_data['content'] = $this->process_export_import_content( $template_data['content'], 'on_export' );

		if ( get_post_meta( $template_id, '_elementor_page_settings', true ) ) {
			$page = Elementor\Core\Settings\Manager::get_settings_managers( 'page' )->get_model( $template_id );

			$page_settings_data = $this->process_element_export_import_content( $page, 'on_export' );

			if ( ! empty( $page_settings_data['settings'] ) ) {
				$template_data['page_settings'] = $page_settings_data['settings'];
			}
		}

		$section_or_page = 'section';
		if ( ! empty( $template['metadata']['template_type'] ) ) {
			if ( strpos( $template['metadata']['template_type'], 'page' ) !== false ) {
				$section_or_page = 'page';
			}
		}

		// We also want to grab the _wp_page_template metadata value if it exists
		// so that the chosen template comes through to the import plugin correctly.
		$wp_page_template = get_post_meta( $template_id, '_wp_page_template', true );
		if ( $wp_page_template ) {
			$template['metadata']['wp_page_template'] = $wp_page_template;
		}

		$export_data = array(
			'version'  => Elementor\DB::DB_VERSION,
			'title'    => get_the_title( $template_id ),
			'type'     => ! empty( $template['metadata']['elementor_library_type'] ) ? $template['metadata']['elementor_library_type'] : $section_or_page,
			'metadata' => $template['metadata'],
		);

		$export_data += $template_data;

		return $export_data;
	}

	/**
	 * Process the content to export/import.
	 *
	 * Copied protection method from: wp-content/plugins/elementor/includes/template-library/sources/local.php
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    mixed $content The content that will be processed for export/import.
	 * @param    mixed $method The method to used.
	 * @return   mixed Things are being returned.
	 */
	private function process_export_import_content( $content, $method ) {
		return \Elementor\Plugin::$instance->db->iterate_data(
			$content,
			function( $element_data ) use ( $method ) {
				$element = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );

				// If the widget/element isn't exist, like a plugin that creates a widget but deactivated.
				if ( ! $element ) {
					return null;
				}

				return $this->process_element_export_import_content( $element, $method );
			}
		);
	}

	/**
	 * Process element content to export/import.
	 *
	 * Copied protection method from: wp-content/plugins/elementor/includes/template-library/sources/local.php
	 *
	 * @since    1.0.0
	 * @param    mixed $element The element to use.
	 * @param    mixed $method The method to use.
	 * @return   mixed Things are being returned.
	 */
	private function process_element_export_import_content( $element, $method ) {
		$element_data = $element->get_data();

		if ( method_exists( $element, $method ) ) {
			// TODO: Use the internal element data without parameters.
			$element_data = $element->{$method}( $element_data );
		}

		// TODO: there's some fields we stripped in the Elements Content App, we should look at doing that here too somehow
		// This prevents someone accidentally sharing their email address/API keys in the exported JSON data.
		$strip_keys = array(
			'mailchimp_api_key',
			'mailchimp_list_id',
			'email_to',
			'email_to_2',
			'email_from',
			'email_from_2',
			'email_reply_to',
			'email_reply_to_2',
		);

		foreach ( $element->get_controls() as $control ) {
			$control_class = \Elementor\Plugin::$instance->controls_manager->get_control( $control['type'] );

			// If the control isn't exist, like a plugin that creates the control but deactivated.
			if ( ! $control_class ) {
				return $element_data;
			}

			if ( method_exists( $control_class, $method ) ) {
				$element_data['settings'][ $control['name'] ] = $control_class->{$method}( $element->get_settings( $control['name'] ), $control );
			}

			// On Export, check if the control has an argument 'export' => false.
			if ( 'on_export' === $method && isset( $control['export'] ) && false === $control['export'] ) {
				unset( $element_data['settings'][ $control['name'] ] );
			}
		}

		return $element_data;
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
		$all_templates = $this->get_all_templates_in_kit();
		$all_images    = array();
		foreach ( $all_templates as $template ) {
			// We manually parse the Elementor JSON data to extract any included images
			$elementor_meta = json_decode( get_post_meta( $template['id'], '_elementor_data', true ), true );
			if ( $elementor_meta ) {
				$all_template_image_ids = $this->extract_elementor_image_details( $elementor_meta );
				$image_ids              = array_unique( $all_template_image_ids );
				foreach ( $image_ids as $image_id ) {
					// we have an image used on this template, add it to the global $all_images array with appropriate metadata for the UI to use
					if ( ! isset( $all_images[ $image_id ] ) ) {
						$image_src = wp_get_attachment_image_src( $image_id, 'tk_preview' );
						if ( $image_src ) {
							$image_filename          = basename( get_attached_file( $image_id ) );
							$image_meta              = get_post_meta( $image_id, 'tk_image_user_data', true );
							$image_size              = wp_get_attachment_metadata( $image_id );
							$all_images[ $image_id ] = array(
								'image_id'          => $image_id,
								'thumbnail_url'     => $image_src[0],
								'filename'          => $image_filename,
								'used_on_templates' => array(),
								'user_data'         => is_array( $image_meta ) ? $image_meta : array(),
								'filesize'          => filesize( get_attached_file( $image_id ) ),
								// Sometimes wp_get_attachment_metadata() returns an empty string '' instead of an array.
								// We assume this is to do with an incorrectly coded 3rd party plugin.
								// WordPress core checks for "isset( $meta['width'], $meta['height'] )" after wp_get_attachment_metadata()
								// in a number of places, so we'll do the same thing here.
								// The author will get a 0px x 0px dimension listed in their backend, but that's better than an uncaught error message.
								'dimensions'        => isset( $image_size['width'], $image_size['height'] ) ? array( $image_size['width'], $image_size['height'] ) : array( 0, 0 ),
							);
						}
					}
					if ( ! empty( $all_images[ $image_id ] ) ) {
						$all_images[ $image_id ]['used_on_templates'][ $template['id'] ] = $template['name'];
					}
				}
			}
		}
		return $all_images;
	}



	private function extract_elementor_image_details( $elementor_meta ) {
		$image_ids = array();
		if ( is_array( $elementor_meta ) ) {
			foreach ( $elementor_meta as $key => $val ) {
				if ( is_array( $val ) ) {
					if ( isset( $val['widgetType'] ) && 'image-carousel' !== $val['widgetType'] && ! empty( $val['settings']['carousel'] ) ) {
						// Image carousel widget
						foreach ( $val['settings']['carousel'] as $image ) {
							$image_id = intval( $image['id'] );
							if ( $image_id > 0 ) {
								$image_ids[] = $image_id;
							}
						}
					} elseif ( isset( $val['image'] ) && ! empty( $val['image']['id'] ) && is_numeric( $val['image']['id'] ) && intval( $val['image']['id'] ) > 0 ) {
						// This is an elementor image widget.
						$image_id = intval( $val['image']['id'] );
						if ( $image_id > 0 ) {
							$image_ids[] = $image_id;
						}
					} else {
						// Loop into the Elementor data array further
						$image_ids = array_merge( $image_ids, $this->extract_elementor_image_details( $val ) );
					}
				} elseif ( 'id' === $key && is_numeric( $val ) && intval( $val ) > 0 ) {
					// some other sort of image (e.g. background)
					// check media file exists with this id.
					$possible_image_id = intval( $val );
					// We add it in anyway because the parent method find_all_images() confirms if this ID is actually an iamge or not.
					$image_ids[] = $possible_image_id;

				}
			}
		}
		return $image_ids;
	}

	/**
	 * This returns an array of fields that are used in Step 2 of the Export process,
	 *
	 * @return array
	 */
	public function get_template_meta_fields() {
		return array(
			array(
				'name'    => 'template_type',
				'label'   => __( 'Template Type:', 'template-kit-export' ),
				'type'    => 'select',
				'options' => array(
					''              => __( ' - Select Template Type - ', 'template-kit-export' ),
					'page'          => array(
						'label'   => __( 'Full Page', 'template-kit-export' ),
						'options' => array(
							'single-page'      => __( 'Single: Page', 'template-kit-export' ),
							'single-home'      => __( 'Single: Home', 'template-kit-export' ),
							'single-post'      => __( 'Single: Post', 'template-kit-export' ),
							'single-product'   => __( 'Single: Product', 'template-kit-export' ),
							'single-404'       => __( 'Single: 404', 'template-kit-export' ),
							'landing-page'     => __( 'Single: Landing Page', 'template-kit-export' ),
							'archive-blog'     => __( 'Archive: Blog', 'template-kit-export' ),
							'archive-product'  => __( 'Archive: Product', 'template-kit-export' ),
							'archive-search'   => __( 'Archive: Search', 'template-kit-export' ),
							'archive-category' => __( 'Archive: Category', 'template-kit-export' ),
						),
					),
					'section'       => array(
						'label'   => __( 'Section / Block', 'template-kit-export' ),
						'options' => array(
							'section-header'      => __( 'Header', 'template-kit-export' ),
							'section-footer'      => __( 'Footer', 'template-kit-export' ),
							'section-popup'       => __( 'Popup', 'template-kit-export' ),
							'section-hero'        => __( 'Hero', 'template-kit-export' ),
							'section-about'       => __( 'About', 'template-kit-export' ),
							'section-faq'         => __( 'FAQ', 'template-kit-export' ),
							'section-contact'     => __( 'Contact', 'template-kit-export' ),
							'section-cta'         => __( 'Call to Action', 'template-kit-export' ),
							'section-team'        => __( 'Team', 'template-kit-export' ),
							'section-map'         => __( 'Map', 'template-kit-export' ),
							'section-features'    => __( 'Features', 'template-kit-export' ),
							'section-pricing'     => __( 'Pricing', 'template-kit-export' ),
							'section-testimonial' => __( 'Testimonial', 'template-kit-export' ),
							'section-product'     => __( 'Product', 'template-kit-export' ),
							'section-services'    => __( 'Services', 'template-kit-export' ),
							'section-stats'       => __( 'Stats', 'template-kit-export' ),
							'section-countdown'   => __( 'Countdown', 'template-kit-export' ),
							'section-portfolio'   => __( 'Portfolio', 'template-kit-export' ),
							'section-gallery'     => __( 'Gallery', 'template-kit-export' ),
							'section-logo-grid'   => __( 'Logo Grid', 'template-kit-export' ),
							'section-clients'     => __( 'Clients', 'template-kit-export' ),
							'section-other'       => __( 'Other', 'template-kit-export' ),
						),
					),
					'global-styles' => __( 'Global Kit Styles', 'template-kit-export' ),
				),
			),
			array(
				'name'  => 'include_in_zip',
				'label' => __( 'Include Template in Export ZIP', 'template-kit-export' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'elementor_pro_required',
				'label' => __( 'Elementor Pro Required', 'template-kit-export' ),
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
		$errors = parent::detect_any_errors_with_template_kit();

		// We need the template types to check if there are ids in the blocks.
		$template_types          = $this->get_template_meta_fields();
		$template_types_sections = $template_types[0]['options']['section']['options'];

		// See if there's any missing required fields on the template kits:
		$templates       = $this->get_all_templates_in_kit();
		$template_errors = array();
		foreach ( $templates as $template ) {
			if ( empty( $template['metadata']['include_in_zip'] ) ) {
				// We want to skip checking errors for any templates not included in the ZIP
				continue;
			}
			// Check for missing template type
			if ( empty( $template['metadata']['template_type'] ) ) {
				$template_errors[] = 'Please choose a template type for: ' . $template['name'];
			}

			// Check the Elementor metadata to ensure it matches our requirements.
			$elementor_meta = json_decode( get_post_meta( $template['id'], '_elementor_data', true ), true );
			if ( $elementor_meta ) {
				$iterator     = new RecursiveIteratorIterator( new RecursiveArrayIterator( $elementor_meta ) );
				$element_type = 'widget';
				foreach ( $iterator as $key => $val ) {
					if ( 'elType' === $key ) {
						$element_type = $val;
					}
					if ( 'custom_css' === $key && strlen( $val ) > 0 ) {
						// Look for any Custom CSS coming in from Elementor Pro:
						$template_errors[] = 'Please remove Custom CSS from the ' . $element_type . ' in template: ' . $template['name'];
					} elseif ( '_element_id' === $key && strlen( $val ) > 0 && array_key_exists( $template['metadata']['template_type'], $template_types_sections ) ) {
						// Error if the user has set a custom element ID on any element
						$template_errors[] = 'Please remove Custom ID value "' . $val . '" from the ' . $element_type . ' in template: ' . $template['name'];
					} elseif ( '_css_classes' === $key && strlen( $val ) > 0 ) {
						// Error if hte user has set a custom classname on any element
						$template_errors[] = 'Please remove Custom Class value "' . $val . '" from the ' . $element_type . ' in template: ' . $template['name'];
					}

					// Hunt for mailto links:
					if ( preg_match_all( '#mailto:\w+#', $val, $matches ) ) {
						foreach ( $matches[0] as $match ) {
							$template_errors[] = 'Please remove the Email link "' . $match . '" from the ' . $element_type . ' in template: ' . $template['name'];
						}
					}
					// Hunt for inline styles:
					if ( preg_match_all( '#style=[\'"]([^\'"]+)[\'"]#imsU', $val, $matches ) ) {
						$allowed_built_in_styles = array(
							'#text-align:[^;]+;#' => '',
						);
						foreach ( $matches[1] as $match ) {
							if ( preg_replace( array_keys( $allowed_built_in_styles ), array_values( $allowed_built_in_styles ), $match ) !== '' ) {
								$template_errors[] = 'Please remove any inline style="' . esc_html( $match ) . '" from ' . $template['name'];
							}
						}
					}
					// Hunt for inline class names
					if ( preg_match_all( '#class=[\'"]([^\'"]+)[\'"]#imsU', $val, $matches ) ) {
						$allowed_built_in_classes = array(
							'size-',
							'wp-image',
							'align',
						);
						foreach ( $matches[1] as $match ) {
							if ( str_replace( $allowed_built_in_classes, '', $match ) === $match ) {
								$template_errors[] = 'Please remove any inline class="' . esc_html( $match ) . '" from ' . $template['name'];
							}
						}
					}
					// Hunt for onclick event handlers:
					if ( preg_match_all( '#(on\w+)=[\'"]([^\'"]+)[\'"]#imsU', $val, $matches ) ) {
						foreach ( $matches[1] as $onclick ) {
							$template_errors[] = 'No ' . esc_html( $onclick ) . ' allowed in: ' . $template['name'];
						}
					}
					if ( preg_match_all( '#<script[^>]*>(.*)<#imsU', $val, $matches ) ) {
						foreach ( $matches[1] as $match ) {
							$template_errors[] = 'No script tags allowed in: ' . $template['name'];
						}
					}
					if ( preg_match_all( '/<(link|meta|div|span|table)[^>]*>/', $val, $matches ) ) {
						foreach ( $matches[0] as $match ) {
							if ( strlen( $match ) > 1 ) {
								$template_errors[] = 'Please remove any custom HTML tags: ' . esc_html( $match ) . ': ' . $template['name'];
							}
						}
					}
				}
			}
		}
		if ( $template_errors ) {
			if ( ! isset( $errors['templates'] ) ) {
				$errors['templates'] = array();
			}
			// Append our errors onto any parent errors
			$errors['templates'] = array_merge( $errors['templates'], $template_errors );
		}

		return $errors;
	}

	/**
	 * Add the additional field to our manifest data that will tell the importer plugin if Elementor Pro is required
	 *
	 * @param $template
	 * @param $manifest_data
	 *
	 * @return   array
	 * @since    1.0.0
	 */
	public function get_template_manifest_data( $template, $manifest_data ) {
		$manifest_data                           = parent::get_template_manifest_data( $template, $manifest_data );
		$manifest_data['elementor_pro_required'] = ! empty( $template['metadata'] ) && ! empty( $template['metadata']['elementor_pro_required'] );
		return $manifest_data;
	}


	/**
	 * Check if there are any site health issues in the context of a page builder.
	 *
	 * This checks if "Elementor" plugin is installed and if "Hello Elementor" theme is active.
	 *
	 * @return array
	 */
	public function get_site_health() {
		$health = parent::get_site_health();
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			$health['errors'][] = __( 'Please install the "Elementor" Plugin to continue.', 'template-kit-export' );
		}
		$current_active_parent_theme = get_template();
		if ( 'hello-elementor' !== $current_active_parent_theme ) {
			$health['errors'][] = __( 'Please ensure the "Hello Elementor" theme is installed and active on this site.', 'template-kit-export' );
		}
		return $health;
	}

	/**
	 * Add the additional help file to our zip
	 *
	 * @return void
	 * @since    1.0.0
	 */
	public function add_additional_files_to_zip() {

		$this->add_file_to_zip( 'help.html', plugin_dir_path( __DIR__ ) . 'zip-contents/elementor/help.html' );

	}


	/**
	 * Get all templates in our CPT.
	 *
	 * Returns an array of all templates in our CPT. Used in the frontend and
	 * also in the zip building process. This extends the default CPT template list
	 * by also returning a list of Elementor Pro Theme Builder templates.
	 *
	 * @since    1.0.2
	 */
	public function get_all_templates_in_kit( $only_include_final_zip_templates = false ) {
		$templates = parent::get_all_templates_in_kit( $only_include_final_zip_templates );

		if ( \Elementor\Plugin::instance()->kits_manager ) {
			$active_kit_id = \Elementor\Plugin::instance()->kits_manager->get_active_id();
			if ( $active_kit_id > 0 ) {
				// We have a new Elementor kit ID, lets export it in the list, as the first item in the list:

				$template_meta_data = get_post_meta( $active_kit_id, 'envato_tk_post_meta', true );
				if ( ! is_array( $template_meta_data ) ) {
					$template_meta_data = array();
				}
				if ( Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) {
					// Flag all templates as included in the zip export when user is exporting an Elementor Kit
					// This allows our error checking to run on everything.
					$template_meta_data['include_in_zip'] = true;
				}
				$template_meta_data['additional_template_information'] = array(
					'These are the global theme styles configured through the Elementor Theme Styles area.',
				);
				$template_meta_data['template_type']                   = 'global-styles';
				if ( $only_include_final_zip_templates && empty( $template_meta_data['include_in_zip'] ) ) {
					// The user has chosen not to include Global Kit Styles in their theme
				} else {
					$templates[] = array(
						'id'             => $active_kit_id,
						'name'           => 'Global Kit Styles',
						'zip_filename'   => 'templates/global.json',
						'include_in_zip' => ! empty( $template_meta_data['include_in_zip'] ),
						'metadata'       => $template_meta_data,
						'preview_url'    => get_site_url( 'template-kit/' ),
						'order'          => - 1,
					);
				}
			}
		}

		// Now we look for Elementor Landing Page templates as well.
		// These templates are stored under their own Custom Post Type called "e-landing-page"
		$landing_page_templates = get_posts(
			array(
				'post_type'   => 'e-landing-page',
				'numberposts' => - 1,
			)
		);
		foreach ( $landing_page_templates as $landing_page_template ) {
			$template_meta_data = get_post_meta( $landing_page_template->ID, 'envato_tk_post_meta', true );
			if ( ! is_array( $template_meta_data ) ) {
				$template_meta_data = array();
			}
			if ( Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) {
				// Flag all templates as included in the zip export when user is exporting an Elementor Kit
				// This allows our error checking to run on everything.
				$template_meta_data['include_in_zip'] = true;
			}
			// We export this as a 'page' template so it shows up correctly when imported into Elementor library at other end
			$template_meta_data['elementor_library_type'] = 'page';

			if ( $only_include_final_zip_templates && empty( $template_meta_data['include_in_zip'] ) ) {
				// We don't want this template in the zip
			} else {
				$templates[] = array(
					'id'             => $landing_page_template->ID,
					'name'           => $landing_page_template->post_title,
					'zip_filename'   => 'templates/' . $this->sanitise_filename( $landing_page_template->post_title ) . '.json',
					'include_in_zip' => ! empty( $template_meta_data['include_in_zip'] ),
					'metadata'       => $template_meta_data,
					'preview_url'    => get_permalink( $landing_page_template->ID ),
					'order'          => $landing_page_template->menu_order,
				);
			}
		}

		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			// Now we look for Elementor Pro Theme Builder templates as well:
			$theme_builder_templates = get_posts(
				array(
					'post_type'   => 'elementor_library',
					'numberposts' => - 1,
				)
			);
			foreach ( $theme_builder_templates as $theme_builder_template ) {
				$terms = get_the_terms( $theme_builder_template, 'elementor_library_type' );
				if ( $terms && count( $terms ) ) {
					$term = current( $terms );
					if ( $term->slug ) {
						switch ( $term->slug ) {
							case 'header':
							case 'footer':
							case 'single':
							case 'archive':
							case 'popup':
							case 'product-post':
							case 'product':
							case 'product-archive':
							case 'single-post':
							case 'single-page':
							case 'search-results':
							case 'error-404':
								$additional_template_information   = array();
								$additional_template_information[] = 'This is a "' . ucwords( str_replace( '-', ' ', $term->slug ) ) . '" template for Elementor Pro.';
								// We have a valid template type we'd like to offer to export:
								$template_meta_data = get_post_meta( $theme_builder_template->ID, 'envato_tk_post_meta', true );
								if ( ! is_array( $template_meta_data ) ) {
									$template_meta_data = array();
								}
								if ( Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) {
									// Flag all templates as included in the zip export when user is exporting an Elementor Kit
									// This allows our error checking to run on everything.
									$template_meta_data['include_in_zip'] = true;
								}
								// We store the legit library type here:
								$template_meta_data['elementor_library_type'] = $term->slug;
								// Flag that Elementor Pro is required:
								$template_meta_data['elementor_pro_required'] = '1';
								// We store the instances value so we can import that in the other end:
								$saved_conditions = get_post_meta( $theme_builder_template->ID, '_elementor_conditions', true );
								if ( is_array( $saved_conditions ) && count( $saved_conditions ) ) {
									$template_meta_data['elementor_pro_conditions'] = $saved_conditions;

									/** @var \ElementorPro\Modules\ThemeBuilder\Module $theme_builder_module */
									$theme_builder_module = ElementorPro\Modules\ThemeBuilder\Module::instance();
									$conditions_manager   = $theme_builder_module->get_conditions_manager();

									ob_start();
									$conditions_manager->admin_columns_content( 'instances', $theme_builder_template->ID );
									$display_condition = ob_get_clean();

									$display_condition = str_replace( '<br />', ' &amp; ', $display_condition );

									$additional_template_information[] = 'This template will display on: ' . $display_condition . '.';
								}

								$template_meta_data['additional_template_information'] = $additional_template_information;
								if ( $only_include_final_zip_templates && empty( $template_meta_data['include_in_zip'] ) ) {
									// We don't want this template in the zip
								} else {
									$templates[] = array(
										'id'             => $theme_builder_template->ID,
										'name'           => $theme_builder_template->post_title,
										'zip_filename'   => 'templates/' . $this->sanitise_filename( $theme_builder_template->post_title ) . '.json',
										'include_in_zip' => ! empty( $template_meta_data['include_in_zip'] ),
										'metadata'       => $template_meta_data,
										'preview_url'    => get_permalink( $theme_builder_template->ID ),
										'order'          => $theme_builder_template->menu_order,
									);
								}
								break;
						}
					}
				}
			}
		}

		if ( Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) {
			// If we're doing a full site export, we also bundle in all pages and posts for checking too.
			$all_posts = get_posts(
				array(
					'post_type'   => [ 'post', 'page' ],
					'numberposts' => - 1,
				)
			);
			foreach ( $all_posts as $post ) {
				$templates[] = array(
					'id'             => $post->ID,
					'name'           => $post->post_title,
					'zip_filename'   => 'templates/' . $post->post_type . '-' . $this->sanitise_filename( $post->post_title ) . '.json',
					'include_in_zip' => true,
					'metadata'       => [
						'elementor_library_type' => 'page'
					],
					'preview_url'    => get_permalink( $post->ID ),
					'order'          => $post->menu_order,
				);
			}
		}

		uasort(
			$templates,
			function( $a, $b ) {
				return $a['order'] > $b['order'];
			}
		);

		return $templates;
	}
}
