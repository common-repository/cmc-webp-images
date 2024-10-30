<?php
/*
 * Hook plugin activation to show user link to settings page.
 */

namespace CmcImages;

defined( 'ABSPATH' ) || exit;

/*
 * Activation hook. Check deps and deactivate if necessary.
 *
 * @since 1.0.1
 */
if (!function_exists(__NAMESPACE__ . '\activation_hook')) {
  function activation_hook() {
    add_option(SLUG . '-activated', 1);
    if (!function_exists('imagecreatefrompng') || strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== 0) {
      die('The Apache webserver and <a href="https://www.php.net/manual/en/image.setup.php">PHP GD</a> are required to use this plugin.');
    }
  }
}

add_action('admin_notices', function() {
  if (!get_option(SLUG . '-activated')) { return; }
  delete_option(SLUG . '-activated');
  echo implode(array(
    '<div class="notice notice-info"><p>CMC Webp Images activated successfully. Go ',
    '<a href="',
    menu_page_url(SLUG . '-options', false),
    '">here</a> to configure your options and start serving webp images.',
    '</p></div>',
  ));
});
