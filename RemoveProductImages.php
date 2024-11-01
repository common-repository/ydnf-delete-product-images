<?php
/**
 * Plugin Name: Remove Product Images
 * Plugin URI: https://zonanegonet.com/wp-plugin-RemoveProductImages
 * Description: This plugin removes the featured image and images from the gallery when removing a WooCommerce product. WARNING: This plugin is designed for when you want to permanently remove a product. This plugin deletes the images when sending to the trash, when restoring the product from the trash the images will not be restored, you will have to reassign the images.
 * Version: 1.1
 * Author: Yin Darwin Naranjo Flores
 * Author URI: https://zonanegonet.com
 * License: GPL2
 */
define('RPIYDNF_PLUGIN_NAME', 'Remove Product Images');

add_action('plugins_loaded', 'rpiydnf_plugin_init', 0);
function rpiydnf_plugin_init() {
    if (!rpiydnf_check_woocommerce()) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', 'rpiydnf_woocommerce_required');
    }
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rpiydnf_settings_links');
function rpiydnf_settings_links($actions) {
    $actions[] = '<a href="https://wordpress.org/support/plugin/ydnf-delete-product-images/reviews/#new-post">Rate ' . RPIYDNF_PLUGIN_NAME . '</a>';
    $actions[] = '<a href="https://zonanegonet.com" target="_blank">Website development?</a>';

    return $actions;
}

function rpiydnf_woocommerce_required($message, $errormsg = false) {
    if ($errormsg) {
        echo '<div id="message" class="error">';
    } else {
        echo '<div id="message" class="updated fade">';
    }

    echo '<p><strong>You must install and activate the WooCommerce plugin. The ', RPIYDNF_PLUGIN_NAME, ' plugin for WooCommerce will be disabled until this is corrected.</strong></p></div>';
}

register_activation_hook(__FILE__, 'rpiydnf_plugin_activate');
function rpiydnf_plugin_activate(){
    if (!rpiydnf_check_woocommerce()) {
        wp_die('Sorry, but this plugin requires Woocommerce to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}

add_action('trashed_post', 'rpiydnf_remove_images', 20, 1);
function rpiydnf_remove_images($post_id) {
    if (!rpiydnf_check_woocommerce()) {
        return;
    }

    # Get the ID
    $post_type = get_post_type( $post_id );

    # Don't run on other types of content
    if ($post_type != 'product') {
        return true;
    }

    # Get the ID of the featured image
    $post_thumbnail_id = get_post_thumbnail_id($post_id);

    # Delete featured image
    if ($post_thumbnail_id) {
        wp_delete_attachment( $post_thumbnail_id, true );
    }

    $gallery_ids = get_post_meta($post_id, '_product_image_gallery', true);

    if ($gallery_ids) {
        $attachment_ids = explode(',', $gallery_ids);

        foreach($attachment_ids as $attachment_id) {
            # Borrar adjuntos
            wp_delete_attachment($attachment_id, true);
        }

        update_post_meta($post_id, '_product_image_gallery', '');
    }
}

function rpiydnf_check_woocommerce() {
    return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
}