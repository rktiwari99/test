<?php
/**
 * Sets up our custom post type for template kits.
 *
 * This class registers the Template Kit custom post type.
 *
 * @since      1.0.0
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/includes
 * @author     Envato <->
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register the custom post type and for the plugin.
 *
 * Maintain a list of all the labels and settings that are
 * used in the custom post type and register the custom post type
 * with the core register_post_type() function.
 *
 * @package    Template_Kit_Export
 * @subpackage Template_Kit_Export/includes
 * @author     Envato <->
 */
class Template_Kit_Export_CPT {

	const CPT_SLUG = 'envato_tk_templates';

	/**
	 * Register custom post type.
	 *
	 * @since    1.0.0
	 */
	public function register_cpt() {

		$labels = array(
			'name'               => __( 'Templates', 'template-kit-export' ),
			'singular_name'      => __( 'Template', 'template-kit-export' ),
			'menu_name'          => __( 'Templates', 'template-kit-export' ),
			'parent_item_colon'  => __( 'Parent Template:', 'template-kit-export' ),
			'all_items'          => __( 'All Templates', 'template-kit-export' ),
			'view_item'          => __( 'View Template', 'template-kit-export' ),
			'add_new_item'       => __( 'Add New Template', 'template-kit-export' ),
			'add_new'            => __( 'New Template', 'template-kit-export' ),
			'edit_item'          => __( 'Edit Template', 'template-kit-export' ),
			'update_item'        => __( 'Update Template', 'template-kit-export' ),
			'search_items'       => __( 'Search Templates', 'template-kit-export' ),
			'not_found'          => __( 'No Templates found', 'template-kit-export' ),
			'not_found_in_trash' => __( 'No Templates found in Trash', 'template-kit-export' ),
		);

		$args = array(
			'description'         => __( 'Templates', 'template-kit-export' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'author', 'editor', 'revisions', 'thumbnail', 'custom-fields', 'elementor' ),
			'taxonomies'          => array(),
			'hierarchical'        => false,
			'public'              => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'menu_position'       => 36,
			'menu_icon'           => 'dashicons-star-filled',
			'can_export'          => true,
			'has_archive'         => true,
			'publicly_queryable'  => true,
			'rewrite'             => array( 'slug' => 'template-kit' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		);

		register_post_type( self::CPT_SLUG, $args );
	}

	/**
	 * Register meta boxes for the CPT.
	 *
	 * @since    1.0.1
	 */
	public function register_meta_boxes() {
		add_meta_box(
			self::CPT_SLUG . '-options',
			__( 'Template Options', 'template-kit-export' ),
			array( $this, 'meta_box_callback' ),
			self::CPT_SLUG,
			'side',
			'high'
		);
	}

	/**
	 * A meta boxes for the CPT.
	 *
	 * @since    1.0.1
	 * @param    array[] $post The post object that contains all the good things.
	 */
	public function meta_box_callback( $post ) {
		wp_nonce_field( basename( __FILE__ ), 'tk_meta_nonce' );

		try {
			$exporter        = Template_Kit_Export::get_exporter();
			$template_fields = $exporter->get_template_meta_fields();
			$template_id     = $post->ID;
			$template_meta   = get_post_meta( $template_id, 'envato_tk_post_meta', true );

			foreach ( $template_fields as $template_field ) {
				$label_id   = sprintf( '%s_%d', $template_field['name'], $template_id );
				$input_name = sprintf( '%s[templates][%d][%s]', Template_Kit_Export_Options::OPTIONS_KEY, $template_id, $template_field['name'] );
				?>
				<p>
				<?php
				if ( 'select' === $template_field['type'] ) {
					$current_value = ! empty( $template_meta['template_type'] ) ? $template_meta['template_type'] : '';
					?>
					<select id="<?php echo esc_attr( $label_id ); ?>" name="<?php echo esc_attr( $input_name ); ?>">
						<?php
						foreach ( $template_field['options'] as $option_key => $option_value ) {
							if ( is_array( $option_value ) ) {
								// We allow nested option groups here.
								?>
								<optgroup label="<?php echo esc_attr( $option_value['label'] ); ?>">
									<?php foreach ( $option_value['options'] as $option_group_value => $option_group_label ) { ?>
										<option value="<?php echo esc_attr( $option_group_value ); ?>"<?php echo selected( $option_group_value, $current_value ); ?>><?php echo esc_attr( $option_group_label ); ?></option>
									<?php } ?>
								</optgroup>
							<?php } else { ?>
								<option value="<?php echo esc_attr( $option_key ); ?>"<?php echo selected( $option_key, $current_value ); ?>><?php echo esc_attr( $option_value ); ?></option>
							<?php } ?>
						<?php } ?>
					</select>
					<?php
				}
				if ( 'checkbox' === $template_field['type'] ) {
					$is_value_selected = isset( $template_meta[ $template_field['name'] ] ) && '1' === $template_meta[ $template_field['name'] ] ? $template_meta[ $template_field['name'] ] : '';
					?>
					<input id="<?php echo esc_attr( $label_id ); ?>" type="checkbox" name="<?php echo esc_attr( $input_name ); ?>" value="1" <?php checked( $is_value_selected ); ?> />
					<label for="<?php echo esc_attr( $label_id ); ?>"><?php echo esc_html( $template_field['label'] ); ?></label>
					<?php
				}
				?>
				</p>
				<?php
			}
		} catch ( Exception $exception ) {
			esc_html_e( 'Please configure the Template Kit in the Settings area.', 'template-kit-export' );
		}
	}


	/**
	 * Update/save CPT meta from edit post/template page.
	 *
	 * @since    1.0.1
	 * @param    int $post_id The post id data.
	 */
	public function save_cpt_meta_data( $post_id ) {
		try {
			$builder = Template_Kit_Export::get_exporter();

			// Nonce check one two. Did you get the check, did you get the check.
			if ( ! isset( $_POST['tk_meta_nonce'] ) || ! wp_verify_nonce( $_POST['tk_meta_nonce'], basename( __FILE__ ) ) ) {
				return $post_id;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			$template_options = isset( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] ) && is_array( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ] )
								&& ! empty( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ]['templates'] )
								&& isset( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ]['templates'][ $post_id ] )
				? stripslashes_deep( $_POST[ Template_Kit_Export_Options::OPTIONS_KEY ]['templates'][ $post_id ] )
				: false;
			if ( $template_options ) {
				// Call out to our builder plugin to save options.
				// Builder can choose where to save them or if it needs to do any extra validation etc.
				$builder->save_template_options( $post_id, $template_options );
			}
		} catch ( Exception $exception ) {
			// noop
		}
		return $post_id;
	}
}
