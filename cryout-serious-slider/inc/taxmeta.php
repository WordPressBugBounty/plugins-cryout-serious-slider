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


		<input id="cryout_serious_slider_imagelist" class="cryout-serious-slider-imagelist" name="cryout_serious_slider_imagelist" type="hidden" value="">

		<?php

	$panels = array( 'general', 'appearance', 'animation' );
	foreach ( $panels as $panel ) { ?>
		<div id="<?php echo $panel ?>">

		<?php
		foreach ($this->option_choices as $option_id => $option_data ) {
			if ( $option_data['panel'] == $panel ) { // group by panel
				switch ($option_data['control']) {
					case 'select':
		$this->selectfield(
							sprintf( 'term_meta[cryout_serious_slider_%s]', $option_id ),
							array_combine( array_column( $option_data['choices'], 'value' ), array_column( $option_data['choices'], 'label' ) ),
							$the_meta[sprintf( 'cryout_serious_slider_%s', $option_id )],
							$option_data['label'],
			'',
			'short',
							!empty( $option_data['um'] ) ? $option_data['um'] : ''
		);

						break;
					case 'color':
		$this->inputfield(
							sprintf( 'term_meta[cryout_serious_slider_%s]', $option_id ),
							$the_meta[sprintf( 'cryout_serious_slider_%s', $option_id )],
							$option_data['label'],
			'',
			'short',
			'',
							sprintf( 'data-default-color="%s"', $the_meta[sprintf( 'cryout_serious_slider_%s', $option_id )] ),
			'' // type workaround for "text" inputs clear
		);
						break;				
					case 'number':
					default:
		$this->inputfield(
							sprintf( 'term_meta[cryout_serious_slider_%s]', $option_id ),
							$the_meta[sprintf( 'cryout_serious_slider_%s', $option_id )],
							$option_data['label'],
			'',
			'short',
							!empty( $option_data['um'] ) ? $option_data['um'] : ''
		);
						break;
				} // switch
			}
		} // foreach
		?>

		</div><!-- <?php echo $panel ?> -->
	<?php } ?>

</div><!--#seriousslider-tabs-->

<?php wp_nonce_field( 'cryout_serious_slider_taxmeta', 'cryout_serious_slider_taxmeta_nonce' ); ?>

</div><!--seriousslider-new-slider-wrapper-->
<br>
