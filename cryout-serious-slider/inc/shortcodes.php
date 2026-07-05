<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* The shortcode class */
class Cryout_Serious_Slider_Shortcode {

	public $shortcode_tag = 'serious-slider';
	private $id = 0;
	private $cid = 0;
	private $custom_style = array();
	private $custom_script = array();
	private $butts = 2;
	private $sanitizer = NULL;

	function __construct($args = array()){
		//register shortcode
		add_shortcode( $this->shortcode_tag, array( $this, 'shortcode_render' ) );
		$this->butts = apply_filters( 'cryout_serious_slider_buttoncount', $this->butts );

		include_once( plugin_dir_path(__FILE__) . '/helpers.php' );
		$this->sanitizer = new Cryout_Serious_Slider_Sanitizers;

		add_action( 'wp_footer', array( $this, 'shortcode_style'  ) );
		// js is attached by shortcode_render
	}

	function shortcode_style() {
		if ( empty( $this->custom_style ) ) return;
		$css = preg_replace( '/([\n\s])+/', ' ', wp_strip_all_tags( implode( PHP_EOL, $this->custom_style ) ) );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<style type="text/css">/* cryout serious slider styling */ %s</style>', $css );
	} // shortcode_style()

	function shortcode_render($attr) {

		global $cryout_serious_slider;

		// exit silently if slider id is not defined
		if ( empty($attr['id'])) { return; } 
		
		$sid = intval($attr['id']); 									// slider cpt tax id from backend
		$cid = sprintf( '%d-r%.4d', abs($sid), wp_rand(100,999) );		// slider div id on frontend (includes random number for uniqueness)

		$options = apply_filters('cryout_serious_slider_shortcode_attributes', $this->shortcode_options( $sid ), $attr, $sid);
		extract($options);

		if (!empty($attr['count'])) 		$count = intval($attr['count']); else $count = 100; // use a sane failsafe

		// allow shortcode attributes to override configured options
		if (!empty($attr['width'])) 		$width = absint( $attr['width'] );
		if (!empty($attr['height'])) 		$height = absint( $attr['height'] );
		if (!empty($attr['responsiveness'])) $responsiveness = sanitize_text_field( $attr['responsiveness'] );
		if (!empty($attr['theme'])) 		$theme = sanitize_text_field( $attr['theme'] );
		if (!empty($attr['align'])) 		$align = sanitize_text_field( $attr['align'] );
		if (!empty($attr['textstyle'])) 	$textstyle = sanitize_text_field( $attr['textstyle'] );
		if (!empty($attr['accent'])) 		$accent = sanitize_text_field( $attr['accent'] );
		if (!empty($attr['animation'])) 	$animation = sanitize_text_field( $attr['animation'] );
		if (!empty($attr['hover'])) 		$hover = sanitize_text_field( $attr['hover'] );
		if (!empty($attr['delay'])) 		$delay = intval( $attr['delay'] );
		if (!empty($attr['transition'])) 	$transition = intval( $attr['transition'] );
		if (!empty($attr['textsize'])) 		$textsize = floatval( $attr['textsize'] );
		if (!empty($attr['hidetitles'])) 	$hidetitles = true; // else value from defaults
		if (!empty($attr['hidecaption'])) 	$hidecaption = true; // else value from defaults
		if (!empty($attr['autoplay'])) 		$autoplay = sanitize_text_field($attr['autoplay']);
		
		// shortcuts for basic sorting
		$allowed_sort = array( 'date', 'order', 'rand' );
		if ( !empty($attr['sort']) && in_array( strtolower( $attr['sort'] ), $allowed_sort ) )
											$sort = sanitize_text_field($attr['sort']);

		switch ($sort) {
			case 'order':
			// sort by order param
			$orderby = 'menu_order';
			$order = 'ASC';
			break;
		case 'rand':
			// sort by order param
			$orderby = 'rand';
			$order = 'ASC';
			break;
			case 'date':
		default:
			// sort by publish date (default)
			$orderby = 'date';
			$order = 'DESC';
			break;
		} // switch

		// or specific order controls
		$allowed_orderby = array( 'none', 'ID', 'author', 'title', 'name', 'date', 'modified', 'rand', 'menu_order' );
		if ( !empty( $attr['orderby'] ) && in_array( strtolower( $attr['orderby'] ), $allowed_orderby ) )
											$orderby = sanitize_text_field( $attr['orderby'] );
		$allowed_order = array( 'asc', 'desc' );
		if ( !empty( $attr['order'] ) && in_array( strtolower( $attr['order'] ), $allowed_order ) )
											$order = sanitize_text_field( $attr['order'] );

		$slider_classes = array();
		$slider_classes[] = 'seriousslider-overlay' . $overlay;
		$slider_classes[] = 'seriousslider-' . $theme;
		$slider_classes[] = 'seriousslider-shadow-' . $shadow;
		$slider_classes[] = 'seriousslider-responsive-' . $responsiveness;
		$slider_classes[] = 'seriousslider-hidetitles-' . $hidetitles;
		$slider_classes[] = 'seriousslider-' . $animation;
		$slider_classes[] = 'seriousslider-sizing' . $sizing;
		$slider_classes[] = 'seriousslider-align' . $align;
		$slider_classes[] = 'seriousslider-caption-animation-' . $captionanimation;
		$slider_classes[] = 'seriousslider-textstyle-' . $textstyle;
		$slider_classes = implode(' ', $slider_classes);

		$the_query = new WP_Query(
			array(
				'post_type' => array( $cryout_serious_slider->posttype ),
				'order' => $order,
				'orderby' => $orderby,
				'showposts' => $count,
					'tax_query' => array(
					array(
						'taxonomy' => $cryout_serious_slider->taxonomy,
						'field'    => 'term_id',
						'terms'    => array( $sid ),
					),
				),
			)
		);

		$counter = 0;
		$this->id = $sid;
		$this->cid = $cid;

		$accent_clean  = esc_html( $this->sanitizer->color_clean( $accent ) );
		$accent_rgb    = esc_html( $this->sanitizer->hex2rgb( $accent ) );
		$transition_s  = esc_html( round( intval( $transition ) / 1000, 2 ) ) . 's';
		$cid_attr      = esc_attr( $cid );
			
		// instance-specific styling
		$theme_css = '';
		switch ( $theme ) {
			case 'light':
				$theme_css = "

				/* Light */
				.serious-slider-{$cid_attr}.seriousslider-light .seriousslider-caption-buttons a:nth-child(2n+1),
				.serious-slider-{$cid_attr}.seriousslider-light .seriousslider-caption-buttons a:hover:nth-child(2n) { color: {$accent_clean}; }
				.serious-slider-{$cid_attr}.seriousslider-light .seriousslider-caption-buttons a:hover:nth-child(2n+1) { background-color: {$accent_clean}; border-color: {$accent_clean}; color: inherit; }";
				break;
			case 'dark':
				$theme_css = "

				/* Dark */
				.serious-slider-{$cid_attr}.seriousslider-dark .seriousslider-caption-buttons a:nth-child(2n) { color: {$accent_clean}; }
				.serious-slider-{$cid_attr}.seriousslider-dark .seriousslider-caption-buttons a:hover:nth-child(2n+1) { border-color: #FFF; }
				.serious-slider-{$cid_attr}.seriousslider-dark .seriousslider-caption-buttons a:hover:nth-child(2n) { border-color: {$accent_clean}; }
				.serious-slider-{$cid_attr}.seriousslider-dark .seriousslider-caption-buttons a:nth-child(2n+1) { background-color: {$accent_clean}; border-color: {$accent_clean}; }";
				break;
			case 'dark2':
				$theme_css = "

				/* Dark2 */
				.serious-slider-{$cid_attr}.seriousslider-dark2 .seriousslider-caption-buttons a:nth-child(2n) { color: {$accent_clean}; }
				.serious-slider-{$cid_attr}.seriousslider-dark2 .seriousslider-caption-buttons a:hover:nth-child(2n+1) { border-color: #222; }
				.serious-slider-{$cid_attr}.seriousslider-dark2 .seriousslider-caption-buttons a:hover:nth-child(2n) { border-color: {$accent_clean}; }
				.serious-slider-{$cid_attr}.seriousslider-dark2 .seriousslider-caption-buttons a:nth-child(2n+1) { background-color: {$accent_clean}; border-color: {$accent_clean}; }";
				break;
			case 'square':
				$theme_css = "

				/* Square */
				.serious-slider-{$cid_attr}.seriousslider-square .seriousslider-caption-buttons a:nth-child(2n+1) { background-color: {$accent_clean}; }
				.serious-slider-{$cid_attr}.seriousslider-square .seriousslider-caption-buttons a:nth-child(2n) { background: #fff; color: {$accent_clean}; }
				.serious-slider-{$cid_attr}.seriousslider-square .seriousslider-caption-buttons a:hover:nth-child(2n+1) { color: {$accent_clean}; background: #FFF; }
				.serious-slider-{$cid_attr}.seriousslider-square .seriousslider-caption-buttons a:hover:nth-child(2n) { color: #fff; background-color: {$accent_clean}; }";
				break;
			case 'tall':
				$theme_css = "

				/* Tall */
				.serious-slider-{$cid_attr}.seriousslider-tall .seriousslider-caption-buttons a:nth-child(2n+1) { background-color: {$accent_clean}; }
				.serious-slider-{$cid_attr}.seriousslider-tall .seriousslider-caption-buttons a:nth-child(2n) { background: #FFF; color: {$accent_clean}; }
				.serious-slider-{$cid_attr}.seriousslider-tall .seriousslider-caption-buttons a:hover { opacity: 0.8; }";
				break;
			case 'captionleft':
				$theme_css = "

				/* Caption Left */
				.serious-slider-{$cid_attr}.seriousslider-captionleft .seriousslider-caption-buttons a:hover { color: {$accent_clean}; }

				/* Caption Bottom */
				.serious-slider-{$cid_attr}.seriousslider-captionbottom .seriousslider-caption-buttons a:hover { }";
				break;
			case 'captionbottom':
				$theme_css = "
				/* Caption Bottom */
				.serious-slider-{$cid_attr}.seriousslider-captionbottom .seriousslider-caption-buttons a:hover { }";
				break;
			} // switch($theme)

		$this->custom_style[] =
			":root{
				--serious-slider-{$cid_attr}-color-accent: {$accent_clean};
				--serious-slider-{$cid_attr}-color-theme: '{$theme}';
				--serious-slider-{$cid_attr}-width: " . intval( $width ) . "px;
				--serious-slider-{$cid_attr}-height: " . intval( $height ) . "px;
			}
			.serious-slider-{$cid_attr} { max-width: var(--serious-slider-{$cid_attr}-width); }
			.serious-slider-{$cid_attr}.seriousslider-sizing1, .serious-slider-{$cid_attr}.seriousslider-sizing1 img { max-height: var(--serious-slider-{$cid_attr}-height); }
			.serious-slider-{$cid_attr}.seriousslider-sizing2, .serious-slider-{$cid_attr}.seriousslider-sizing2 img.item-image { height: var(--serious-slider-{$cid_attr}-height); }
			.serious-slider-{$cid_attr} .seriousslider-caption-inside { max-width: " . intval( $caption_width ) . "px; font-size: " . esc_html( round( $textsize, 2 ) ) . "em; }
			.serious-slider-{$cid_attr} .seriousslider-inner > .item { -webkit-transition-duration: {$transition_s}; -o-transition-duration: {$transition_s}; transition-duration: {$transition_s}; }
			.serious-slider-{$cid_attr}.seriousslider-textstyle-bgcolor .seriousslider-caption-title span { background-color: rgba( {$accent_rgb}, 0.6); }
			/* Indicators */
			.serious-slider-{$cid_attr}.seriousslider-dark .seriousslider-indicators li.active,
			.serious-slider-{$cid_attr}.seriousslider-dark2 .seriousslider-indicators li.active,
			.serious-slider-{$cid_attr}.seriousslider-square .seriousslider-indicators li.active,
			.serious-slider-{$cid_attr}.seriousslider-tall .seriousslider-indicators li.active,
			.serious-slider-{$cid_attr}.seriousslider-captionleft .seriousslider-indicators li.active,
			.serious-slider-{$cid_attr}.seriousslider-captionbottom .seriousslider-indicators li.active { background-color: rgba( {$accent_rgb}, 0.8); }
			/* Arrows */
			.serious-slider-{$cid_attr}.seriousslider-dark .seriousslider-control:hover .control-arrow,
			.serious-slider-{$cid_attr}.seriousslider-dark2 .seriousslider-control:hover .control-arrow,
			.serious-slider-{$cid_attr}.seriousslider-square .seriousslider-control:hover .control-arrow,
			.serious-slider-{$cid_attr}.seriousslider-tall .seriousslider-control .control-arrow { background-color: rgba( {$accent_rgb}, 0.8); }
			.serious-slider-{$cid_attr}.seriousslider-tall .seriousslider-control:hover .control-arrow { color: rgba( {$accent_rgb}, 1); background-color: #FFF; }
			.serious-slider-{$cid_attr}.seriousslider-captionbottom .seriousslider-control .control-arrow,
			.serious-slider-{$cid_attr}.seriousslider-captionleft .seriousslider-control .control-arrow { color: rgba( {$accent_rgb}, .8); }
			.serious-slider-{$cid_attr}.seriousslider-captionleft .seriousslider-control:hover .control-arrow { color: rgba( {$accent_rgb}, 1); }
			/* Buttons */
			{$theme_css}";

		// instance-specific js attached to the enqueued slider script handle
		$instance_js = sprintf(
			"jQuery('#serious-slider-%s').carousel({interval:%s,pause:'%s',stransition:%d});",
			$cid_attr,
			$autoplay ? intval( $delay ) : 'false',
			esc_js( $hover ),
			intval( $transition )
		);
		wp_add_inline_script( 'cryout-serious-slider-script', $instance_js );

		if ( $the_query->have_posts() ):
		ob_start(); ?>
		<div id="serious-slider-<?php echo esc_attr( $cid ) ?>" class="cryout-serious-slider seriousslider serious-slider-<?php echo esc_attr( $cid ) ?> serious-slider-<?php echo intval( $sid ) ?> <?php echo wp_kses_data( $slider_classes ) ?>" data-ride="seriousslider">
			<div class="seriousslider-inner" role="listbox">

			<?php while ($the_query->have_posts()):
				$the_query->the_post();
				$counter++;

				// retrieve parameters
				$slide_meta = get_post_meta( get_the_ID() );

				if ( !empty($slide_meta['cryout_serious_slider_link'][0]) )
						$meta_link = ' href="' . esc_url($slide_meta['cryout_serious_slider_link'][0]) . '"';
						else $meta_link = '';
				if ( !empty($slide_meta['cryout_serious_slider_target'][0]) && $slide_meta['cryout_serious_slider_target'][0] )
						$meta_target = 'target="_blank"';
						else $meta_target = '';

				$meta_buttons = array();
				for ( $i = 1; $i <= $this->butts; $i++ ) {
					$meta_buttons[ $i ] = array(
						'label'  => ! empty( $slide_meta[ 'cryout_serious_slider_button' . $i ][0] ) ? sanitize_text_field( $slide_meta[ 'cryout_serious_slider_button' . $i ][0] ) : false,
						'url'    => ! empty( $slide_meta[ 'cryout_serious_slider_button' . $i . '_url' ][0] ) ? esc_url( $slide_meta[ 'cryout_serious_slider_button' . $i . '_url' ][0] ) : '',
						'target' => ! empty( $slide_meta[ 'cryout_serious_slider_button' . $i . '_target' ][0] ) ? 'target="_blank"' : '',
					);
				}

				$image_data = wp_get_attachment_image_src (get_post_thumbnail_ID( get_the_ID() ), 'full' );

				if ( !empty($sizing) && $sizing ) $sizes = 'width="' . $width . '" height="' . $height . '"'; else $sizes = '';

				$slide_title = get_the_title();
				$slide_text = get_the_content();

				?>

			<div class="item slide-<?php echo intval( $counter ) ?> <?php if ($counter==1) echo 'active' ?>" role="option">
				<?php if (!empty($image_data[0])): ?>
				<a <?php echo wp_kses_data( $meta_link ); ?> <?php echo wp_kses_data( $meta_target ); ?>>
					<img class="item-image" src="<?php echo esc_url( $image_data[0] ) ?>" alt="<?php the_title_attribute(); ?>" <?php echo wp_kses_data( $sizes ) ?>>
				</a>
				<?php endif; ?>
				<?php if (( !empty($slide_title) || !empty($slide_text) ) && !$hidecaption): ?>
				<div class="seriousslider-caption">
					<div class="seriousslider-caption-inside">
						<?php if (!empty($slide_title) && !$hidetitles) { ?><div class="seriousslider-caption-title"><span><?php the_title(); ?></span></div><?php } ?>
						<?php if (!empty($slide_text)) { ?><div class="seriousslider-caption-text"><?php echo wp_kses_post( strip_shortcodes( wpautop( $slide_text ) ) ) ?></div><?php } ?>
						<div class="seriousslider-caption-buttons">
							<?php for ( $i=1; $i<=$this->butts; $i++ ) { ?>
								<?php if ( !empty($meta_buttons[$i]['label']) ) { ?>
									<a class="seriousslider-button" href="<?php echo esc_url( $meta_buttons[$i]['url']) ?>" <?php echo wp_kses_data( $meta_buttons[$i]['target'] ) ?>><?php echo esc_attr( $meta_buttons[$i]['label'] ) ?></a>
								<?php } ?>
							<?php } ?>
						</div>
					</div><!--seriousslider-caption-inside-->
				</div><!--seriousslider-caption-->
				<?php endif; ?>
				<div class="seriousslider-hloader"></div>
				<figure class="seriousslider-cloader">
					<svg width="200" height="200">
						<circle cx="95" cy="95" r="20" transform="rotate(-90, 95, 95)"/>
					</svg>
			  </figure>
			</div>

			<?php endwhile; // $the_query->have_posts() ?>
			</div>

			<div class="seriousslider-indicators">
				<ol class="seriousslider-indicators-inside">
					<?php for ($i=0;$i<$counter;$i++) { ?>
					<li data-target="#serious-slider-<?php echo esc_attr( $cid ) ?>" data-slide-to="<?php echo intval($i) ?>" <?php if ($i==0) echo 'class="active"' ?> role="button"></li>
					<?php } ?>
				</ol>
			</div>

			<button class="left seriousslider-control" data-target="#serious-slider-<?php echo esc_attr( $cid ) ?>" role="button" data-slide="prev">
			  <span class="sicon-prev control-arrow" aria-hidden="true"></span>
			  <span class="sr-only"><?php esc_html_e('Previous Slide','cryout-serious-slider') ?></span>
			</button>
			<button class="right seriousslider-control" data-target="#serious-slider-<?php echo esc_attr( $cid ); ?>" role="button" data-slide="next">
			  <span class="sicon-next control-arrow" aria-hidden="true"></span>
			  <span class="sr-only"><?php esc_html_e('Next Slide','cryout-serious-slider') ?></span>
			</button>
		</div>
		<!-- end cryout serious slider <?php echo $cid ?> -->
		<?php
		wp_reset_postdata(); /* clean up the query */
		return ob_get_clean();
		endif; 

	} // shortcode_render()

	function shortcode_options($sid) {

		global $cryout_serious_slider;

		if (is_numeric($sid)) {
			$data = get_option( "cryout_serious_slider_{$sid}_meta" );
			$data = wp_parse_args( $data, $cryout_serious_slider->defaults );
		} else {
			$data = $cryout_serious_slider->defaults;
		}
		foreach ($data as $id=>$value){
			$options[str_replace('cryout_serious_slider_','',$id)] = $value;
		}
		return $options;
	} // shortcode_options()

} // class

/* Initialize the shortcode */
$cryout_serious_slider_shortcode = new Cryout_Serious_Slider_Shortcode;

/* FIN */
