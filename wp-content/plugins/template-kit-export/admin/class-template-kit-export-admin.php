<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/admin
 * @author     Envato <->
 */
class Template_Kit_Export_Admin {

	const ADMIN_MENU_SLUG = 'envato-export-template-kit';

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Template_Kit_Export_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Template_Kit_Export_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/template-kit-export-admin.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/template-kit-export-admin.min.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'template_kit_export',
			array(
				'thumbnail_width' => TEMPLATE_KIT_EXPORT_THUMBNAIL_WIDTH,
			)
		);
		wp_enqueue_script( $this->plugin_name );

	}

	/**
	 * Add the Envato icon to the admin menu.
	 *
	 * Taken from the Envato Elements plugin and implemented the same way to
	 * reduce space requried by css in the head.
	 *
	 * @since    1.0.0
	 */
	public function add_menu_icon() {
		require plugin_dir_path( __FILE__ ) . 'partials/template-kit-export-menu-icon.php';
	}

	/**
	 * Adds the admin menu to the WordPress sidebar
	 *
	 * @since     1.0.0
	 */
	public function template_kit_add_menu() {
		add_menu_page(
			__( 'Template Kits', 'template-kit-export' ),
			__( 'Template Kit', 'template-kit-export' ),
			'manage_options',
			self::ADMIN_MENU_SLUG,
			array( $this, 'template_kit_export_page' ),
			'',
			20
		);

		add_submenu_page(
			self::ADMIN_MENU_SLUG,
			__( 'Export', 'template-kit-export' ),
			__( 'Export', 'template-kit-export' ),
			'manage_options',
			self::ADMIN_MENU_SLUG,
			array( $this, 'template_kit_export_page' )
		);

		add_submenu_page(
			self::ADMIN_MENU_SLUG,
			__( 'Templates', 'template-kit-export' ),
			__( 'Templates', 'template-kit-export' ),
			'manage_options',
			'edit.php?post_type=' . Template_Kit_Export_CPT::CPT_SLUG
		);

	}

	/**
	 * Disable the WP big image size threshold so users do not get cropped images of their uploads.
	 *
	 * @since     1.0.17
	 */
	public function disable_big_image_threshold() {
		add_filter( 'big_image_size_threshold', '__return_false' );
	}

	/**
	 * Register media thumbnail to use in preview step.
	 *
	 * @since     1.0.0
	 */
	public function register_media_size() {
		add_image_size( 'tk_preview', TEMPLATE_KIT_EXPORT_THUMBNAIL_WIDTH );
	}

	/**
	 * Add our custom size available to file_frame.
	 *
	 * @param string[] $sizes Array of available image sizes registered.
	 *
	 * @return array
	 * @since     1.0.0
	 */
	public function add_tk_preview_size( $sizes ) {
		return array_merge(
			$sizes,
			array(
				'tk_preview' => __( 'Template Kit Preview' ),
			)
		);
	}

	/**
	 * Add CPT to Template Kit menu item.
	 *
	 * Allows us to nest the default WordPress CPT view in a submenu item
	 * under our main plugin nav menu item.
	 *
	 * @param string $this_parent_file The file of the parent menu item.
	 *
	 * @return    string
	 * @since     1.0.0
	 */
	public function template_kit_nest_cpt_underneath( $this_parent_file ) {
		global $submenu_file;
		if ( is_admin() &&
		     (
			     'edit.php?post_type=' . Template_Kit_Export_CPT::CPT_SLUG === $submenu_file ||
			     'post-new.php?post_type=' . Template_Kit_Export_CPT::CPT_SLUG === $submenu_file
		     )
		) {
			$this_parent_file = self::ADMIN_MENU_SLUG;
			$submenu_file     = 'edit.php?post_type=' . Template_Kit_Export_CPT::CPT_SLUG; // phpcs:ignore
		}

		return $this_parent_file;
	}

	/**
	 * Displays the UI for the plugin export options
	 *
	 * @since     1.0.0
	 */
	public function template_kit_export_page() {
		require plugin_dir_path( __FILE__ ) . 'partials/template-kit-export-admin-wrapper.php';
	}

	/**
	 * Export zip admin action.
	 *
	 * This is called when the user visits admin.php?action=envato_tk_export_zip
	 * The hook for this method is setup in class-template-kit-export.php
	 *
	 * @throws    Exception Error a short error message.
	 * @since     1.0.0
	 */
	public function export_zip() {

		check_admin_referer( 'export_the_zip' );

		$zip_data = array();

		try {
			$exporter = Template_Kit_Export::get_exporter();
			$zip_data = $exporter->build_zip();
			if ( ! $zip_data || ! $zip_data['filename'] ) {
				throw new Exception( 'Unable to build zip' );
			}
			if ( ! $zip_data['zip_data'] ) {
				throw new Exception( 'Error missing zip data' );
			}
		} catch ( Exception $exception ) {
			wp_die( 'Zip export failed: ' . esc_html( $exception->getMessage() ) );
		}

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $zip_data['filename'] );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . strlen( $zip_data['zip_data'] ) );

		// phpcs:ignore
		echo $zip_data['zip_data'];
		exit;
	}

	/**
	 * Wizard save admin action.
	 *
	 * This is called when the user saves data for the template kit.
	 *
	 * @since     1.0.0
	 */
	public function wizard_save() {

		check_admin_referer( 'envato_tk_wizard', 'envato_tk_wizard_nonce' );

		$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : 1;

		$success = false;

		switch ( $step ) {
			case 1:
				$options = isset( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] ) && is_array( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] ) ? stripslashes_deep( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] ) : array();

				// Save the `export_type` input box to our options db:.
				if ( ! empty( $options['export_type'] ) ) {
					Template_Kit_Export_Options::save( 'export_type', $options['export_type'] );
					$success = true;
				}
				break;
			case 2:
				try {
					// check everything is valid / no missing fields / save to db etc...
					$options = isset( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] ) && is_array( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] ) ? stripslashes_deep( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] ) : array();

					// Save the `kit name` input box to our options db:.
					if ( isset( $options['kit_name'] ) ) {
						Template_Kit_Export_Options::save( 'kit_name', $options['kit_name'] );
					}

					// Save the `page builder` drop down option to our options db:.
					if ( ! empty( $options['page_builder'] ) ) {
						Template_Kit_Export_Options::save( 'page_builder', $options['page_builder'] );
					}

					// Save the `kit version` input box to our options db:.
					if ( isset( $options['kit_version'] ) ) {
						Template_Kit_Export_Options::save( 'kit_version', $options['kit_version'] );
					}

					// Save the `required_plugins` list to our options db:.
					if ( ! empty( $options['required_plugins'] ) ) {
						$required_plugins = array();
						foreach ( $options['required_plugins'] as $plugin_path => $maybe_required_plugin ) {
							if ( ! empty( $maybe_required_plugin['required'] ) ) {
								$required_plugins[ $plugin_path ] = $maybe_required_plugin;
							}
						}
						Template_Kit_Export_Options::save( 'required_plugins', wp_json_encode( stripslashes_deep( $required_plugins ) ) );
					}
					$builder = Template_Kit_Export::get_exporter();
					$errors  = $builder->detect_any_errors_with_template_kit();
					if ( empty( $errors['kit'] ) ) {
						$success = true;
					}
				} catch ( Exception $exception ) {
					wp_die( 'Error:: ' . esc_html( $exception->getMessage() ) );
				}
				break;
			case 3:
				try {
					$builder = Template_Kit_Export::get_exporter();
					// Find available templates, loop over each one and see if the user is updating data for them.
					$all_templates = $builder->get_all_templates_in_kit();
					foreach ( $all_templates as $template ) {
						// Grab the template options out of $_POST variable from the frontend.
						$template_options = isset( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] ) && is_array( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] )
						                    && ! empty( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ]['templates'] )
						                    && isset( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ]['templates'][ $template['id'] ] )
							? stripslashes_deep( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ]['templates'][ $template['id'] ] )
							: false;
						if ( $template_options ) {
							// Call out to our builder plugin to save options.
							// Builder can choose where to save them or if it needs to do any extra validation etc.
							$builder->save_template_options( $template['id'], $template_options );
						} else {
							echo 'No options found from frontend, this should not happen';
						}
					}
					$errors = $builder->detect_any_errors_with_template_kit();
					if ( empty( $errors['templates'] ) ) {
						$success = true;
					}
				} catch ( Exception $exception ) {
					wp_die( 'Error:: ' . esc_html( $exception->getMessage() ) );
				}
				break;
			case 4:
				try {
					$exporter   = Template_Kit_Export::get_exporter();
					$all_images = $exporter->find_all_images();
					foreach ( $all_images as $image ) {
						$user_data_to_save = isset( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ]['images'][ $image['image_id'] ] ) && is_array( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ]['images'][ $image['image_id'] ] ) ? stripslashes_deep( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ]['images'][ $image['image_id'] ] ) : false;
						if ( $user_data_to_save ) {
							$exporter->save_image_data( $image['image_id'], $user_data_to_save );
						}
					}
					$errors = $exporter->detect_any_errors_with_template_kit();
					if ( empty( $errors['images'] ) ) {
						$success = true;
					}
				} catch ( Exception $exception ) {
					wp_die( 'Error Saving Image Data: ' . esc_html( $exception->getMessage() ) );
				}

				break;
			default:
				throw new \Exception( 'Unexpected value' );
		}

		if ( $success ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::ADMIN_MENU_SLUG . '&step=' . ( $step + 1 ) ) );
			exit;
		}

		// not successful, like a missing field or something.
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::ADMIN_MENU_SLUG . '&error=1&step=' . $step ) );
		exit;
	}

	/**
	 * This is an action that is run after_setup_theme that allows us to modify the media template files
	 */
	public function inject_media_upload_warning_notice() {
		// Add our custom media template functions.
		add_action( 'admin_footer', array( $this, 'wp_print_customized_media_templates' ) );
		add_action( 'wp_footer', array( $this, 'wp_print_customized_media_templates' ) );
	}

	/**
	 * This lets us modify the media upload UI here using basic CSS
	 * wp-includes/media-template.php:158
	 */
	public function wp_print_customized_media_templates() {
		?>
		<style>
        .media-frame-title h1:after {
            content: 'Template Kit Notice: Please ensure Template Kit images are licensed correctly.';
            font-size: 12px;
            padding-left: 10px;
            font-weight: normal;
        }
		</style>
		<?php
	}


	/**
	 * Add additional meta fields to the image edit/upload window
	 *
	 * @param $form_fields array, fields to include in attachment form
	 * @param $post object, attachment record in database
	 *
	 * @return array $form_fields, modified form fields
	 */
	public function attachment_fields_to_edit( $form_fields, $post ) {

		try {
			$exporter                       = Template_Kit_Export::get_exporter();
			$image_fields                   = $exporter->get_image_meta_fields();
			$form_fields['tk-envato-title'] = array(
				'label' => 'Template Kit Image Meta Data:',
				'input' => 'html',
				'html'  => 'Please fill in details about this image for use in the Template Kit. <br/> Ensure the image is compatible with our <a href="https://help.author.envato.com/hc/en-us/articles/360038151251-WordPress-Template-Kit-Requirements" target="_blank" rel="noreferrer noopener">Template Kit Guidelines</a> before proceeding.',
			);
			$image_meta                     = get_post_meta( $post->ID, 'tk_image_user_data', true );
			foreach ( $image_fields as $image_field ) {
				if ( 'text' === $image_field['type'] ) {
					$form_fields[ 'tk-envato-' . $image_field['name'] ] = array(
						'label' => $image_field['label'],
						'input' => 'text',
						'value' => is_array( $image_meta ) && ! empty( $image_meta[ $image_field['name'] ] ) ? esc_attr( $image_meta[ $image_field['name'] ] ) : '',
					);
				}
				if ( 'select' === $image_field['type'] ) {
					$current_value = is_array( $image_meta ) && ! empty( $image_meta[ $image_field['name'] ] ) ? esc_attr( $image_meta[ $image_field['name'] ] ) : '';
					$html          = '';
					$html          .= '<select name="attachments[' . $post->ID . '][tk-envato-' . esc_attr( $image_field['name'] ) . ']">';
					foreach ( $image_field['options'] as $option_key => $option_value ) {
						$html .= '<option value="' . esc_attr( $option_key ) . '"' . selected( $option_key, $current_value, false ) . '>' . esc_attr( $option_value ) . '</option>';
					}
					$html                                               .= '</select>';
					$form_fields[ 'tk-envato-' . $image_field['name'] ] = array(
						'label' => $image_field['label'],
						'input' => 'html',
						'html'  => $html,
					);
				}
			}
		} catch ( Exception $exception ) {
			return $form_fields;
		}

		return $form_fields;
	}

	/**
	 * Save additional meta fields from the media upload dialog
	 *
	 * @param $post object, attachment record in database
	 * @param $attachment array, fields to include in attachment form
	 *
	 * @return object
	 */
	public function attachment_fields_to_save( $post, $attachment ) {

		// Unfortunately we cannot return errors from here.
		// See wp-admin/includes/ajax-actions.php:3094
		try {
			$exporter     = Template_Kit_Export::get_exporter();
			$image_fields = $exporter->get_image_meta_fields();
			$user_data    = array();
			foreach ( $image_fields as $image_field ) {
				if ( ! empty( $attachment[ 'tk-envato-' . $image_field['name'] ] ) ) {
					$user_data[ $image_field['name'] ] = $attachment[ 'tk-envato-' . $image_field['name'] ];
				}
			}
			update_post_meta( $post['ID'], 'tk_image_user_data', $user_data );
		} catch ( Exception $exception ) {
			return $post;
		}

		return $post;
	}

	/**
	 * Display a message above the WordPress Custom CSS edit box.
	 *
	 * @param $wp_customize WP_Customize_Manager
	 */
	public function customize_register( $wp_customize ) {
		$css              = $wp_customize->get_section( 'custom_css' );
		$css->description = '<div style="background: #fcf8e3; border-left: 5px solid #ded5a6; padding: 10px; color: #8a6d3b;"><strong>Warning</strong> <br/>Template kits cannot contain custom CSS. Any CSS entered into this customizer will not be exported as part of the template kit.</pre>';
	}

	/**
	 * Display a message above the Elementor Custom CSS edit box.
	 *
	 * @param $element    \Elementor\Controls_Stack
	 * @param $section_id string
	 */
	public function elementor_custom_css_message( $element, $section_id ) {
		if ( 'section_custom_css' !== $section_id ) {
			return;
		}

		$element->add_control(
			'custom_css_template_kit_warning',
			array(
				'raw'             => '<div style="background: #fcf8e3; border-left: 5px solid #ded5a6; padding: 10px; color: #8a6d3b;"><strong>Warning:</strong> Custom CSS is not allowed in template kits. Please use features available in the page builder.</div>',
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
			)
		);

	}


	/**
	 * Try to automatically tag certain images that are uploaded to the website.
	 *
	 * @param $metadata
	 * @param $attachment_id
	 *
	 * @return array
	 */
	public function wp_generate_attachment_metadata( $metadata, $attachment_id ) {

		if ( $metadata && ! empty( $metadata['file'] ) ) {
			// Most images uploaded to a Template Kit will be from Envato Elements.
			// These images follow a similar format like: coastline-with-palm-trees-P6WLLHN.jpg
			// We'd like to store the Elements item ID `P6WLLHN` in metadata so we can correctly license down the track.
			// We store this in the "tk_image_user_data" so it matches the format expected in Step 3 of the wizard.
			if ( preg_match( '#[\w-]([A-Z0-9]{7})(-\d+)?\.(png|jpg)#', $metadata['file'], $matches ) ) {
				// If we get here we have a 7 digit humane ID for an Envato Elements licensed photo.
				// Woohoo!
				update_post_meta(
					$attachment_id,
					'tk_image_user_data',
					array(
						'image_source'    => 'envato_elements',
						'person_or_place' => '',
						'image_urls'      => 'https://elements.envato.com/image-' . $matches[1],
					)
				);
			}
		}

		return $metadata;

	}

	/**
	 * Hooks into updating post meta in order to capture Envato Elements integrated photo imports.
	 * The Envato Elements plugin sets the photo ID as metadata on photo import:
	 *   update_post_meta( $attachment_id, 'envato_elements', $photo_id );
	 *
	 * We capture this by hooking into the update_postmeta call here:
	 *   do_action( "add_{$meta_type}_meta", $object_id, $meta_key, $_meta_value );
	 *
	 * @param $object_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	public function add_post_meta( $object_id, $meta_key, $meta_value ) {
		if ( 'envato_elements' === $meta_key && $meta_value && preg_match( '#[A-Z0-9]{7}#', $meta_value ) ) {
			// We've successfully licensed a photo from Envato Elements using the Envato Elements plugin.
			update_post_meta(
				$object_id,
				'tk_image_user_data',
				array(
					'image_source'    => 'envato_elements',
					'person_or_place' => '',
					'image_urls'      => 'https://elements.envato.com/image-' . $meta_value,
				)
			);
		}
	}

	/**
	 * Adds an admin notice if the Envato Elements plugin isn't installed
	 */
	public function envato_elements_plugin_admin_notice() {
		if ( ! defined( 'ENVATO_ELEMENTS_SLUG' ) && ! Template_Kit_Export_Options::get( 'dismiss_envato_elements_plugin', false ) ) {
			require plugin_dir_path( __FILE__ ) . 'partials/template-kit-export-install-envato-elements.php';
		}
	}

	/**
	 * Callback for dismissing the admin notice above
	 */
	public function envato_elements_plugin_admin_notice_dismiss() {
		Template_Kit_Export_Options::save( 'dismiss_envato_elements_plugin', true );
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::ADMIN_MENU_SLUG ) );
	}

	/**
	 * Filter for disabling all the Elementor custom schemes that we are unable to Export/Import as part of the Kit.
	 *
	 * @param $schemes array
	 *
	 * @return array
	 */
	public function disable_elementor_schemes( $schemes ) {
		return array(
			'color-picker',
		);
	}

	/**
	 * Checks if the version number has changed.
	 * Allows us to run some code on initial plugin activation and version upgrade
	 */
	public function version_upgrade_check() {
		if ( Template_Kit_Export_Options::get( 'plugin_version' ) !== TEMPLATE_KIT_EXPORT_VERSION ) {
			// Force WordPress to rebuild rewrite rules completely:
			delete_option( 'rewrite_rules' );
			flush_rewrite_rules();
			Template_Kit_Export_Options::save( 'plugin_version', TEMPLATE_KIT_EXPORT_VERSION );
		}
	}

	/**
	 * Modify the Elementor export manifest.json data, inject some additional metadata around images.
	 * Only do this for Elementor Kit export types.
	 *
	 * @param $manifest_data
	 * @param $elementor
	 */
	public function modify_elementor_export( $manifest_data, $elementor ) {
		if ( Template_Kit_Export_Options::get( 'export_type' ) === TEMPLATE_KIT_EXPORT_TYPE_ELEMENTOR ) {
			try {
				$exporter  = Template_Kit_Export::get_exporter();
				$templates = $exporter->get_all_templates_in_kit();

				$manifest_data['_envato'] = [
					'manifest_version' => TEMPLATE_KIT_EXPORT_VERSION,
					'title'            => $exporter->get_kit_name(),
					'page_builder'     => $exporter->get_page_builder_type(),
					'kit_version'      => $exporter->get_kit_version(),
					'templates'        => false,
					'required_plugins' => $exporter->get_required_plugins_for_manifest(),
					'images'           => $exporter->get_image_meta_data_for_manifest( $templates ),
				];
			} catch ( Exception $exception ) {
				// noop
			}
		}

		return $manifest_data;
	}

}
