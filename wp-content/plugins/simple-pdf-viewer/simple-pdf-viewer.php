<?php 
/*
Plugin Name: Simple PDF Viewer
Description: This plugin allows you to view PDF documents with Google Document Viewer.
Version: 1.9
Author: WebArea | Vera Nedvyzhenko
*/

function s_pdf_admin_scripts(){
	wp_enqueue_style('s_pdf_iris_colorpicker', plugins_url('css/iris.min.css', __FILE__));
	wp_enqueue_style('s_pdf_admin_styles', plugins_url('css/admin-style.css', __FILE__));
	if(isset($_GET['page'])){
		if($_GET['page'] == 'simple_pdf_viewer'){
			wp_deregister_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui','//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js',array('jquery'));
			wp_enqueue_script('s_pdf_admin_iris_script', plugins_url('js/iris-admin.js', __FILE__));
		}
	}
	wp_enqueue_script('s_pdf_iris_colorpicker_scripts', plugins_url('js/iris.min.js', __FILE__));
	wp_enqueue_script('s_pdf_admin_script', plugins_url('js/admin-main.js', __FILE__));

	$s_pgf_docw = get_option('s_pdf_options')['doc_width'];
	$s_pgf_docw_percent = get_option('s_pdf_options')['doc_width_percent'];
	$s_pgf_doch = get_option('s_pdf_options')['doc_height'];
	$s_pgf_bttn = get_option('s_pdf_options')['bttn_text'];
	$s_pgf_bttn_color = get_option('s_pdf_options')['bttn_color'];

	wp_localize_script( 's_pdf_admin_script', 's_pgf_docw', $s_pgf_docw);
	wp_localize_script( 's_pdf_admin_script', 's_pgf_docw_percent', $s_pgf_docw_percent);
	wp_localize_script( 's_pdf_admin_script', 's_pgf_doch', $s_pgf_doch);
	wp_localize_script( 's_pdf_admin_script', 's_pgf_bttn', $s_pgf_bttn);
	wp_localize_script( 's_pdf_admin_script', 's_pgf_bttn_color', $s_pgf_bttn_color);
}

add_action('admin_enqueue_scripts', 's_pdf_admin_scripts');

function s_pdf_scripts(){
	wp_enqueue_style('s_pdf_styles', plugins_url('css/style.css', __FILE__));
	wp_enqueue_script('s_pdf_scripts', plugins_url('js/main.js', __FILE__));
}

add_action('login_enqueue_scripts', 's_pdf_scripts');
add_action( 'wp_enqueue_scripts', 's_pdf_scripts' );

