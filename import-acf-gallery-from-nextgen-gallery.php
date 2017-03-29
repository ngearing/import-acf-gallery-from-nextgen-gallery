<?php
/**
 * Plugin Name: Export Nextgen Galleries to ACF Gallery field
 */

class options_page {

    function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    function admin_menu() {
        add_options_page(
            'Page Title',
            'Circle Tree Login',
            'manage_options',
            'options_page_slug',
            array(
                $this,
                'settings_page'
            )
        );
    }

    function  settings_page() {
        echo 'This is the page content';
    }
}

new options_page;



// Get images from a Post's Gallery
global $nggdb;

$get_gall_id = get_field('photo_gallery', $post_id);
$img_ids = $nggdb->get_ids_from_gallery($get_gall_id[0]['ngg_id']);

$images = [];

foreach ($img_ids as $img_id) {
    $img = $nggdb->find_image($img_id);
    $image[] = $img->get_permalink();
}

$image_ids = [];
foreach ($images as $image) {
    $array = array( //array to mimic $_FILES
        'name'      => basename($image), //isolates and outputs the file name from its absolute path
        'type'      => wp_check_filetype($image), // get mime type of image file
        'tmp_name'  => $image, //this field passes the actual path to the image
        'error'     => 0, //normally, this is used to store an error, should the upload fail. but since this isnt actually an instance of $_FILES we can default it to zero here
        'size'      => filesize($image) //returns image filesize in bytes
    );

    $image_ids[] = media_handle_sideload($array, $post_id); //the actual image processing, that is, move to upload directory, generate thumbnails and image sizes and writing into the database happens here
}

//insert the ACF magic bits
$array = get_field('field_57d78cb42186f', $post_id, false);
if (is_array($array)) {
    $array = array();
}
$array = array_merge($array, $image_ids);
update_field('field_57d78cb42186f', $array, $post_id);
