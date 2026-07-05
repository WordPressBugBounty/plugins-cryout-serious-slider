<?php
/**
 * Block Editor integration for Serious Slider
 *
 * Uses direct call to shortcode_render() front rendering to avoid functionality duplication
 * Allows per-block instance options override through the existing 'cryout_serious_slider_shortcode_attributes' filter
 *
 * Option choices and defaults are inherited from the main class (as $option_choices and $defaults) and passed to JS
 * in enqueue_editor_resources()
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Cryout_Serious_Slider_Blocks {

	private $rest_endpoint = 'cryout-serious-slider/v1';

	const nooverride = '__default__';

	public function __construct() {
		add_action( 'rest_api_init',               array( $this, 'register_rest_routes' ) );
		add_action( 'init',                        array( $this, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_resources' ) );
	} // __construct()

	public function register_rest_routes() {
		register_rest_route( $this->rest_endpoint, '/sliders', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'rest_list_sliders' ),
			'permission_callback' => function() { return current_user_can( 'edit_others_posts' ); },
		) );
		register_rest_route( $this->rest_endpoint, '/sliders/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'rest_get_slider' ),
			'permission_callback' => function() { return current_user_can( 'edit_others_posts' ); },
			'args'                => array( 'id' => array( 'type' => 'integer', 'required' => true, 'minimum' => 1 ) ),
		) );
	} // register_rest_routes()

	public function rest_list_sliders() {
		global $cryout_serious_slider;
		$terms = get_terms( array( 'taxonomy' => $cryout_serious_slider->taxonomy, 'hide_empty' => false ) );
		if ( is_wp_error( $terms ) ) return new WP_Error( 'terms_error', $terms->get_error_message(), array( 'status' => 500 ) );

		$data = array();
		foreach ( $terms as $term ) {
			$data[] = array(
				'id'          => $term->term_id,
				'name'        => $term->name,
				'slug'        => $term->slug,
				'count'       => $term->count,
				'first_thumb' => $this->get_first_thumb( $term->term_id ),
				'shortcode'   => '[serious-slider id="' . $term->term_id . '"]',
			);
		}
		return rest_ensure_response( $data );
	} // rest_list_sliders()

	public function rest_get_slider( WP_REST_Request $request ) {
		global $cryout_serious_slider;
		$id   = (int) $request->get_param( 'id' );
		$term = get_term( $id, $cryout_serious_slider->taxonomy );
		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'not_found', __( 'Slider not found.', 'cryout-serious-slider' ), array( 'status' => 404 ) );
		}

		$raw_opts   = get_option( "cryout_serious_slider_{$id}_meta" );
		$options    = wp_parse_args( $raw_opts, $cryout_serious_slider->defaults );
		$clean_opts = array();
		foreach ( $options as $k => $v ) {
			$clean_opts[ str_replace( 'cryout_serious_slider_', '', $k ) ] = $v;
		}

		$slides_query = new WP_Query( array(
			'post_type'      => $cryout_serious_slider->posttype,
			'posts_per_page' => 50,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'tax_query'      => array( array(
				'taxonomy' => $cryout_serious_slider->taxonomy,
				'field'    => 'term_id',
				'terms'    => $id,
			) ),
		) );

		$slides = array();
		foreach ( $slides_query->posts as $post ) {
			$thumb_id = get_post_thumbnail_id( $post->ID );
			$slides[] = array(
				'id'        => $post->ID,
				'title'     => $post->post_title,
				'thumb_url' => $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '',
			);
		}

		return rest_ensure_response( array(
			'id'        => $term->term_id,
			'name'      => $term->name,
			'slug'      => $term->slug,
			'shortcode' => '[serious-slider id="' . $term->term_id . '"]',
			'options'   => $clean_opts,
			'slides'    => $slides,
		) );
	} // rest_get_slider()
	
	/* defines locally overridable option ids */
	private function get_override_map() {
		global $cryout_serious_slider;
		$map = array();
		foreach ( array_keys( $cryout_serious_slider->option_choices ) as $key ) {
			// turn 'caption_width' into 'CaptionWidth', everything else is already a single word
			$override_id = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $key ) ) );
			$map[ 'override' . $override_id ] = $key;
		}
		return $map;
	} // get_override_map()

	/* retrieves first slide image for preview in the block editor */
	private function get_first_thumb( $term_id ) {
		global $cryout_serious_slider;
		$query = new WP_Query( array(
			'post_type'      => $cryout_serious_slider->posttype,
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'tax_query'      => array( array(
				'taxonomy' => $cryout_serious_slider->taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_id,
			) ),
		) );
		if ( $query->have_posts() ) {
			$thumb_id = get_post_thumbnail_id( $query->posts[0]->ID );
			return $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
		}
		return '';
	} // get_first_thumb()

	/* self explanatory */
	public function register_blocks() {
		$attributes = array(
			'sliderId'                 => array( 'type' => 'integer', 'default' => 0 ),
			'sliderName'               => array( 'type' => 'string',  'default' => '' ),
		);
		foreach ( array_keys( $this->get_override_map() ) as $attr_key ) {
			$attributes[ $attr_key ] = array( 'type' => 'string', 'default' => self::nooverride );
		};
		
		register_block_type( 'cryout-serious-slider/slider', array(
			'render_callback' => array( $this, 'render_block' ),
			'attributes'      => $attributes,
		) );
	} // register_blocks()

	/* render callback function */
	public function render_block( $attributes ) {
		global $cryout_serious_slider_shortcode;

		$slider_id = ! empty( $attributes['sliderId'] ) ? (int) $attributes['sliderId'] : 0;
		if ( ! $slider_id ) return '';

		if ( is_null( $cryout_serious_slider_shortcode ) ) return '';

		$attr = array( 'id' => $slider_id );
		foreach ( $this->get_override_map() as $attr_key => $option_key ) {
			$val = isset( $attributes[ $attr_key ] ) ? $attributes[ $attr_key ] : self::nooverride;
			if ( $val !== self::nooverride && $val !== '' ) {
				$attr[ $option_key ] = $val;
			}
		}

		return $cryout_serious_slider_shortcode->shortcode_render( $attr );
	} // render_block()

	/**
	 * build clean defaults array for JS: strip the 'cryout_serious_slider_' prefix
	 * and cast numeric values to strings so JS gets consistent string types.
	 */
	private function get_js_defaults() {
		global $cryout_serious_slider;
		$js_defaults = array();
		foreach ( $cryout_serious_slider->defaults as $key => $value ) {
			$short_key = str_replace( 'cryout_serious_slider_', '', $key );
			$js_defaults[ $short_key ] = (string) $value;
		}
		return $js_defaults;
	} // get_js_defaults()

	/**
	 * generic, panel-level ui strings for the block editor and block-only strings not reused elsewhere
	 */
	private function get_field_labels() {
		return array(
			// panel titles
			'panelGeneral'      => __( 'General',    'cryout-serious-slider' ),
			'panelAppearance'   => __( 'Appearance', 'cryout-serious-slider' ),
			'panelAnimation'    => __( 'Animation',  'cryout-serious-slider' ),
			'panelSlider'       => __( 'Slider',     'cryout-serious-slider' ),
			// ui strings
			'selectSlider'      => __( 'Select Slider',              'cryout-serious-slider' ),
			'createSlider'      => __( 'Create new slider',          'cryout-serious-slider' ),
			'shortcodeHint'     => __( 'Shortcode:',                 'cryout-serious-slider' ),
			'overrideDesc'      => __( 'Override the pre-configured slider options for this local instance below', 'cryout-serious-slider' ),
			'editSlides'        => __( 'Edit slides',                'cryout-serious-slider' ),
			'manageSlider'      => __( 'Manage slider',              'cryout-serious-slider' ),
			'optionsCustomized' => __( 'Slider instance customized locally', 'cryout-serious-slider' ),
			'resetAll'          => __( 'Reset all',                  'cryout-serious-slider' ),
			'loadingPreview'    => __( 'Loading preview...',      	 'cryout-serious-slider' ),
			'restError'         => __( 'Could not load slider data from REST API.', 'cryout-serious-slider' ),
			'apiError'          => __( 'REST API error.',            'cryout-serious-slider' ),
			'sliderNotFound'    => __( 'Slider not found.',          'cryout-serious-slider' ),
			'selectPrompt'      => __( 'Select a slider from the settings panel on the right.', 'cryout-serious-slider' ),
			'slides'            => __( 'slides',                     'cryout-serious-slider' ),
			'slide'             => __( 'Slide',                      'cryout-serious-slider' ),
			'editSlide'         => __( 'Edit',                       'cryout-serious-slider' ),
			'global'            => __( 'global',                     'cryout-serious-slider' ),
			'globalPrefix'      => __( 'Global',                     'cryout-serious-slider' ),
			'sliderSetting'     => __( 'slider setting',             'cryout-serious-slider' ),
			'close'             => __( 'Close',                      'cryout-serious-slider' ),
			'reset'             => __( 'Reset',                      'cryout-serious-slider' ),
			'blockTitle'        => __( 'Serious Slider',             'cryout-serious-slider' ),
			'blockDescription'  => __( 'Insert an existing Serious Slider into the content.', 'cryout-serious-slider' ),
		);
	} // get_field_labels()

	/* block editor assets */
	public function enqueue_editor_resources() {
		global $cryout_serious_slider; // access some variables from main class

		wp_register_script(
			'cryout-serious-slider-block',
			$cryout_serious_slider->plugin_url . 'resources/block.js',
			array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n' ), // dependencies
			$cryout_serious_slider->version,
			true // not in footer
		);
		wp_localize_script( 'cryout-serious-slider-block', 'CRYOUT_SLIDER_BLOCK_PARAMS', array(
			'restUrl'       => esc_url_raw( rest_url( $this->rest_endpoint ) ),
			'restNonce'     => wp_create_nonce( 'wp_rest' ),
			'pluginUrl'     => $cryout_serious_slider->plugin_url,
			'noOverride'    => self::nooverride,
			'adminUrl'      => esc_url_raw( admin_url() ),
			'posttype'      => $cryout_serious_slider->posttype,
			'taxonomy'      => $cryout_serious_slider->taxonomy,
			'defaultcolor'  => $cryout_serious_slider->defaults['cryout_serious_slider_accent'],
			'sliderDefaults' => $this->get_js_defaults(),
			'optionChoices'  => $cryout_serious_slider->option_choices,
			'fieldLabels'    => $this->get_field_labels(),
		) );
		wp_enqueue_script( 'cryout-serious-slider-block' );

		wp_enqueue_style( 'cryout-serious-slider-block-editor', $cryout_serious_slider->plugin_url . 'resources/block-editor.css', array(), $cryout_serious_slider->version );
		wp_enqueue_style( 'cryout-serious-slider-style', $cryout_serious_slider->plugin_url . 'resources/style.css', array(), $cryout_serious_slider->version );
		
	} // enqueue_editor_resources()

} // class

new Cryout_Serious_Slider_Blocks;

// FIN
