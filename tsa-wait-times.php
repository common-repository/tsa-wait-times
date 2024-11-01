<?php
/**
 * Plugin Name: TSA Wait Times
 * Plugin URI: https://www.tsawaittimes.com/wordpress
 * Description: Retrieve current estimated wait times for all security checkpoints at all U.S. airports. You can also retrieve current airport delays. Add the short code and we'll update the content automatically for you. 
 * Version: 1.5
 * Author: TayTech, LLC
 * Author URI: https://www.tsawaittimes.com
 * Text Domain: TSAWaitTimes.com
 * Domain Path: 
 * Network: 
 * License: GPLv2
 */
 /*  Copyright 2020  TayTech, LLC  (email : support@tsawaittimes.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	exit;
}
/**
 * Add our shortcodes 
**/
add_shortcode('tsa_wait_time', 'tsaWaitTimes');

/**
 * Add our CSS
 * Added stylesheet to its own action.
**/
function tsa_add_styles(){
	wp_enqueue_style('tsacss', plugins_url('tsa-wait-times/css/tsa.css'),array(),'20240101');
}
add_action('wp_enqueue_scripts','tsa_add_styles');

/**
 * Add our gauge JS
 * Added js to header for user
**/
function tsa_add_gauge_js(){
	if ('n' != esc_attr(get_option('tsa_airport_show_gauge'))){
		wp_enqueue_script('pureknob', plugins_url('js/tsa-pureknob.js', __FILE__), [], 1.0, false);//for some reason, this stopped working as well - doesn't even get called. Commenting out the add_action until we have more time to work through this.
	}
}
//add_action( 'wp_head', 'tsa_add_gauge_js' );


