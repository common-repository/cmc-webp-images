<?php
/*
 * Display webp conversion stats.
 */

namespace CmcImages;

if (!current_user_can('manage_options')) { wp_die('You do not have permissions to access this page.'); }

$stats = get_option(SLUG . '-conversion-stats');

if (!$stats) {
  echo implode(array(
    '<p>No status information available. Try checking the <i>Generate Webp Images Now</i> checkbox on the options page. ',
    'If you have already done this, wait a minute and  page.</p>'
  ));
  return;
}

$id = SLUG . '-quality-test';

?>
<div class="container" id="<?php echo esc_attr($id); ?>">
<h2>Current Webp Generation Status<br><small>Last Completed: <span id="<?php echo esc_attr(SLUG); ?>-last-completed"><?php echo @esc_html(@$stats['last_completed'] ?: 'never'); ?></span></small> </h2>
<table>
<tr>
<th>Webp Files Generated</th>
<th>Failed Generations</th>
<th>Bytes Saved</th>
</tr>
<tr>
<td align="right" id="<?php echo esc_attr(SLUG); ?>-num-converted">
<?php echo @esc_html(@$stats['converted']); ?>
</td>
<td align="right" id="<?php echo esc_attr(SLUG); ?>-num-failures">
<?php echo @esc_html(@$stats['failures']); ?>
</td>
<td align="right" id="<?php echo esc_attr(SLUG); ?>-bytes-saved">
<?php echo @number_format(@esc_html(@$stats['saved'])); ?>
</td>
</tr>
<tr>
<td colspan="3" align="center" id="<?php echo esc_attr(SLUG); ?>-is-completed">
<?php if ($stats['completed']) { ?>
Webp conversion has <b>completed.</b>
<?php } else { ?>
Webp conversion in process.
<?php } ?>
</td>
</table>
</div>
<style>
.container#<?php echo esc_html($id); ?> {
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
#<?php echo esc_html($id); ?> table {
  max-width: 600px;
}
#<?php echo esc_html($id); ?> table th {
  font-weight: bold !important;
}
#<?php echo esc_html($id); ?> table tr:nth-child(odd) {
  background-color: #fafafa;
}
#<?php echo esc_html($id); ?> table td, #<?php echo esc_html($id); ?> table th {
  padding: 10px 10px !important;
}
</style>
