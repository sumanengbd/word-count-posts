<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Progress Bar Front-End HTML
 * 
 */
$options = [
    'wcp_progress_bar' => '',
    'wcp_progress_bar_background' => '#DDDDDD',
    'wcp_progress_bar_foreground' => '#2271B1',
    'wcp_progress_bar_thickness' => '3',
    'wcp_progress_bar_location' => '0',
    'wcp_progress_bar_location_class' => '',
    'wcp_progress_bar_location_position' => '0'
];

foreach ($options as $key => &$value) {
    $value = get_option($key) ?: $value;
}

if ( $options['wcp_progress_bar'] ) {
    $location = [
        '0' => 'top: 0; bottom: auto; position: fixed;',
        '1' => 'top: auto; bottom: 0; position: fixed;',
        '2' => 'top: 0; bottom: auto; position: absolute;'
    ][$options['wcp_progress_bar_location']];
  ?>
    <div class="wcp-progress-wrap" style="background-color: <?php echo $options['wcp_progress_bar_background']; ?>; height: <?php echo $options['wcp_progress_bar_thickness'] . 'px'; ?>; <?php echo $options['wcp_progress_bar_location'] == '2' && $options['wcp_progress_bar_location_position'] == '1' ? 'top: auto; bottom: 0; position: absolute;' : $location; ?>" position="<?php echo $options['wcp_progress_bar_location_position']; ?>" <?php echo $options['wcp_progress_bar_location'] == '2' && $options['wcp_progress_bar_location_class'] ? 'position-custom="'.$options['wcp_progress_bar_location_class'].'"' : ''; ?>>
        <div class="wcp-progress-bar" style="background-color: <?php echo $options['wcp_progress_bar_foreground']; ?>;"></div>
    </div>
  <?php
}