/**
 * Display the current wait time for an airport
 *
 * @param array $params An array of values provided in the shortcode. Expecting 'airport' => 'ATL' or equivalent airport
**/
function tsaWaitTimes($params){
	$output = array();
	if (isset($params['airport'])){$airport = $params['airport'];}else{$airport = '';}
	
	//$output[] = "<link href='https://www.tsawaittimes.com/css/wordpress.css' rel='stylesheet'>";
	
	//-- Is there an airport --
	if ($airport == ''){
		$output[] = "<p><span style='font-weight:bold;color:red;'>ERROR:</span> No airport code supplied. Please add an aiport code to your Wordpress Short Code.</p>";
		$output[] = "<p>EXAMPLE: [tsa_wait_time airport='ATL']</p>";
		$output = @implode("", $output);
		return $output;
	}
	
	//-- Call the API --
	$data = json_decode(callTSAAPI('airport', $airport));
	if (getTSAKey() == 'test'){$output[] = "<p>** TEST DATA **</p><p>To retrieve live data, please create an <a href='https://www.tsawaittimes.com/myaccount' target='_blank'>API key</a>. Instructions for working with the TSA Wait Times WordPress plugin can be found <a href='https://www.tsawaittimes.com/wordpress' target='_blank'>here</a>.</p>";}
	
	//-- Is there an error --
	if (isset($data->error)){
		$output[] = "<p><span style='font-weight:bold;color:red;'>ERROR:</span> " . $data->error . "</p>";
		$output = @implode("", $output);
		return $output;
	}
	
	//-- Create the page content --
	if ('n' != esc_attr(get_option('tsa_airport_show_header'))){
		$output[] = "<h1>" . $data->name . " (" . $data->city . ", " . $data->state . ")</h1>";
	}
	//-- Show the gauge? _-
	$output[] = "<div class='tsa-row'>";
	if ('n' != esc_attr(get_option('tsa_airport_show_gauge'))){
		//-- some chart vars for us --
		if ($data->rightnow > 60){ $max = $data->rightnow; }else{ $max = 60; }
		$color = '1C8406';
		if ($data->rightnow > 10 && $data->rightnow <= 20){ $color = '4bc0c0'; }
		if ($data->rightnow > 20 && $data->rightnow <= 30){ $color = 'f47603'; }
		if ($data->rightnow > 30){ $color = 'e90816'; }
		$output[] = "<div class='tsa-col-md-4 tsa-col-12 tsa-col-sm-12 tsa-center'>";
		$output[] = "<script type='text/javascript' src='https://www.tsawaittimes.com/js/tsa-pureknob.js'></script>";
		$output[] = "<script type='text/javascript'>
		jQuery(document).ready(function($){
			var knob = pureknob.createKnob(120, 120);
			// Set properties.
			knob.setProperty('angleStart', -0.50 * Math.PI);
			knob.setProperty('angleEnd', 0.50 * Math.PI);
			knob.setProperty('colorBG', '#E3E3E3');
			knob.setProperty('colorFG', '#" . $color . "');
			knob.setProperty('trackWidth', 0.4);
			knob.setProperty('valMin', 0);
			knob.setProperty('valMax', " . $max . ");

			// Set initial value.
			knob.setValue(" . $data->rightnow . ");

			// Create element node.
			var node = knob.node();

			// Add it to the DOM.
			var elem = document.getElementById('tsa_chart');
			elem.appendChild(node);
		});
			</script>";
		$output[] = "<div id='tsa_chart'></div>";
		$output[] = "</div>";
		$output[] = "<div class='tsa-col-md-8 tsa-col-12 tsa-col-sm-12'>";
		$output[] = "Passengers moving through the security checkpoints should anticipate waiting on average for:<br><strong>" . $data->rightnow_description . "</strong>";
		$output[] = "</div>";
	}else{
		$output[] = "<div class='tsa-col-12'>";
		$output[] = "Passengers moving through the security checkpoints should anticipate waiting on average for:<br><strong>" . $data->rightnow_description . "</strong>";
		$output[] = "</div>";
	}
	$output[] = "</div>";
	/* FAA Ground Stops */
	if (isset($data->faa_alerts->ground_stops) && $data->faa_alerts->ground_stops->reason != ''){
		$output[] = "<div class='tsa-top tsa-alert tsa-alert-danger'><strong>GROUND STOP:</strong> There is a ground stop in effect until " . $data->faa_alerts->ground_stops->end_time . " due to " . $data->faa_alerts->ground_stops->reason . ".</div>";
	}
	/* FAA Ground Delays */
	if (isset($data->faa_alerts->ground_delays) && $data->faa_alerts->ground_delays->reason != ''){
		$output[] = "<div class='tsa-top tsa-alert tsa-alert-warning'><strong>GROUND DELAY:</strong> There is a ground delay in effect averaging " . $data->faa_alerts->ground_delays->avg . " due to " . $data->faa_alerts->ground_delays->reason . ".</div>";
	}
	/* FAA General Delays */
	if (isset($data->faa_alerts->general_delays) && $data->faa_alerts->general_delays->reason != ''){
		$output[] = "<div class='tsa-top tsa-alert tsa-alert-primary'><strong>NOTICE:</strong> " . $data->faa_alerts->general_delays->trend . " due to " . substr($data->faa_alerts->general_delays->reason, (strrpos($data->faa_alerts->general_delays->reason, ':') ?: -1) +1) . ".</div>";
	}
	//-- Show the TSA Precheck lanes? --
	if ('n' != esc_attr(get_option('tsa_airport_show_precheck'))){
		$output[] = "<h2 class='tsa-top'>TSA Precheck Lanes: ";
		if ($data->precheck){$output[] = '<span class="tsa-text-success">Available</span>';}else{$output[] = '<span class="tsa-text-danger">Not Available</span>';}
		$output[] = "</h2>";
		$output[] = "<div class='tsa-row'>";		
		$output[] = "<div class='tsa-col-12'>";
		foreach ($data->precheck_checkpoints AS $terminal => $gates){ 
			$output[] = "<p class='tsa-bold' style='margin-top:10px;margin-bottom:0px;'>" . $terminal . "</p>";
			$output[] = "<ul class='tsa-list-group'>";
			foreach ($gates AS $gate => $status){
				$output[] = "<li class='tsa-list-group-item'>";
				$output[] = $gate;
				$output[] = "<span class='tsa-float-right tsa-badge tsa-badge-";
				if ($status == 'Open'){$output[] = "success";}else{$output[] = "danger";}
				$output[] = " tsa-badge-pill'>" . $status . "</span>";
				$output[] = "</li>";
			}
			$output[] = "</ul>";
		}
		$output[] = "</div>";
		$output[] = "</div>";
	}
	//-- Show the Hourly Estimates? --
	$_barheight = '20';//how many pixels tall should the progress bar be?
	if ('n' != esc_attr(get_option('tsa_airport_show_hourly'))){
		$output[] = "<h2 class='tsa-top'>Estimated Wait Times For Today</h2>";
		foreach ($data->estimated_hourly_times AS $time){
			$output[] = "<div class='tsa-row'>";
			$output[] = "<div class='tsa-col-md-4 tsa-col-sm-6 tsa-col-12'>";
			$output[] = $time->timeslot;
			$output[] = "</div>";
			$output[] = "<div class='tsa-col-md-8 tsa-col-sm-6 tsa-col-12'>";
				//-- assuming 60 minutes is max wait time --
				$bartime = round(($time->waittime/60)*100);
				$barcolor = 'success';
				if ($time->waittime >= 10 && $time->waittime < 20){ $barcolor = 'info'; }
				if ($time->waittime >= 20 && $time->waittime < 30){ $barcolor = 'warning'; }
				if ($time->waittime >= 30){ $barcolor = 'danger'; }
			$output[] = "<div class='tsa-progress tsa-md-progress' style='height: " . $_barheight . "px;'>";
			$output[] = "<div class='tsa-progress-bar tsa-bg-" . $barcolor . "' role='progressbar' style='width: " . $bartime . "%; height: " . $_barheight . "px;' aria-valuenow='" . $bartime . "' aria-valuemin='0' aria-valuemax='100'>" . round($time->waittime) . " m</div>";
			$output[] = "</div>";
			$output[] = "</div>";
			$output[] = "</div>";
			$output[] = "<hr>";
		}
	}
	$output[] = "<p class='tsa-disclaimer tsa-top'>* Wait times are estimates, subject to change, and may not be indicative of your experience.</p>";

	$output = @implode("", $output);
	return $output;
}


