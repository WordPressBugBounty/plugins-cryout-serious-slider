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
	}

	function shortcode_style() {

		$css = preg_replace( '/([\n\s])+/', ' ', wp_strip_all_tags( implode( PHP_EOL, $this->custom_style ) ) );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<style type="text/css">/* cryout serious slider styling */ %s</style>', $css );
	} // shortcode_slyle()

	function shortcode_script() {
		ob_start();
		?><script type="text/javascript">
			/* cryout serious slider script */
			jQuery(document).ready(function(){
		<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo implode( PHP_EOL, $this->custom_script ); ?>
			})
		</script><?php
		ob_end_flush();
	} // shortcode_script()

	function shortcode_render($attr) {

		global $cryout_serious_slider;

		// exit silently if slider id is not defined
		if ( empty($attr['id'])) { return; } 
		
		$sid = intval($attr['id']); 									// slider cpt tax id from backend
		$cid = sprintf( '%d-rnd%.4d', abs($sid), wp_rand(1000,9999) );	// slider div id on frontend (includes random number for uniqueness)

		$options = apply_filters('cryout_serious_slider_shortcode_attributes', $this->shortcode_options( $sid ), $attr, $sid);
		extract($options);


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
		default:
			// sort by publish date (default)
			$orderby = 'date';
			$order = 'DESC';
			break;
		} // switch

		if (!empty($attr['count'])) $count = intval($attr['count']); else $count = -1;

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
		$hidetitle = ( !empty($hidetitles) || !empty($attr['hidetitle']) );
		$hidecaption = ( !empty($hidecaption) || !empty($attr['hidecaption']) );
		if (!empty($attr['autoplay'])) 		$autoplay = sanitize_text_field($attr['autoplay']);

		// allow order override via shortcode
		if (!empty($attr['orderby'])) $orderby = esc_attr($attr['orderby']);

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
						'field'    => 'id',
						'terms'    => array( $sid ),
					),
				),
			)
		);

		$counter = 0;
		$this->id = $sid;
		$this->cid = $cid;

		ob_start(); ?>
			:root{
				--serious-slider-__CID__-color-accent: __ACCENT__;
				--serious-slider-__CID__-color-theme: '<?php echo $theme ?>';
				--serious-slider-__CID__-width: <?php echo intval( $width ); ?>px;
				--serious-slider-__CID__-height: <?php echo intval( $height ); ?>px;
			}
			
			.serious-slider-__CID__ { max-width: var(--serious-slider-__CID__-width); }
			.serious-slider-__CID__.seriousslider-sizing1, .serious-slider-__CID__.seriousslider-sizing1 img { max-height: var(--serious-slider-__CID__-height);  }
			.serious-slider-__CID__.seriousslider-sizing2, .serious-slider-__CID__.seriousslider-sizing2 img.item-image { height: var(--serious-slider-__CID__-height);  }
			.serious-slider-__CID__ .seriousslider-caption-inside { max-width: <?php echo intval($caption_width) ?>px;  font-size: <?php echo esc_html( round($textsize,2) ) ?>em; }

			.serious-slider-__CID__ .seriousslider-inner > .item {
				-webkit-transition-duration: __TRANSITION__;
				-o-transition-duration: __TRANSITION__;
				transition-duration: __TRANSITION__;
			}

			.serious-slider-__CID__.seriousslider-textstyle-bgcolor .seriousslider-caption-title span {
				background-color: rgba( __ACCENT_RGB__, 0.6);
			}

			/* Indicators */
			.serious-slider-__CID__.seriousslider-dark .seriousslider-indicators li.active,
			.serious-slider-__CID__.seriousslider-dark2 .seriousslider-indicators li.active,
			.serious-slider-__CID__.seriousslider-square .seriousslider-indicators li.active,
			.serious-slider-__CID__.seriousslider-tall .seriousslider-indicators li.active,
			.serious-slider-__CID__.seriousslider-captionleft .seriousslider-indicators li.active,
			.serious-slider-__CID__.seriousslider-captionbottom .seriousslider-indicators li.active {
				background-color: rgba( __ACCENT_RGB__, 0.8);
			}

			/* Arrows */
			.serious-slider-__CID__.seriousslider-dark .seriousslider-control:hover .control-arrow,
			.serious-slider-__CID__.seriousslider-dark2 .seriousslider-control:hover .control-arrow,
			.serious-slider-__CID__.seriousslider-square .seriousslider-control:hover .control-arrow,
			.serious-slider-__CID__.seriousslider-tall .seriousslider-control .control-arrow {
				background-color: rgba( __ACCENT_RGB__, 0.8);
			}

			.serious-slider-__CID__.seriousslider-tall .seriousslider-control:hover .control-arrow {
				color: rgba( __ACCENT_RGB__, 1);
				background-color: #FFF;
			}

			.serious-slider-__CID__.seriousslider-captionbottom .seriousslider-control .control-arrow,
			.serious-slider-__CID__.seriousslider-captionleft .seriousslider-control .control-arrow {
				color: rgba( __ACCENT_RGB__, .8);
			}

			.serious-slider-__CID__.seriousslider-captionleft .seriousslider-control:hover .control-arrow {
				color: rgba( __ACCENT_RGB__, 1);
			}

			/* Buttons */

			<?php switch ($theme) {
				case 'light': ?>

				/* Light */
				.serious-slider-__CID__.seriousslider-light .seriousslider-caption-buttons a:nth-child(2n+1),
				.serious-slider-__CID__.seriousslider-light .seriousslider-caption-buttons a:hover:nth-child(2n) {
					color: var(--serious-slider-__CID__-color-accent);
				}

				.serious-slider-__CID__.seriousslider-light .seriousslider-caption-buttons a:hover:nth-child(2n+1) {
					background-color: var(--serious-slider-__CID__-color-accent);
					border-color: var(--serious-slider-__CID__-color-accent);
					color: inherit;
				}

			<?php break;
				case 'dark': ?>

				/* Dark */
				.serious-slider-__CID__.seriousslider-dark .seriousslider-caption-buttons a:nth-child(2n) {
					color: var(--serious-slider-__CID__-color-accent);
				}

				.serious-slider-__CID__.seriousslider-dark .seriousslider-caption-buttons a:hover:nth-child(2n+1) {
					border-color: #FFF;
				}

				.serious-slider-__CID__.seriousslider-dark .seriousslider-caption-buttons a:hover:nth-child(2n) {
					border-color: var(--serious-slider-__CID__-color-accent);
				}

				.serious-slider-__CID__.seriousslider-dark .seriousslider-caption-buttons a:nth-child(2n+1)  {
					background-color: var(--serious-slider-__CID__-color-accent);
					border-color: var(--serious-slider-__CID__-color-accent);
				}
				
			<?php break;
				case 'dark2': ?>

				/* Dark2 */
				.serious-slider-__CID__.seriousslider-dark2 .seriousslider-caption-buttons a:nth-child(2n) {
					color: var(--serious-slider-__CID__-color-accent);
				}

				.serious-slider-__CID__.seriousslider-dark2 .seriousslider-caption-buttons a:hover:nth-child(2n+1) {
					border-color: #222;
				}

				.serious-slider-__CID__.seriousslider-dark2 .seriousslider-caption-buttons a:hover:nth-child(2n) {
					border-color: var(--serious-slider-__CID__-color-accent);
				}

				.serious-slider-__CID__.seriousslider-dark2 .seriousslider-caption-buttons a:nth-child(2n+1)  {
					background-color: var(--serious-slider-__CID__-color-accent);
					border-color: var(--serious-slider-__CID__-color-accent);
				}

			<?php break;
				case 'square': ?>

				/* Square */
				.serious-slider-__CID__.seriousslider-square .seriousslider-caption-buttons a:nth-child(2n+1) {
					background-color: var(--serious-slider-__CID__-color-accent);
				}

				.serious-slider-__CID__.seriousslider-square .seriousslider-caption-buttons a:nth-child(2n) {
					background: #fff;
					color: var(--serious-slider-__CID__-color-accent);
				}

				.serious-slider-__CID__.seriousslider-square .seriousslider-caption-buttons a:hover:nth-child(2n+1) {
					color: var(--serious-slider-__CID__-color-accent);
					background: #FFF;
				}

				.serious-slider-__CID__.seriousslider-square .seriousslider-caption-buttons a:hover:nth-child(2n) {
					color: #fff;
					background-color: var(--serious-slider-__CID__-color-accent);
				}

			<?php break;
				case 'tall': ?>

				/* Tall */
				.serious-slider-__CID__.seriousslider-tall .seriousslider-caption-buttons a:nth-child(2n+1) {
					background-color: var(--serious-slider-__CID__-color-accent);
				}

				.serious-slider-__CID__.seriousslider-tall .seriousslider-caption-buttons a:nth-child(2n) {
					background: #FFF;
					color: var(--serious-slider-__CID__-color-accent);
				}

				.serious-slider-__CID__.seriousslider-tall .seriousslider-caption-buttons a:hover {
					opacity: 0.8;
				}

			<?php break;
				case 'captionleft': ?>

				/* Caption Left */
				.serious-slider-__CID__.seriousslider-captionleft .seriousslider-caption-buttons a:hover {
					color: var(--serious-slider-__CID__-color-accent);
				}

			<?php
				case 'captionbottom': ?>

				/* Caption Bottom */
				.serious-slider-__CID__.seriousslider-captionbottom .seriousslider-caption-buttons a:hover {
				}

			<?php
				break;
				default: ?> /* <?php echo esc_html($theme) ?> */ <?php
				break;
			} // switch($theme)

		$this->custom_style[] = str_replace(
			array(
				'__CID__', 			// slider ctp id
				'__TRANSITION__', 	// animation transition duration (s)
				'__ACCENT__', 		// configured accent color, hex
				'__ACCENT_RGB__', 	// configured accent color, rgba
			),
			array(
				esc_attr( $cid ),
				esc_html( round(intval($transition)/1000,2) ) . 's',
				esc_html( $this->sanitizer->color_clean( $accent ) ),
				esc_html( $this->sanitizer->hex2rgb( $accent ) ),
			),
			ob_get_clean()
		);
		add_action( 'wp_footer', array($this, 'shortcode_style') );
		ob_start() ?>

			jQuery('#serious-slider-<?php echo esc_attr( $cid ) ?>').carousel({
				interval: <?php if ($autoplay) echo intval( $delay ); else echo 'false'; ?>,
				pause: <?php echo intval($hover) ?>,
				stransition: <?php echo intval($transition) ?>
			});

		<?php
		$this->custom_script[] = ob_get_clean();
		add_action( 'wp_footer', array($this, 'shortcode_script') );

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
						<?php if (!empty($slide_title) && !$hidetitle) { ?><div class="seriousslider-caption-title"><span><?php the_title(); ?></span></div><?php } ?>
						<?php if (!empty($slide_text)) { ?><div class="seriousslider-caption-text"><?php the_content() ?></div><?php } ?>
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

			<?php endwhile; ?>
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
		<?php
		wp_reset_postdata(); /* clean up the query */
		return ob_get_clean();
		endif; ?>
		<!-- end cryout serious slider -->
		<?php

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

/* Initialize the shortcode class */
$cryout_serious_slider_shortcode = new Cryout_Serious_Slider_Shortcode;

/* FIN */