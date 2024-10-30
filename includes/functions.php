<?php

namespace CmcImages;

defined('ABSPATH') || exit;

/*
 * Check if string starts with another string
 *
 * @param string $haystack String to search in.
 * @param string $needle String to search for.
 * @return boolean True if $haystack starts with $needle.
 * @since 1.0.0
 */
if (!function_exists(__NAMESPACE__ . '\starts_with')) {
  function starts_with ($haystack, $needle) {
    $length = strlen ($needle);
    return substr($haystack, 0, $length) === $needle;
  }
}

/*
 * Check if string ends with another string
 *
 * @param string $haystack String to search in.
 * @param string $needle String to search for.
 * @return boolean True if $haystack ends with $needle.
 * @since 1.0.0
 */
if (!function_exists(__NAMESPACE__ . '\ends_with')) {
  function ends_with ($haystack, $needle) {
    $length = strlen ($needle);
    if (!$length) {
      return true;
    }
    return substr($haystack, -$length) === $needle;
  }
}

/*
 * Remove content inserted by insert_with_markers() function
 *
 * @param string $contents File contents.
 * @param string $marker Marker used to delineate the start and end of the content to remove.
 * @return string Original $contents minus the text between the # START $marker and # END $marker markers.
 * @since 1.0.0
 */
if (!function_exists(__NAMESPACE__ . '\remove_marker')) {
  function remove_marker($contents, $marker) {
      $posa = strpos($contents, "\n# BEGIN " . $marker);
      if ($posa === false) { return $contents; }
      $posb = strpos($contents, '# END ' . $marker);
      if ($posb === false) { return $contents; }
      $posb += strlen('# END ' . $marker);
      $newcontent = substr($contents, 0, $posa);
      $newcontent .= substr($contents, $posb, strlen($contents));
      return $newcontent;
  }
}

/*
 * Log to cmc-images/logs/debug.txt
 *
 * Log data for debugging. The function will maintain the log file, truncating
 * it when it reaches 5M in size.
 * @param mixed $s Pretty much any PHP type will work here. The function uses print_r for non-strings.
 * @since 1.0.0
 */
if (!function_exists(__NAMESPACE__ . '\logit')) {
  function logit($s) {
    if (!CMC_DEBUG) { return; }
    if (!is_string($s)) {
      $s = print_r($s, true);
    }
    $s .= "\n";
    $file = __DIR__ . '/../logs/debug.txt'; 
    if (@filesize($file) > 5000000) {
      unlink($file);
    }
    $f = fopen($file, 'a+');
    fwrite($f, $s);
    fclose($f);
  }
}


/*
 * Create webp image and store in options to ensure deletion on plugin uninstall (if requested).
 * @since 1.0.0
 */
if (!function_exists('makeWebpImage')) {
  function makeWebpImage ($filePath, $overwrite=false) {
    if (!function_exists('imagewebp')) { return; }

    if (!function_exists('imagecreatefrompng')) { return; }
    if (!get_option(SLUG . '-enable-webp')) { return; }
    if (strpos($filePath, get_home_path()) !== 0) { return; }
    if (strpos($filePath, '..') !== false) { return; }

    $quality = get_option(SLUG . '-image-quality') ?: 50;
    $is_jpg = true ? ends_with($filePath, '.jpg') : false;
    $is_png = true ? ends_with($filePath, '.png') : false;

    if (!$is_jpg && !$is_png) { return; }

    if ($is_jpg) {
      $im = @imagecreatefromjpeg($filePath);
    } else {
      $im = @imagecreatefrompng($filePath);
    }

    if (!$im) { return; }

    $newFilePath = $filePath . '.webp';

    if (!$overwrite && file_exists($newFilePath)) {
      return;
    }

    @imagewebp($im, $newFilePath, $quality);
    logit($newFilePath);

    if (is_string($filePath)) {
      $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    } else {
      $ext = $filePath->getExtension();
    }

    if (!file_exists($newFilePath) || !filesize($newFilePath)) {
      if ($ext == 'png') { 
        // "Palette image not supported by webp" error can occur here
        imagepalettetotruecolor($im);
        imagealphablending($im, true);
        imagesavealpha($im, true);
        imagewebp($im, $newFilePath, $quality);
        if (!file_exists($newFilePath) || !filesize($newFilePath)) {
          @unlink($newFilePath);
          return;
        }
      } else {
        @unlink($newFilePath);
        return;
      }
    }

    // Save file for later deletion on uninstall
    $webpAry = get_option(SLUG . '-webp-images') ?: array();
    if (!is_array($webpAry)) {
      $webpAry = array();
    }
    if (!in_array($newFilePath, $webpAry)) {
      $webpAry[] = $newFilePath;
      update_option(SLUG . '-webp-images', $webpAry);
    }
  }
}

/*
 * Delete previously created webp images.
 * @since 1.0.3
 */
if (!function_exists(__NAMESPACE__ . '\delete_webp_images')) {
  function delete_webp_images() {
    $images = get_option(SLUG . '-webp-images');
    if ($images) {
      foreach ($images as $image) { 
        if (!ends_with($image, '.webp')) {
          continue;
        }
        @unlink($image);
      }
    }
  }
}

/*
 * Remove changes to .htaccess.
 * @since 1.0.3
 */
if (!function_exists(__NAMESPACE__ . '\remove_htaccess_mods')) {
  function remove_htaccess_mods() {
      $htaccess = get_home_path() . '.htaccess';
      if (!file_exists($htaccess)) { return; }
      file_put_contents($htaccess, remove_marker(file_get_contents($htaccess), SLUG));
  }
}
