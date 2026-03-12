<?php 

// Exit if accessed directly 
if ( !defined( 'ABSPATH' ) ) exit;

?>

		<?php wp_nonce_field( 'cryout_serious_slider_meta_nonce', 'cryout_serious_slider_meta_nonce' ); ?>

		<?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
		<?php for ($i=1;$i<=$this->butts;$i++) { ?>
		<p>
			<label for="cryout_serious_slider_button<?php echo intval( $i ) ?>"><?php 
			/* translators: label for button number X */
			printf( esc_html__('Button %s Label:', 'cryout-serious-slider'), intval( $i ) ) 
			?></label>
			<input type="text" size="30" name="cryout_serious_slider_button<?php echo intval( $i ) ?>" id="cryout_serious_slider_button<?php echo intval( $i ) ?>" value="<?php echo esc_attr( $buttons[$i]['label'] ) ?>" />
			<span>&nbsp;&nbsp;</span>
			<label for="cryout_serious_slider_button<?php echo intval( $i ) ?>_url"><?php esc_html_e('Link URL:', 'cryout-serious-slider') ?></label>
			<input type="text" size="40" name="cryout_serious_slider_button<?php echo intval( $i ) ?>_url" id="cryout_serious_slider_button<?php echo intval( $i ) ?>_url" value="<?php echo esc_url( $buttons[$i]['url'] ) ?>" />
			<span>&nbsp;&nbsp;</span>
			<input type="checkbox" id="cryout_serious_slider_button<?php echo intval( $i ) ?>_target" name="cryout_serious_slider_button<?php echo intval( $i ) ?>_target" <?php checked( $buttons[$i]['target'] ); ?> />
			<label for="cryout_serious_slider_button<?php echo intval( $i ) ?>_target"><?php esc_html_e('Open in New Window', 'cryout-serious-slider') ?></label>
		</p>
		<?php } ?>
		
		<p>
			<label for="cryout_serious_slider_link"><?php esc_html_e('Image Link URL:', 'cryout-serious-slider') ?></label>
			<input type="text" size="60" name="cryout_serious_slider_link" id="cryout_serious_slider_link" value="<?php echo esc_attr( $text ); ?>" />
			<span>&nbsp;&nbsp;</span>
			<input type="checkbox" id="cryout_serious_slider_target" name="cryout_serious_slider_target" <?php checked( $checked ); ?> />
			<label for="cryout_serious_slider_target"><?php esc_html_e('Open in New Window', 'cryout-serious-slider') ?></label>
		</p>
		<p>	<em><?php esc_html_e('Leave fields empty to disable elements.', 'cryout-serious-slider') ?></em> </p>
