<?php 

/*
 * Environment
 */

if (defined('DTBASEDIR') === false) {
	define('DTBASEDIR', plugin_dir_path(__FILE__));
}

if (defined('DTADMINPAGE') === false) {
	define('DTADMINPAGE', admin_url() .'admin.php?page=diagnostic-tool');
}

if (defined('DTCRONHOOK') === false) {
	define('DTCRONHOOK', 'diagnostic_tool_cron_hook');
}

if (defined('DTPLUGINBASE') === false) {
	$base=str_replace('diagnostic-tool', '', plugin_dir_path( __FILE__ ));
	$base=str_replace('//', '/', $base);
	$base=str_replace('\\\\', '\\', $base);
	define('DTPLUGINBASE', $base);
}

/*
 * Behavior
 */

// mws_disable_http_filter
if (defined('DTHTTPFILTERDISABLE') === false) {
	define('DTHTTPFILTERDISABLE', 'diagnostic_tool_http_filter_disable');
}

// mws_outconnplug_con
if (defined('DTHTTPFILTERLOG') === false) {
	define('DTHTTPFILTERLOG', 'diagnostic_tool_http_filter_log');
}

// mws_disable_filesum_check
if (defined('DTFILECHECKDISABLE') === false) {
	define('DTFILECHECKDISABLE', 'diagnostic_tool_file_check_disable');
}

// mws_filessum_opt
if (defined('DTFILECHECKLOG') === false) {
	define('DTFILECHECKLOG', 'diagnostic_tool_file_check_log');
}

// mws_filessumlist_opt
if (defined('DTFILECHECKLOGLIST') === false) {
	define('DTFILECHECKLOGLIST', 'diagnostic_tool_file_check_log_list');
}