define( 'SPDF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Add media button
function s_pdf_media_button(){
	echo '<a href="#" id="spdf-media-button" class="button">Add PDF</a>';
}

add_action('media_buttons', 's_pdf_media_button', 15);

// Add Sortcode
function s_pdf_shortcode($atts){
	extract(shortcode_atts(array(
		'url' => '',
		'download' => '',
		'width' => '',
		'height' => '',
	), $atts));

	$s_pgf_docw = get_option('s_pdf_options')['doc_width'];
	$s_pgf_docw_persent = get_option('s_pdf_options')['doc_width_percent'];
	$s_pgf_doch = get_option('s_pdf_options')['doc_height'];

	$s_pgf_bttn_style = get_option('s_pdf_options')['bttn_style'];

	$s_pgf_bttn_color = get_option('s_pdf_options')['bttn_color'];
	if($s_pgf_bttn_color == ''){
		$s_pgf_bttn_color = '#000';
	}

	if($s_pgf_bttn_style != ''){
		$spdf_button_style = 'background:' . $s_pgf_bttn_color . ';';
	}else{
		$spdf_button_style = '';
	}
	
	if($s_pgf_docw == ''){
		$s_pgf_docw = 400;
	}

	if($s_pgf_doch == ''){
		$s_pgf_doch = 500;
	}

	if($download != ''){
		$s_pgf_download_bttn_text = '<a class="s_pdf_download_link" href="'.$url.'" download><button style="'.$spdf_button_style.'" class="s_pdf_download_bttn">'.$download.'</button></a>';
	}else{
		$s_pgf_download_bttn_text = '';
	}

	if($width != '' && $height != ''){
		$s_pgf_custom_size = 'width: ' . $width . 'px; height:' . $height . 'px;';
		$find_percent = strpos($width, '%');
		if ($find_percent !== false) {
			$s_pgf_custom_size = 'width: 100%; height:' . $height . 'px;';
		}
	}else{
		$s_pgf_custom_size = 'width: ' . $s_pgf_docw . 'px; height:' . $s_pgf_doch . 'px;';
		if ($s_pgf_docw_persent == 1) {
			$s_pgf_custom_size = 'width: 100%; height:' . $s_pgf_doch . 'px;';
		}
	}

	$s_pdf_doc_viewer = '//docs.google.com/gview?embedded=true&url=';
	return '<iframe id="s_pdf_frame" src="' . $s_pdf_doc_viewer . $url . '" style="' . $s_pgf_custom_size . '" frameborder="0"></iframe>' . $s_pgf_download_bttn_text;
}

add_shortcode( 'googlepdf', 's_pdf_shortcode');

// Add Settings Page
function s_pdf_settings_page(){
	add_options_page( 'Simple PDF Viewer', 'Simple PDF Viewer', 'manage_options', 'simple_pdf_viewer', 's_pdf_settings_page_content' );
}

add_action('admin_menu', 's_pdf_settings_page');

function s_pdf_settings_page_content(){
	?>
	<div class="wrap s_pdf_wrap">
		<h2>Simple PDF Viewer Settings</h2>

		<form action="options.php" method="POST">
			<?php
				settings_fields( 's_pdf_option_group' );
				do_settings_sections( 's_pdf_settings_page' );
				submit_button();
			?>
		</form>
	</div>
	<?php
}

add_action('admin_init', 's_pdf_register_settings');

function s_pdf_register_settings(){
	register_setting( 's_pdf_option_group', 's_pdf_options');

	add_settings_section( 'section_width_id', 'Document Width', '', 's_pdf_settings_page' ); 
	add_settings_section( 'section_id', 'Other Settings', '', 's_pdf_settings_page' ); 

	add_settings_field('s_pdf_option_doc_width_percent', 'Document Width 100%', 's_pdf_option_doc_width_percent_fill', 's_pdf_settings_page', 'section_width_id' );
	add_settings_field('s_pdf_option_doc_width', 'Document Width (px)', 's_pdf_option_doc_width_fill', 's_pdf_settings_page', 'section_width_id' );
	add_settings_field('s_pdf_option_doc_height', 'Document Height (px)', 's_pdf_option_doc_height_fill', 's_pdf_settings_page', 'section_id' );
	add_settings_field('s_pdf_option_doc_bttn', 'Download Button Text', 's_pdf_option_doc_bttn_fill', 's_pdf_settings_page', 'section_id' );
	add_settings_field('s_pdf_option_doc_bttn_style', 'Use Custom Button Style', 's_pdf_option_doc_bttn_style_fill', 's_pdf_settings_page', 'section_id' );
	add_settings_field('s_pdf_option_doc_bttn_color', 'Download Button Color', 's_pdf_option_doc_bttn_color_fill', 's_pdf_settings_page', 'section_id' );
	add_settings_field('s_pdf_option_doc_viewer', 'Document Viewer Type', 's_pdf_option_doc_viewer_fill', 's_pdf_settings_page', 'section_id' );
}

function s_pdf_option_doc_width_fill(){
	$val = get_option('s_pdf_options');
	$val = $val['doc_width'];
	?>
	<input type="number" name="s_pdf_options[doc_width]" value="<?php echo esc_attr( $val ) ?>" />
	<?php
}

function s_pdf_option_doc_width_percent_fill(){
	$val = get_option('s_pdf_options')['doc_width_percent'];
	?>
	<input type="checkbox" name="s_pdf_options[doc_width_percent]" value="1"<?php checked( 1 == $val ); ?> />
	<?php
}

function s_pdf_option_doc_height_fill(){
	$val = get_option('s_pdf_options');
	$val = $val['doc_height'];
	?>
	<input type="number" name="s_pdf_options[doc_height]" value="<?php echo esc_attr( $val ) ?>" />
	<?php
}

function s_pdf_option_doc_bttn_fill(){
	$val = get_option('s_pdf_options');
	$val = $val['bttn_text'];
	?>
	<input type="text" name="s_pdf_options[bttn_text]" value="<?php echo esc_attr( $val ) ?>" />
	<?php
}

function s_pdf_option_doc_bttn_color_fill(){
	$val = get_option('s_pdf_options');
	$val = $val['bttn_color'];
	?>
	<div id="s_pdf_colorpicker_show"></div>
	<input type="text" id="s_pdf_colorpicker" name="s_pdf_options[bttn_color]" value="<?php echo esc_attr( $val ) ?>" />
	<?php
}

function s_pdf_option_doc_bttn_style_fill(){
	$val = get_option('s_pdf_options')['bttn_style'];
	?>
	<input type="checkbox" name="s_pdf_options[bttn_style]" value="1"<?php checked( 1 == $val ); ?> />
	<?php
}

function s_pdf_option_doc_viewer_fill(){
	?>
		<div class="prov">
			<div class="pro">
				<a href="https://sellfy.com/p/a3uM/" target="_blank">Get Pro Version</a>
			</div>
			<img src="<?php echo SPDF_PLUGIN_URL . 'img/mediaviewer.jpg' ?>" alt="">
		</div>
	<?php
}

?>
