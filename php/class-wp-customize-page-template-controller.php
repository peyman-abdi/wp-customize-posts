<?php
/**
 * Customize Page Template Controller Class
 *
 * @package WordPress
 * @subpackage Customize
 */

/**
 * Class WP_Customize_Page_Template_Controller
 */
class WP_Customize_Page_Template_Controller extends WP_Customize_Postmeta_Controller {

	/**
	 * Meta key.
	 *
	 * @var string
	 */
	public $meta_key = '_wp_page_template';

	/**
	 * Post type support for the postmeta.
	 *
	 * @var string
	 */
	public $post_type_supports = 'page-attributes';

	/**
	 * Setting transport.
	 *
	 * @var string
	 */
	public $setting_transport = 'refresh';

	/**
	 * Default value.
	 *
	 * @var string
	 */
	public $default = 'default';

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		$handle = 'customize-page-template';
		wp_enqueue_script( $handle );
		wp_add_inline_script( $handle, 'CustomizePageTemplate.init()' );

		$exports = array(
			'defaultPageTemplateChoices' => $this->get_page_template_choices(),
			'l10n' => array(
				'controlLabel' => __( 'Page Template', 'customize-posts' ),
			),
		);
		wp_scripts()->add_data(
			$handle,
			'data',
			sprintf( 'var _wpCustomizePageTemplateExports = %s', wp_json_encode( $exports ) )
		);
	}

	/**
	 * Get page template choices.
	 *
	 * @return array
	 */
	public function get_page_template_choices() {
		$choices = array();
		$choices[] = array(
			'value' => 'default',
			'text' => __( '(Default)', 'customize-posts' ),
		);
		foreach ( wp_get_theme()->get_page_templates() as $template_file => $template_name ) {
			$choices[] = array(
				'text' => $template_name,
				'value' => $template_file,
			);
		}
		return $choices;
	}

	/**
	 * Apply rudimentary sanitization of a file path for a generic setting instance.
	 *
	 * @see sanitize_meta()
	 *
	 * @param string $raw_path Path.
	 * @return string Path.
	 */
	public function sanitize_value( $raw_path ) {
		$path = $raw_path;
		$special_chars = array( '..', './', chr( 0 ) );
		$path = str_replace( $special_chars, '', $path );
		$path = ltrim( $path, '/' );
		return $path;
	}

	/**
	 * Sanitize (and validate) an input for a specific setting instance.
	 *
	 * @see update_metadata()
	 *
	 * @param string                        $page_template The value to sanitize.
	 * @param WP_Customize_Postmeta_Setting $setting       Setting.
	 * @param bool                          $strict        Whether validation is being done. This is part of the proposed patch in in #34893.
	 * @return mixed|null Null if an input isn't valid, otherwise the sanitized value.
	 */
	public function sanitize_setting( $page_template, WP_Customize_Postmeta_Setting $setting, $strict = false ) {
		$post = get_post( $setting->post_id );
		$page_templates = wp_get_theme()->get_page_templates( $post );

		if ( 'default' !== $page_template && ! isset( $page_templates[ $page_template ] ) ) {
			if ( $strict ) {
				return new WP_Error( 'invalid_page_template', __( 'The page template is invalid.', 'customize-posts' ) );
			} else {
				return null;
			}
		}
		return $page_template;
	}
}
