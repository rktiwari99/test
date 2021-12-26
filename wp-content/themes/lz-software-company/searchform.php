<?php
/**
 * Template for displaying search forms in LZ Software Company
 *
 * @subpackage LZ Software Company
 * @since 1.0
 * @version 0.1
 */
?>

<?php $lz_software_company_unique_id = esc_attr( uniqid( 'search-form-' ) ); ?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e('Search for:','lz-software-company'); ?></span>
		<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search', 'placeholder','lz-software-company' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	</label>
	<button type="submit" class="search-submit"><?php echo esc_html_x( 'Search', 'submit button', 'lz-software-company' ); ?></button>
</form>