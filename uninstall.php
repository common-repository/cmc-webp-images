<?php
/*
 * Clean up when uninstalling
 *
 * Removes all WP options set during configuration.
 * @since 1.0.0
 */

namespace CmcImages;

if (!defined('WP_UNINSTALL_PLUGIN')) {
  die;
}

require_once(__DIR__ . '/constants.php');
require_once(__DIR__ . '/includes/functions.php');

// Remove webp images
if (get_option(SLUG . '-delete-webp-on-uninstall')) {
  delete_webp_images();
}

// Delete stored options
$option_names = array(
  '-webp-images',
  '-image-quality',
  '-webp-in-theme',
  '-webp-in-plugins',
  '-cache-buster',
  '-conversion-stats',
  '-enable-webp',
  '-create-webp-images',
  '-delete-webp-on-uninstall',
);
foreach($option_names as $option_name) {
  delete_option(SLUG . $option_name);
}
remove_htaccess_mods();

