<?php
/*
 * WordPress filters
 * @since 1.0.0
 */

namespace CmcImages;

defined('ABSPATH') || exit;

/*
 * Create webp images on the fly
 *
 * Uses the wp_get_attachment_url hook to check for and optionally create webp files in real time.
 * @since 1.0.0
 */
add_filter('wp_get_attachment_url', function($url) {
    if (!function_exists('imagecreatefrompng')) { return $url; }
    if (!get_option(SLUG . '-enable-webp')) { return $url; }

    $filePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($url)['path'];
    $quality = get_option(SLUG . '-image-quality') ?: 50;
    makeWebpImage($filePath, $quality);
    return $url;
});
