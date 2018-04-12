<?php
/**
 * @package WP_Custom_Attachment
 * @version 1.0
 */
/*
Plugin Name: WP Custom Attachment
Plugin URI: http://www.example.com
Description: This is a simple plugin for custom post attachment.
Author: Mahfuz
Version: 1.0
Author URI: http://www.example.com
*/

add_action('add_meta_boxes', 'add_custom_meta_boxes');
function add_custom_meta_boxes() {

	// Define the custom attachment for post
	add_meta_box(
		'wp_custom_attachment',
		'Custom Attachment',
		'wp_custom_attachment',
		'post',
		'side'
	);

	// Define the custom attachment for page
	add_meta_box(
		'wp_custom_attachment',
		'Custom Attahcment',
		'wp_custom_attachment',
		'page',
		'side'
	);
} // end of add_meta_boxes

function wp_custom_attachment() {
	wp_nonce_field( plugin_basename( __FILE__ ), 'wp_custom_attachment_nonce');

	$html = '<p class="description">';
		$html .= 'Upload your PDF here.';
	$html .= '</p>';
	$html .= '<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="25" />';

	echo $html;	
}

add_action( 'save_post', 'save_custom_meta_data' );
function save_custom_meta_data($id) {
	if( ! wp_verify_nonce($_POST['wp_custom_attachment_nonce'], plugin_basename( __FILE__ ) ) ) {
		return;
	}

	if(defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $id;
	} // end if

	if( 'page' == $_POST['post_type'] ) {
		if( ! current_user_can( 'edit_page', $id) ) {
			return $id;
		}// end if
	}else {
		if( ! current_user_can('edit_page', $id) ) {
			return $id;
		}// end if
	}
	/* end security verification */

	if( !empty($_FILES['wp_custom_attachment']['name'] ) ) {
		$supported_types = array('application/pdf');

		$arr_file_type = wp_check_filetype(basename($_FILES['wp_custom_attachment']['name']));
		$upload_type = $arr_file_type['type'];

		if( in_array($upload_type, $supported_types) ) {
			$upload = wp_upload_bits($_FILES['wp_custom_attachment']['name'], null, file_get_contents($_FILES['wp_custom_attachment']['tmp_name']) );

			if( isset($upload['error']) && $upload !== 0) {
				wp_die('There is an error uploading your file. The error is: ' . $upload['error']);
			}else {
				add_post_meta($id, 'wp_custom_attachment', $upload);
				update_post_meta($id, 'wp_custom_attachment', $upload);
			}
		}else {
			wp_die("The file type that you've upload is not a PDF");
		}
	}

} // end save_custom_meta_data

function update_edit_form() {
	echo 'enctype="multipart/form-data"';
}
add_action( 'post_edit_form_tag', 'update_edit_form' );