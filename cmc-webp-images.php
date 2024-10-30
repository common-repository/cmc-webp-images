<?php
/*
 * Plugin Name: CMC Webp Images
 * Version: 1.0.4
 * Description: Serve jpg and png images in nextgen webp format, for better performance and higher Google Analytics scores.
 * Plugin URI: https://capemaycreative.com/wordpress/plugins/cmc-webp-images
 * Author: Cape May Creative
 * Author URI: https://capemaycreative.com
 * Requires at least: 5.0
 * Tested up to: 5.8
 *
 * @package Cmc_Images
 * @author Cape May Creative
 * @since 1.0.0
 */

namespace CmcImages;

defined('ABSPATH') || exit;

require_once(__DIR__ . '/constants.php');
define(__NAMESPACE__ . '\PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once(__DIR__ . '/includes/functions.php');

require_once(__DIR__ . '/plugin.php');
register_activation_hook(__FILE__, __NAMESPACE__ . '\activation_hook');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation_hook');

require_once(__DIR__ . '/actions.php');
require_once(__DIR__ . '/filters.php');
require_once(__DIR__ . '/classes/class-admin.php');

