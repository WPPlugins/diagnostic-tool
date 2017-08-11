<?php

/*
Plugin Name: Diagnostic Tool
Plugin URI: http://wordpress.org/plugins/diagnostic-tool/
Description: Provides visibility of Email setup & hooks used by other plugins, Outgoing connections exposing possible firewall problems, DNS setup and test facility, Cron overview & File System Diagnostics Tool to find changed files. This plugin does not affect the normal running of your site.
Version: 1.0.7
Author: Richard Bevan, Marcin Cembrzynski
Author URI: http://mywwwsupport.com
License: GPL2
*/

/*  
2013, Diagnostic Tool, Richard Bevan <rbevan@restdeveloper.com>, Marcin Cembrzynski <marcin@imre.co.uk>

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

require_once('init.php');
require_once('lib/DTNonce.class.php');
require_once('lib/DTSettings.class.php');
require_once('lib/DTFileCheckSum.class.php');
require_once('lib/DTOutboundConnection.class.php');
require_once('lib/DTResolver.class.php');

/*
 * Debug hooks
 */
new DTOutboundConnection();
$gDTSettings = new DTSettings();
$gDTSettingsVals = $gDTSettings->getSettings();

/*
 * Activate / Deactivate
 */
register_deactivation_hook(__FILE__, 'DTDeactivate');
register_activation_hook(__FILE__, 'DTActivate');

/*
 * Header, menu & cron hooks
 */
add_filter('admin_init', 'DTCreateHeader');
add_filter('cron_schedules','custom_cron_schedules');

add_action('admin_menu', 'DTCreateMenu');
add_action(DTCRONHOOK, 'DTRunCronJobs');

/*
 * AJAX callbacks
 */
add_action('wp_ajax_diagnostic_tool_http_check', array('DTOutboundConnection', 'HTTPCheck'));
add_action('wp_ajax_diagnostic_tool_mail_check', array('DTOutboundConnection', 'MailCheck'));
add_action('wp_ajax_diagnostic_tool_dns_check', array('DTResolver', 'RunTest'));

function custom_cron_schedules($schedules){
	$schedules['tenminutes'] = array(
		'interval'   => (10*60),
		'display'   => __('Ten minutes'),
	);

	return $schedules;
}

function DTActivate() {
    wp_schedule_event(time(), 'tenminutes', DTCRONHOOK);
}

function DTDeactivate() {
	delete_option(DTHTTPFILTERDISABLE);
	delete_option(DTHTTPFILTERLOG);
	delete_option(DTFILECHECKDISABLE);
	delete_option(DTFILECHECKLOG);
	delete_option(DTFILECHECKLOGLIST);

    wp_clear_scheduled_hook(DTCRONHOOK);
}

function DTCreateHeader() {
	wp_register_style('mwscss', plugins_url('css/mws.css', __FILE__));
	wp_enqueue_style('mwscss');
	wp_register_script('mwsjs', plugins_url('js/dt.js', __FILE__), array(), "9.0.0");
	wp_enqueue_script('mwsjs');
}

function DTCreateMenu() {
	add_menu_page('diagnostic-tool', 'Diagnostic Tool', 'administrator', 'diagnostic-tooloverview', 'DTOverviewPage');
	add_submenu_page('diagnostic-tooloverview', 'Altered Files', 'Altered Files', 'administrator', DTBASEDIR.'/views/dtfilechecksumview.php');
	add_submenu_page('diagnostic-tooloverview', 'Email Testing', 'Email Testing', 'administrator', DTBASEDIR.'/views/dtemailview.php');
	add_submenu_page('diagnostic-tooloverview', 'Outbound Connections', 'Outbound Connections', 'administrator', DTBASEDIR.'/views/dtoutboundconnectionsview.php');
	add_submenu_page('diagnostic-tooloverview', 'Untracked Transports', 'Untracked Transports', 'administrator', DTBASEDIR.'/views/dtoutboundconnectionsuntrackedview.php');
	add_submenu_page('diagnostic-tooloverview', 'DNS Resolver', 'DNS Resolver', 'administrator', DTBASEDIR.'/views/dtresolverview.php');
	add_submenu_page('diagnostic-tooloverview', 'Cron Overview', 'Cron Overview', 'administrator', DTBASEDIR.'/views/dtcronview.php');
	add_submenu_page('diagnostic-tooloverview', 'Settings', 'Settings', 'administrator', DTBASEDIR.'/views/dtsettings.php');
}

/*
 * Overview page
 */
function DTOverviewPage() {
	global $wp_filter, $gDTSettingsVals;

	echo '<div class="wrap">';
	echo '<h2>Overview</h2>';
	echo '<h3>Welcome to the Diagnostic Tool</h3>';
	echo '<p>This plugin has been developed to help deal with common Wordpress issues. First you will get a description of the check or report that will be run, you can then choose to proceed if relevent. Please choose from the menu to the left of this text. Under <a href="'.DTADMINPAGE.'/views/dtsettings.php">settings</a> you can turn off parts of the plugin that you do not need.</p>';

	echo '<p>Support: <a href="http://wordpress.org/support/plugin/diagnostic-tool">http://wordpress.org/support/plugin/diagnostic-tool</a></p>';

	echo '<h3>Module setup check</h3>';

	echo '<table class="wp-list-table widefat">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Diagnostic Tool Cron</th>';
	echo '<th>Result</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	echo '<tr>';
	echo '<td>Cron mode</td>';
	echo '<td>'.(($gDTSettingsVals->cron_on_load) ? 'On Page Load (<a href="'.DTADMINPAGE.'/views/dtcronview.php">Not Great</a>)' : '<em>Disabled On Page Load (Good)</em>').'</td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td>Installed</td>';
	echo '<td>'.(($gDTSettingsVals->cron_setup) ? 'Yes' : 'No').'</td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td>Overdue</td>';
	echo '<td>'.(($gDTSettingsVals->cron_overdue) ? 'Yes' : 'No').'</td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td>Runs Every</td>';
	echo '<td>'.($gDTSettingsVals->cron_interval).' seconds</td>';
	echo '</tr>';

	echo '<tr>';

	if ($gDTSettingsVals->cron_next_runtime > 0) {
		echo '<td>Next Run</td>';
		echo '<td>'.($gDTSettingsVals->cron_next_runtime).' seconds</td>';
	} else {
		echo '<td><em>Overdue By</em></td>';
		echo '<td><em>'.($gDTSettingsVals->cron_overdue_by).' seconds</em></td>';
	}

	echo '</tr>';

	echo '</tbody>';
	echo '</table>';

	if ($gDTSettingsVals->cron_next_runtime < 0) {
		echo '<p><em>An overdue cron is not the end of the world. As long as the Overdue By is not in the thousands.</em></p>';
	}

	echo '</div>';

}

/*
 * Cron
 */ 

function DTRunCronJobs()
{
	global $gDTSettingsVals; 

	/* File Checksum */
	if ($gDTSettingsVals->disable_filesum_check != true)
	{
		$checksum = new DTFileCheckSum();
		$checksum->runCron();
	}
}
