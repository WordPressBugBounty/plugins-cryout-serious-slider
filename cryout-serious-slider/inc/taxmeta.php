<?php 

// Exit if accessed directly 
if ( !defined( 'ABSPATH' ) ) exit;  
?>

<div class="seriousslider-media">
	<div class="seriousslider-media-container"></div>
	<a href="#" class="button" id="seriousslider-media"><?php esc_html_e( 'Select Images', 'cryout-serious-slider' ) ?></a>
</div>

<div class="seriousslider-new-slider-button">
	<a id="new-slider-button" class="button button-primary">
		<?php esc_html_e( 'Continue', 'cryout-serious-slider' ) ?>
	</a>
</div>

<div class="seriousslider-new-slider-wrapper" style="display: none;">

<div id="seriousslider-tabs">
	<ul>
		<li><a href="#general"><?php esc_html_e( 'General', 'cryout-serious-slider' ) ?></a></li>
		<li><a href="#appearance"><?php esc_html_e( 'Appearance', 'cryout-serious-slider' ) ?></a></li>
		<li><a href="#animation"><?php esc_html_e( 'Animation', 'cryout-serious-slider' ) ?></a></li>
	</ul>


	<div id="general">
		<input id="cryout_serious_slider_imagelist" class="cryout-serious-slider-imagelist" name="cryout_serious_slider_imagelist" type="hidden" value="">

		<?php

		$this->selectfield(
			'term_meta[cryout_serious_slider_sort]',
			array( 
				'date' => __('by date','cryout-serious-slider'), 
				'order' => __('by order value','cryout-serious-slider'),
				'rand' => __('random','cryout-serious-slider'),
			),
			$the_meta['cryout_serious_slider_sort'],
			__('Sort Order','cryout-serious-slider'),
			'',
			'short'
		);
		$this->selectfield(
			'term_meta[cryout_serious_slider_sizing]',
			array( 0 => __('Adapt to images','cryout-serious-slider'), 1 => __('Contain Images','cryout-serious-slider'), 2 => __('Force Images to Size','cryout-serious-slider') ),
			$the_meta['cryout_serious_slider_sizing'],
			__('Slider Size','cryout-serious-slider'),
			'',
			'short'
		);
		$this->inputfield(
			'term_meta[cryout_serious_slider_width]',
			$the_meta['cryout_serious_slider_width'],
			__('Width','cryout-serious-slider'),
			'',
			'short',
			'px'
		);
		$this->inputfield(
			'term_meta[cryout_serious_slider_height]',
			$the_meta['cryout_serious_slider_height'],
			__('Height','cryout-serious-slider'),
			'',
			'short',
			'px'
		);
		$this->selectfield(
			'term_meta[cryout_serious_slider_responsiveness]',
			array( 'legacy' => __('Legacy Resize','cryout-serious-slider'), 'maintain' => __('Maintain Height','cryout-serious-slider') ),
			$the_meta['cryout_serious_slider_responsiveness'],
			__('Responsiveness','cryout-serious-slider'),
			'',
			'short'
		);
		$this->selectfield(
			'term_meta[cryout_serious_slider_hidetitles]',
			array( 0 => __('Show titles','cryout-serious-slider'), 1 => __('Hide titles','cryout-serious-slider') ),
			$the_meta['cryout_serious_slider_hidetitles'],
			__('Hide Caption Titles','cryout-serious-slider'),
			'',
			'short'
		);		?>
	</div><!--#general-->

	<div id="appearance">
		<?php
		$this->selectfield(
			'term_meta[cryout_serious_slider_theme]',
			array(	'light' 	=> __( 'Light', 'cryout-serious-slider' ),
					'light2' 	=> __( 'Light 2', 'cryout-serious-slider' ),
					'dark' 		=> __( 'Dark', 'cryout-serious-slider' ),
					'dark2' 	=> __( 'Dark 2', 'cryout-serious-slider' ),
					'square'	=> __( 'Square', 'cryout-serious-slider' ),
					'tall'		=> __( 'Tall', 'cryout-serious-slider' ),
					'captionleft'	=> __( 'Caption Left', 'cryout-serious-slider' ),
					'captionbottom'	=> __( 'Caption Bottom', 'cryout-serious-slider' ),
					'theme' 	=> __( 'Cryout Theme', 'cryout-serious-slider' )
			),
			$the_meta['cryout_serious_slider_theme'],
			__('Style','cryout-serious-slider'),
			'',
			'short'
		);
		$this->selectfield(
			'term_meta[cryout_serious_slider_shadow]',
			array(	'none' 		=> __( 'None', 'cryout-serious-slider' ),
					'level1' 	=> __( 'Level 1', 'cryout-serious-slider' ),
					'level2' 	=> __( 'Level 2', 'cryout-serious-slider' ),
					'level3' 	=> __( 'Level 3', 'cryout-serious-slider' ),
					'sharp' 	=> __( 'Sharp', 'cryout-serious-slider' ),
					'diffuse'	=> __( 'Diffuse', 'cryout-serious-slider' ),
					'dreamy'	=> __( 'Dreamy', 'cryout-serious-slider' ),
					'shorter'	=> __( 'Shorter', 'cryout-serious-slider' ),
					'longer'	=> __( 'Longer', 'cryout-serious-slider' )
			),
			$the_meta['cryout_serious_slider_shadow'],
			__('Shadow','cryout-serious-slider'),
			'',
			'short'
		);
		$this->selectfield(
			'term_meta[cryout_serious_slider_overlay]',
			array(  0 => __('Always hidden', 'cryout-serious-slider'),
					1 => __('Appear on hover','cryout-serious-slider'),
					2 => __('Always visible','cryout-serious-slider')
			),
			$the_meta['cryout_serious_slider_overlay'],
			__('Bullets and Navigation','cryout-serious-slider'),
			'',
			'short'
		);
		$this->inputfield(
			'term_meta[cryout_serious_slider_textsize]',
			$the_meta['cryout_serious_slider_textsize'],
			__('Base Font Size','cryout-serious-slider'),
			'',
			'short',
			'em',
			'step="0.05"'
		);

		$this->selectfield(
			'term_meta[cryout_serious_slider_align]',
			array(  'left' => __('Left', 'cryout-serious-slider'),
					'center' => __('Center','cryout-serious-slider'),
					'right' => __('Right','cryout-serious-slider'),
					'justify' => __('Justify','cryout-serious-slider'),
			),
			$the_meta['cryout_serious_slider_align'],
			__('Caption Alignment','cryout-serious-slider'),
			'',
			'short'
		);
		$this->inputfield(
			'term_meta[cryout_serious_slider_caption_width]',
			$the_meta['cryout_serious_slider_caption_width'],
			__('Caption Width','cryout-serious-slider'),
			'',
			'short',
			'px'
		);
		$this->selectfield(
			'term_meta[cryout_serious_slider_textstyle]',
			array(  'none' => __('None', 'cryout-serious-slider'),
					'textshadow' => __('Text Shadow','cryout-serious-slider'),
					'bgcolor' => __('Background Color','cryout-serious-slider'),
			),
			$the_meta['cryout_serious_slider_textstyle'],
			__('Text Style','cryout-serious-slider'),
			'',
			'short'
		);
		$this->inputfield(
			'term_meta[cryout_serious_slider_accent]',
			$the_meta['cryout_serious_slider_accent'],
			__('Accent Color','cryout-serious-slider'),
			'',
			'short',
			'',
			'data-default-color="'.$the_meta['cryout_serious_slider_accent'].'"',
			'' // type workaround for "text" inputs clear
		); ?>
	</div><!--appearance-->

	<div id="animation">
		<?php
		$this->selectfield(
			'term_meta[cryout_serious_slider_autoplay]',
			array( 1 => __('Enabled','cryout-serious-slider'), 0 => __('Disabled','cryout-serious-slider') ),
			$the_meta['cryout_serious_slider_autoplay'],
			__('Autoplay','cryout-serious-slider'),
			'',
			'short'
		);
		$this->selectfield(
			'term_meta[cryout_serious_slider_animation]',
			array(
				'fade' => __('Fade','cryout-serious-slider'),
				'slide' => __('Slide','cryout-serious-slider'),
				'overslide' => __('Overslide','cryout-serious-slider'),
				'underslide' => __('Underslide','cryout-serious-slider'),
				'parallax' => __('Parallax','cryout-serious-slider'),
				'hflip' => __('Horizontal Flip','cryout-serious-slider'),
				'vflip' => __('Vertical Flip','cryout-serious-slider'),
			),
			$the_meta['cryout_serious_slider_animation'],
			__('Transition Effect','cryout-serious-slider'),
			'',
			'short'
		);
		$this->selectfield(
			'term_meta[cryout_serious_slider_hover]',
			array( 'hover' => __('Enabled','cryout-serious-slider'), 'false' => __('Disabled','cryout-serious-slider') ),
			$the_meta['cryout_serious_slider_hover'],
			__('Transition Pause on Hover','cryout-serious-slider'),
			'',
			'short'
		);
		$this->inputfield(
			'term_meta[cryout_serious_slider_delay]',
			$the_meta['cryout_serious_slider_delay'],
			__('Transition Delay','cryout-serious-slider'),
			'',
			'short',
			'ms'
		);
		$this->inputfield(
			'term_meta[cryout_serious_slider_transition]',
			$the_meta['cryout_serious_slider_transition'],
			__('Transition Duration','cryout-serious-slider'),
			'',
			'short',
			'ms'
		);
		$this->selectfield(
			'term_meta[cryout_serious_slider_captionanimation]',
			array(
				'none' => __('None','cryout-serious-slider'),
				'fade' => __('Fade','cryout-serious-slider'),
				'slide' => __('Slide','cryout-serious-slider'),
				'blur' => __('Blur','cryout-serious-slider'),
				'zoomin' => __('Zoom In','cryout-serious-slider'),
				'zoomout' => __('Zoom Out','cryout-serious-slider'),
			),
			$the_meta['cryout_serious_slider_captionanimation'],
			__('Caption Text Animation','cryout-serious-slider'),
			'',
			'short'
		);	?>
	</div><!--animation-->

</div><!--#seriousslider-tabs-->

<?php wp_nonce_field( 'cryout_serious_slider_taxmeta', 'cryout_serious_slider_taxmeta_nonce' ); ?>

</div><!--seriousslider-new-slider-wrapper-->
<br>
