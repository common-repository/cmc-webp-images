<?php
/*
 * Compare original image to webp
 *
 * Called from admin page, this file will display the same image as a jpg and webp image side by side.
 * Expects a URL query variable, "quality" -- a number from 1 to 100.
 * Note that this file is called directly, outside of the WP environment.
 *
 */

namespace CmcImages;

if (!defined('ABSPATH')) { die(); }

if (!current_user_can('manage_options')) { 
  echo 'You do not have permissions to access this page.';
  return;
}

$filePath = __DIR__ . '/../classes/assets/img/quality-test.jpg';
$quality = @intval(@esc_attr(get_option(SLUG . '-image-quality')) ?: 50);

if (!ctype_digit($quality) || $quality < 1 || $quality > 100) {
  $quality = 50;
} 

$newFilePath = $filePath . '.webp';
$im = imagecreatefromjpeg($filePath);
imagewebp($im, $newFilePath, $quality);
$oldFileSize = round(filesize($filePath) / 1000) . 'K';
$newFileSize = round(filesize($newFilePath) / 1000) . 'K';
?><p>The quality test reflects the <i>saved</i> webp image quality.</p>
<div style="width: 100%;display:flex;flex-direction:row;justify-content:space-around;">
  <div>
    <h2>JPEG</h2>
    <p>Size: <?php echo esc_html($oldFileSize); ?></p>
    <img src="<?php echo plugin_dir_url(__FILE__); ?>../classes/assets/img/quality-test.jpg">
  </div>
  <div style="margin-left: 10px;">
    <h2>WEBP (quality = <?php echo esc_html($quality); ?>)</h2>
    <p>Size: <?php echo esc_html($newFileSize); ?></p>
    <img id="cmc-img-webp"
    src="<?php echo implode('', array(
      plugin_dir_url(__FILE__),
      '../classes/assets/img/quality-test.jpg.webp?cb=',
      rand(100000,1000000),
    )); ?>">
  </div>
</div>

<style>
.container {
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
img {
  width: 100%;
  height: auto;
}
</style>