/**
 * Utility function to call the TSAWaitTimes API
 *
 * @param string $service The service to call
 * @param string $var A variable to pass. Example: ATL for airport
**/
function callTSAAPI($service, $var = ''){
	//-- Build the URL --
	$apiKey = getTSAKey();
	if ($var != ''){$var = $var . '/';}
	$url = 'https://www.tsawaittimes.com/api/' . $service . '/' . $apiKey . '/' . $var . 'json';
	$response = wp_remote_get($url);
	if (is_array($response)){
	  $body = $response['body'];
	}
	if (!isset($body)){$body = '';}
	return $body;
}

/**
 * Utility function to get the TSAWaitTimes API Key
 *
**/
function getTSAKey(){
	$key = get_option('tsa_api_key', 'test');
	if ($key == ''){$key = 'test';}
	return $key;
}

/**
 * Admin Menu
**/
if (is_admin()){ // admin actions
  add_action('admin_menu', 'tsa_create_menu');
  add_action('admin_init', 'register_tsasettings');
}

function tsa_create_menu() {
	add_submenu_page('options-general.php', 'TSA Wait Times Settings', 'TSA Wait Times Settings', 'administrator', 'tsa_settings', 'tsa_settings_page');
}

function register_tsasettings() {
	register_setting( 'tsa-settings-group', 'tsa_api_key' );
	foreach (tsa_get_custom_settings() AS $k => $v){
		register_setting( 'tsa-settings-group', $k );
	}
}

function tsa_get_custom_settings(){
	$_tsa_settings_core = [
		'tsa_airport_show_header' => 'Show Airport Name?', 
		'tsa_airport_show_gauge' => 'Show Wait Time Indicator?',
		'tsa_airport_show_precheck' => 'Show TSA Precheck Lanes?',
		'tsa_airport_show_hourly' => 'Show Estimated Hourly Wait Times?'
	];
	return $_tsa_settings_core;
}

function tsa_settings_page() {
	$_tsa_settings_core = tsa_get_custom_settings();
?>
<div class="wrap">
<h2>TSA Wait Times Settings</h2>
<p>It's quick and easy to add airport-related content to your website, but you'll need to register for an API key on TSAWaitTimes.com.</p>
<p><a href="https://www.tsawaittimes.com/myaccount" target="_blank">Click here to request an API key.</a></p>
<p>If you do not use a live API key, a test key will be used; however, TEST DATA IS NOT CURRENT. It is merely used to show you what the data would look like.</p>
<form method="post" action="options.php">
    <?php settings_fields( 'tsa-settings-group' ); ?>
    <?php do_settings_sections( 'tsa-settings-group' ); ?>
    <hr>
	<table class="form-table">
        <tr valign="top">
        <td>Your API Key from TSAWaitTimes.com</td>
        <td><input type="text" name="tsa_api_key" class="regular-text" value="<?php echo esc_attr( get_option('tsa_api_key') ); ?>" /></td>
        </tr>
    </table>
	<hr>
	<h4>Custom Settings</h4>
	<table class="form-table">
		<?php foreach ($_tsa_settings_core AS $_field => $_question){ ?>
		<?php $_answer = esc_attr( get_option($_field) ); ?>
        <tr valign="top" style="border-bottom:1px solid #333;">
        <td><?= $_question; ?></td>
        <td><select name="<?= $_field; ?>" size="1">
			<option value="y"<?php if ($_answer == 'y' || $_answer == '' ){echo " selected";} ?>>Yes</option>
			<option value="n"<?php if ($_answer == 'n'){echo " selected";} ?>>No</option>
		</select>
        </tr>
		<?php } ?>
    </table>
    <?php submit_button(); ?>
</form>
<p>For complete instructions on how to implement the TSAWaitTimes.com plugin, be sure to visit our <a href="https://www.tsawaittimes.com/wordpress" target="_blank">WordPress How-To Guide</a>.</p>
</div>
<?php } ?>