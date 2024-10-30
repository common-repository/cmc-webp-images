=== CMC Webp Images ===
Contributors: capemaycreative
Tags: webp, cmc, images, image optimization, optimize, jpg, png, seo, page speed, load speed, image size
Requires at least: 5.0
Tested up to: 5.8
Stable tag: 1.0.4
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
CMC Webp Images creates a webp version of all your WordPress images. It can do so all at once or when an image is requested. Simple to install and set up, this plugin will increase your page load times and decrease the amount of bandwidth you need to serve images. The webp image format is the recommended image format by Google's [Page Speed/Lighthouse](https://developers.google.com/web/tools/lighthouse).

== Installation ==
To install the plugin, use the plugin admin page to search for *cmc images*, then click on the appropriate search result item. After you activate the plugin, head over to Settings -&gt; CMC Images to configure the various settings.

The following is a brief description of each configuration setting.

= Enable Webp Support =
When this item is checked, your Apache web server's .htaccess file will be updated to serve webp images (when the file exists).

= Serve Webp Images in Theme Folders =
Check this item to allow webp generate in your theme subfolders.

= Serve Webp Images in Plugins Folders =
Check this item to allow webp generate in your plugins subfolders.

**NOTE:** To serve webp images in your theme and plugin subfolders, you must check the *Generate Webp Images Now** checkbox.

= Webp Image Quality =
Enter a number representing the quality of the webp images. Lower numbers mean smaller images/faster load times, but also lower quality. We recommend using a quality of 50 to start, then adjusting accordingly.

= Test Image Quality =
Before saving, use the *Test Image Quality* button to get a visual idea of what your images will look like.

= Delete WEbp Images on Uninstall =
Check this item to ensure that any generate webp images are removed when uninstalling the plugin.

= Generate Webp Images Now =
By default, webp images are created and served when requested by the browser. Checking this item will create the webp images ahead of time, saving time and system resources. This process will take a few seconds or more, depending on the number and sizes of your images.

= Save Changes =
Click the *Save Changes* button when all configuration items have been set.

== Requirements ==
CMC Images requires:
- Wordpress 5.0 or greater
- PHP 7.0 or greater
- The Apache 2 web server
- [PHP GD module](https://www.php.net/manual/en/image.installation.php) 

== Changelog ==
= 20211208 =
* Version 1.0.0 released.

= 20211209 =
* Version 1.0.1 released.
* Fixed issue where removing .htaccess statements was leaving an errant return character.
* Fixed issue where wp_cron would only start generating images after another page was viewed in the browser.

= 20211209 =
* Version 1.0.2 released.
* Fixed typo in .htaccess.

= 20211216 =
* Version 1.0.3 released.
* Delete webp images before recreating.
* Added deactivation hook to remove .htaccess entries.

= 20220118 =
* Version 1.0.4 released.
* Fixed issue where error would occur upon removing the imagewebp() PHP function AFTER the plugin was installed.

== Screenshots ==
1. Admin options page
2. Quality test
3. Image generation status

