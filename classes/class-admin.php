<?php
/*
 * CMC Webp Images admin class
 *
 * Admin page interface.
 * - Display configuration page and save plugin options.
 * - Compare original and webp images of varying quality.
 * @since 1.0.0
 */

namespace CmcImages;

defined('ABSPATH') || exit;

if (!class_exists(__NAMESPACE__ . '\CmcImages')) {
  class CmcImages {
    function __construct() {
      add_action('cmc_make_webp', array($this, 'makeWebpImages'));
      if (!is_admin()) { return; }
      $this->errMsg = null;
      $this->enableWebp = get_option(SLUG . '-enable-webp');
      add_filter('plugin_action_links_' . PLUGIN_BASENAME, array($this, 'addSettingsLink'));
      add_action('wp_ajax_' . SLUG . '-check-status', array($this, 'ajaxCheckGenerationStatus'));
      add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
      add_action('admin_menu', array($this, 'adminMenuHook'));
      add_action('admin_init', array($this, 'registerSettingsHook'));
    }

    /*
     * Add settings link to plugin entry on plugins page.
     * @since 1.0.2
     */
    public function addSettingsLink($links) {
      $link = implode('', array(
        '<a href="',
         menu_page_url(SLUG, false),
         '">Settings</a>'
      ));
      $links['settings'] = $link;
      return $links;
    }

    /*
     * Enqueue admin scripts.
     * @since 1.0.1
     */
    public function enqueueScripts() {
      if (!current_user_can('manage_options')) {
        return;
      }
      if (!isset($_GET['page']) || $_GET['page'] != SLUG) {
        return;
      }
      add_thickbox();
      $vars = array();
      $vars['SLUG'] = SLUG;
      $vars['QUALITYTESTID'] = SLUG . '-quality-test';
      $vars['ENABLEWEBPID'] = SLUG . '-enable-webp';
      $vars['QUALITYTESTFILE'] = plugin_dir_url(__FILE__) . 'assets/img/quality-test.jpg';
      $vars['NONCE'] = wp_create_nonce(SLUG);

      wp_register_script(SLUG . '-admin', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery'),
        filemtime(__DIR__ . '/assets/js/admin.js'));
      wp_localize_script(SLUG . '-admin', 'CMC', $vars);
      wp_enqueue_script(SLUG . '-admin');
    }

    /*
     * Check webp generation status.
     * @since 1.0.1
     */
    public function ajaxCheckGenerationStatus() {
      if (!current_user_can('manage_options')) {
        wp_send_json_error(array('msg'=>'You do not have permissions to view this resource.'));
      }

      if (!check_ajax_referer(SLUG, '_ajax_nonce')) {
        wp_send_json_error(array('msg'=>'Invalid request.'));
      }

      $stats = get_option(SLUG . '-conversion-stats', '{}');
      wp_send_json_success($stats);
    }

    /*
     * Register WordPress settings.
     * @since 1.0.0
     */
    public function registerSettingsHook() {
      if (!current_user_can('manage_options')) {
        return;
      }
      add_settings_section(
        SLUG . '-image-options',
        'Image Options', 
        array($this, 'renderIntro'),
        SLUG,
      );
      add_settings_field(
        SLUG . '-enable-webp',
        'Enable Webp Support',
        array($this, 'renderEnableWebp'),
        SLUG,
        SLUG . '-image-options'
      );

      add_settings_field(
        SLUG . '-webp-in-theme',
        'Serve Webp Images in Theme Folders',
        array($this, 'renderThemeWebp'),
        SLUG,
        SLUG . '-image-options'
      );

      add_settings_field(
        SLUG . '-webp-in-plugins',
        'Serve Webp Images in Plugin Folders',
        array($this, 'renderPluginsWebp'),
        SLUG,
        SLUG . '-image-options'
      );

      add_settings_field(
        SLUG . '-image-quality',
        'Webp Image Quality (1 - 100)',
        array($this, 'renderImageQuality'),
        SLUG,
        SLUG . '-image-options'
      );

      add_settings_field(
        SLUG . '-delete-webp-on-uninstall',
        'Delete Webp Images on Uninstall',
        array($this, 'renderDeleteOnUninstall'),
        SLUG,
        SLUG . '-image-options'
      );

      add_settings_field(
        SLUG . '-generate-webp-images',
        'Generate Webp Images Now',
        array($this, 'renderGenerateWebpImages'),
        SLUG,
        SLUG . '-image-options'
      );
    }

    /*
     * Displays intro paragraphs on options page.
     * @since 1.0.0
     */
    public function renderIntro() {
      if (!current_user_can('manage_options')) {
        return;
      }
      echo implode('', array(
        '<div style="max-width:800px;">',
        '<p>Check the <i>Enable Webp Support</i> checkbox to start serving webp images.</p>',
        '<p>Webp images from your media uploads will be created and served in real-time. ',
        'To generate these files ahead of time, check the <i>Generate Webp Images Now</i> button. ',
        'You will also need to check this option if you want to serve webp images ',
        'from your plugins and/or theme folders.</p>',
        '</div>',
      ));
    }

    /*
     * Renders a checkbox to start the webp creation process.
     * @since 1.0.0
     */
    public function renderGenerateWebpImages($input) {
      if (!current_user_can('manage_options')) {
        return;
      }

      echo implode('', array(
        '<input type="checkbox"',
        $this->enableWebp ? '' : ' disabled ',
        'name="',
        esc_attr(SLUG),
        '-generate-webp-images" value="true" ',
        'id="',
        esc_attr(SLUG),
        '-generate-webp-images" ',
        '>',
        '<p>This operation can take several minutes, depending on the number of images you have.</p>',
        '<p>It will be automatically unchecked once you save changes and the generation process is scheduled.</p>',
        '<div id="',
        esc_attr(SLUG),
        '-conversion-status-window" ',
        'style="width: 100%; height: 300px; display: none;">',
        '</div>',
        '<p>',
        '<a href="#TB_inline?&width=600&height=220&inlineId=',
        esc_attr(SLUG),
        '-generation-status" ',
        'name="Generation Status" ',
        'class="thickbox button button-secondary">',
        'View Generation Status',
        '</a>',
        '</p>',
      ));
      echo '<div id="' . esc_attr(SLUG) . '-generation-status" style="display:none;">';
      require_once(__DIR__ . '/../partials/generation-status.php');
      echo '</div>';
    }

    /*
     * Renders a checkbox to allow webp creation in the active theme folder.
     * @since 1.0.0
     */
    public function renderThemeWebp($input) {
      if (!current_user_can('manage_options')) {
        return;
      }

      echo implode('', array(
        '<input type="checkbox" name="',
        esc_attr(SLUG),
        '-webp-in-theme" value="true"',
        $this->enableWebp ? '' : ' disabled ',
        get_option(SLUG . '-webp-in-theme') ? ' checked ' : '',
        '>'
      ));
      echo '<p>Serve any images in your theme using the webp format.</p>';
    }

    /*
     * Renders a checkbox to allow webp creation in the plugins folder.
     * @since 1.0.0
     */
    public function renderPluginsWebp($input) {
      if (!current_user_can('manage_options')) {
        return;
      }

      echo implode('', array(
        '<input type="checkbox" name="',
        esc_attr(SLUG),
        '-webp-in-plugins" value="true"',
        $this->enableWebp ? '' : ' disabled ',
        get_option(SLUG . '-webp-in-plugins') ? ' checked ' : '',
        '>'
      ));
      echo '<p>Serve any images in WordPress plugins folders using the webp format.</p>';
    }

    /*
     * Renders checkbox to delete created webp images upon plugin uninstall.
     * @since 1.0.0
     */
    public function renderDeleteOnUninstall($input) {
      if (!current_user_can('manage_options')) {
        return;
      }

      echo implode('', array(
        '<input type="checkbox" name="',
        esc_attr(SLUG),
        '-delete-webp-on-uninstall" value="true" ',
        get_option(SLUG . '-delete-webp-on-uninstall') ? ' checked ' : '',
        $this->enableWebp ? '' : 'disabled',
        '>',
        '<p>When uninstalling the plugin, delete any generated webp files.</p>',
      ));
    }

    /*
     * Renders an <input> and slider to set the webp image quality
     *
     * In addition to rendering the quality <input> element and slider, also
     * adds a button to visually compare an example of a webp image to a jpg original
     * based on the entered quality number.
     * @since 1.0.0
     */
    public function renderImageQuality($input) {
      if (!current_user_can('manage_options')) {
        return;
      }

      $quality = @intval(get_option(SLUG . '-image-quality') ?: 50);
      if (!ctype_digit($quality) || $quality < 1 || $quality > 100) {
        $quality = 50;
      }
  ?>
      <div></div><input type="range" min="1" max="100" value="<?php echo esc_attr($quality); ?>" class="slider" id="cmc-images-slider"></div>
      <input type="number" id="<?php echo esc_attr(SLUG); ?>-image-quality" name="<?php echo esc_attr(SLUG); ?>-image-quality"
        value="<?php echo esc_attr($quality); ?>" 
        min="1"
        step="1"
        max="100"
        onfocus="this.previousValue = this.value"
        onkeydown="this.previousValue = this.value"
        oninput="validity.valid || (value = this.previousValue)">
      <p>Higher numbers result in better quality, lower numbers result in better compression.</p>
      <p>When you change this number, you must also check the <i>Generate Webp Images Now</i> checkbox.</p>
      <div style="display:none;" id="<?php echo esc_attr(SLUG); ?>-quality-modal">
      <?php require_once(__DIR__ . '/../partials/quality-test.php'); ?>
      </div>
      <div>
        <a name="Quality Test"  href="#TB_inline?&width=600&height=650&inlineId=<?php echo esc_attr(SLUG); ?>-quality-modal"
          class="button button-secondary thickbox">
          Test Image Quality
        </a>
      </div>
  <?php }

   /*
    * Renders a checkbox to enable the plugin.
    * @since 1.0.0
    */
    public function renderEnableWebp($input) {
      if (!current_user_can('manage_options')) {
        return;
      }

      echo implode('', array(
        '<input type="checkbox" name="',
        esc_attr(SLUG),
        '-enable-webp" value="true"',
        ' disabled ',
        'id="',
        esc_attr(SLUG),
        '-enable-webp"',
        get_option(SLUG . '-enable-webp') ? ' checked ' : '',
        '>'
      ));
      if (get_option(SLUG . '-enable-webp')) {
        $this->enableWebp(true);
      } else {
        $this->enableWebp(false);
      }
      echo '<p>Enabling this option will allow webp images to be served to browsers that support it.</p>';
    }

    /*
     * Adds a WordPress menu item under Settings.
     * @since 1.0.0
     */
    public function adminMenuHook() {
      if (!current_user_can('manage_options')) {
        return;
      }
      add_options_page(
        PLUGIN_NAME,
        PLUGIN_NAME,
        'manage_options',
        SLUG,
        array($this, 'renderOptions')
      );
    }

    /*
     * Renders the plugin's options page.
     * @since 1.0.0
     */
    public function renderOptions() {
      $err = '<p>You do not have permissions to edit this resource.</p>';
      if (!current_user_can('manage_options')) {
        echo $err;
        return;
      }
      if (@$_REQUEST['page'] != SLUG) {
        echo $err;
        return;
      }

      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $quality = @sanitize_text_field($_POST[SLUG . '-image-quality']) ?: 50;
        $enableWebp = @$_POST[SLUG . '-enable-webp'] ? true : false;
        $doTheme = @$_POST[SLUG . '-webp-in-theme'] ? true : false;
        $doPlugins = @$_POST[SLUG . '-webp-in-plugins'] ? true : false;
        $renderNow = @$_POST[SLUG . '-generate-webp-images'] ? true : false;
        $deleteOnUninstall = @$_POST[SLUG . '-delete-webp-on-uninstall'] ? true : false;
      } else {
        $quality = get_option(SLUG . '-image-quality') ?: 50;
        $enableWebp = get_option(SLUG . '-enable-webp');
        $doTheme = get_option(SLUG . '-webp-in-theme');
        $doPlugins = get_option(SLUG . '-webp-in-plugins');
        $deleteOnUninstall = get_option(SLUG . '-delete-webp-on-uninstall');
        $renderNow = false;
      }

      $this->enableWebp = $enableWebp; // So other functions can enable/display form elements 

      if (!$quality || !is_numeric($quality) || $quality < 1 || $quality > 100) {
        echo implode('', array(
          '<div class="notice notice-error">',
          'Image quality must be a number from 1 to 100.',
          '</div>'
        ));
      } else {
        update_option(SLUG . '-image-quality', $quality ?: 50);
      }

      if ($enableWebp) {
        update_option(SLUG . '-enable-webp', true);
      } else {
        delete_option(SLUG . '-enable-webp');
      }

      if ($doTheme) {
        update_option(SLUG . '-webp-in-theme', true);
      } else {
        delete_option(SLUG . '-webp-in-theme');
      }

      if ($doPlugins) {
        update_option(SLUG . '-webp-in-plugins', true);
      } else {
        delete_option(SLUG . '-webp-in-plugins');
      }

      if ($deleteOnUninstall) {
        update_option(SLUG . '-delete-webp-on-uninstall', true);
      } else {
        delete_option(SLUG . '-delete-webp-on-uninstall');
      }

      if ($renderNow) {
        echo implode('', array(
          '<div class="notice notice-success">',
          '<p>Webp generation started.</p>',
          '</div>',
        ));
        wp_schedule_single_event(time(), 'cmc_make_webp');
        // Force cron to fire immediately (not sure if necessary)
        wp_remote_get(get_site_url() . '/wp-cron.php?doing_wp_cron', array('blocking'=>false));
      } else {
        delete_option(SLUG . '-generate-webp-images');
      }

  ?>
      <div class="wrap">
      <h1><?php echo esc_attr(PLUGIN_NAME); ?></h1>
      <form name="options_form" method="post">
  <?php
      settings_fields(SLUG);
      do_settings_sections(SLUG);
      submit_button();
  ?>
      </form>
      </div>
<?php
    }

    /*
     * Generates all webp images under the uploads/ folder, and possibly the plugins/ 
     * and themes/<your-theme> folders.
     * @since 1.0.0
     */
    public function makeWebpImages() {

      delete_webp_images();

      $doTheme = get_option(SLUG . '-webp-in-theme');
      $doPlugins = get_option(SLUG . '-webp-in-plugins');

      $bytesSaved = 0;
      $conversions = 0;
      $failures = 0;

      $statusCheck = 10; // Update status every $statusCheck iterations

      update_option(SLUG . '-conversion-stats', array(
        'last_completed' => '(running)',
        'saved' => 0,
        'converted' => 0,
        'failures' => 0,
        'completed' => 0,
      ));

      set_time_limit(60 * 60); // one hour

      // Convert images under uploads folder
      $counter = 0;
      $this->updateGenerationStatus(0, 0, 0, false);
      $uploadsDir = wp_upload_dir()['basedir'];
      $it = new \RecursiveDirectoryIterator($uploadsDir);
      foreach (new \RecursiveIteratorIterator($it) as $file) {
        list ($b, $c, $f) = $this->makeWebpImage($file);
        $bytesSaved += $b;
        $conversions += $c;
        $f += $failures;
        $counter++;
        if (!($counter % $statusCheck)) {
          $this->updateGenerationStatus($bytesSaved, $conversions, $failures, false);
        }
      }

      // Convert images under plugins folder
      if ($doPlugins) {
        $pluginsDir = dirname(dirname(plugin_dir_path(__FILE__)));
        $it = new \RecursiveDirectoryIterator($pluginsDir);
        foreach (new \RecursiveIteratorIterator($it) as $file) {
          list ($b, $c, $f) = $this->makeWebpImage($file);
          $bytesSaved += $b;
          $conversions += $c;
          $f += $failures;
          $counter++;
          if (!($counter % $statusCheck)) {
            $this->updateGenerationStatus($bytesSaved, $conversions, $failures, false);
          }
        }
      }

      if ($doTheme) {
        // Convert images parentDir theme folder
        $parentThemeDir = get_template_directory();
        $childThemeDir = get_stylesheet_directory();
        $it = new \RecursiveDirectoryIterator($parentThemeDir);
        foreach (new \RecursiveIteratorIterator($it) as $file) {
          list ($b, $c, $f) = $this->makeWebpImage($file);
          $bytesSaved += $b;
          $conversions += $c;
          $f += $failures;
          $counter++;
          if (!($counter % $statusCheck)) {
            $this->updateGenerationStatus($bytesSaved, $conversions, $failures, false);
          }
        }

        // Convert images under child theme folder
        if ($parentThemeDir != $childThemeDir) {
          $it = new \RecursiveDirectoryIterator($childThemeDir);
          foreach (new \RecursiveIteratorIterator($it) as $file) {
            list ($b, $c, $f) = $this->makeWebpImage($file);
            $bytesSaved += $b;
            $conversions += $c;
            $f += $failures;
            $counter++;
            if (!($counter % $statusCheck)) {
              $this->updateGenerationStatus($bytesSaved, $conversions, $failures, false);
            }
          }
        }
      }
      $this->updateGenerationStatus($bytesSaved, $conversions, $failures, true);
    }

    /*
     * PRIVATE FUNCTIONS
     */

    /*
     * Make a webp image.
     */
    private function makeWebpImage($file) {
      $bytesSaved = $conversions = $failures = 0;

      $ext = $file->getExtension();
      if ($ext != 'jpg' && $ext != 'png') {
        return array(0, 0, 0);
      }

      makeWebpImage($file, true);

      if (file_exists($file . '.webp') && filesize($file . '.webp')) {
        $bytesSaved += (filesize($file) - filesize($file . '.webp'));
        $conversions++;
      } else {
        $failures++;
      }
      return array($bytesSaved, $conversions, $failures);
    }

    /*
     * Update conversion stats.
     */
    private function updateGenerationStatus($bytesSaved, $conversions, $failures, $completed) {
      if (!$completed) {
        $last = get_option(SLUG. '-conversion-stats');
        $lastCompleted = @$last['last_completed'];
        if (!$lastCompleted) {
          $lastCompleted = '(running)';
        }
      } else {
        $lastCompleted = date_format(date_create(), 'Y-m-d H:i:s');
      }
      update_option(SLUG . '-conversion-stats', array(
        'last_completed' => $lastCompleted,
        'saved' => $bytesSaved,
        'converted' => $conversions,
        'failures' => $failures,
        'completed' => $completed,
      ));
    }

    /*
     * Enables/disables webp delivery
     *
     * Modifies the .htaccess file at the root of the WordPress install to enable/disable webp delivery.
     * @since 1.0.0
     */
    private function enableWebp($enable) {
      if (!current_user_can('manage_options')) {
        return;
      }
      // Enable wepb in .htaccess
      if ($enable) {
        $lines = "<IfModule mod_rewrite.c>
    RewriteEngine On

    # Check if browser supports WebP images
    RewriteCond %{HTTP_ACCEPT} image/webp

    # Check if WebP replacement image exists
    RewriteCond %{REQUEST_FILENAME}.webp -f

    # Serve WebP image instead
    RewriteRule (.+)\.(jpe?g|png)$ $1.$2.webp [T=image/webp,E=REQUEST_image]
  </IfModule>

  <IfModule mod_headers.c>
    # Vary: Accept for all the requests to jpeg, png and gif
    Header append Vary Accept env=REQUEST_image
  </IfModule>

  <IfModule mod_mime.c>
    AddType image/webp .webp
  </IfModule>";

        insert_with_markers(get_home_path() . '.htaccess', SLUG, $lines);
        return;
      }
      // Remove webp in .htaccess
      remove_htaccess_mods();
    }
  }
  new CmcImages();
}
