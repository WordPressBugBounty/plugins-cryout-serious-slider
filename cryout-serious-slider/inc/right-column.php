<?php 

// Exit if accessed directly 
if ( !defined( 'ABSPATH' ) ) exit;

?>

	<div id="floater-right">
		<div class="col-wrap">
			<div class="form-wrap">
				<h3 class="hndle"> </h3>
				<div class="inside">
					<a id="cryout-manage-slides" class="button" href="edit.php?post_type=<?php echo esc_attr($this->posttype) . '&'. esc_attr($this->taxonomy) . '=' . esc_attr($term_slug)  ?>"> <?php esc_html_e('&laquo; Manage Slides &raquo;', 'cryout-serious-slider') ?></a>
					<h3><?php esc_html_e('Shortcode', 'cryout-serious-slider') ?></h3>
					<p><?php esc_html_e('Use the shortcode to include the slider in posts, pages or widgets', 'cryout-serious-slider') ?></p>
					<input type="text" readonly="readonly" value="[serious-slider id=<?php echo intval($term_ID) ?>]"><br>
					<br><hr>
					<h3><?php esc_html_e('Template', 'cryout-serious-slider') ?></h3>
					<p><?php esc_html_e('Use the PHP code to include the slider directly in files', 'cryout-serious-slider') ?></p>
					<textarea readonly="readonly" rows="3"><?php printf( "&lt;?php\n    echo do_shortcode( '[serious-slider id=%s]' );\n ?&gt;", intval( $term_ID ) ) ?></textarea>
				</div>
			</div>
		</div>
	</div>
