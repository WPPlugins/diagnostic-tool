<?php

class DTSettings {

	function __construct() {
	}

	public function getSettings() {

		global $wp_filter;

		$return = (object)array(
							'cron_on_load' => null,
							'cron_full' => null,
							'cron_setup' => false,
							'cron_overdue' => true, 
							'cron_overdue_by' => 0, 
							'cron_interval' => 0, 
							'cron_next_runtime' => 0, 
							'http_filter' => false,
							'disable_http_filter' => false,
							'disable_filesum_check' => false
							);

		$timestamp = time();

		$return->cron_on_load = (defined('DISABLE_WP_CRON')) ? false : true;
		
		$return->cron_full = get_option('cron', array());
		foreach ($return->cron_full as $cronRunTime => $cronArray)
		{
			if (!is_array($cronArray)) {
				continue;
			}

			foreach ($cronArray as $thisCronName => $thisCronDetails)
			{
				if ($thisCronName == DTCRONHOOK) {
					$return->cron_setup=true;
					foreach ($thisCronDetails as $ingored => $thisCronFinerDetails)
					{
						$return->cron_interval = $thisCronFinerDetails['interval'];
						if ($cronRunTime > $timestamp) {
							$return->cron_overdue=false;
							$return->cron_next_runtime = $cronRunTime - $timestamp;
						} else {
							$return->cron_overdue_by = $timestamp - $cronRunTime;
						}
					}
				}
			}
		}

		if (has_filter('http_api_debug') !== false)
		{
			foreach ($wp_filter['http_api_debug'] as $key1 => $array1)
			{
				foreach ($array1 as $key2 => $array2)
				{
					if (isset($array2['function']))
					{
						$string1=serialize($array2['function'][0]);
						$string2=serialize($array2['function'][1]);

						if (strpos($string1, 'DTOutboundConnection') !== false && strpos($string2, 'DebugOutbound') !== false) {
							$return->http_filter=true;
						}
					}
				}       
			}
		}

		// User choices
		$return->disable_http_filter=get_option(DTHTTPFILTERDISABLE);
		$return->disable_filesum_check=get_option(DTFILECHECKDISABLE);

		return $return;
	}

	public static function updateSettings($givenArray)
	{
		// Update some settings 
		if (isset($givenArray->disable_http_filter))
		{
			$val = (($givenArray->disable_http_filter == 'Yes' || $givenArray->disable_http_filter === true) ? true : false);
			update_option(DTHTTPFILTERDISABLE, $val);
		}

		if (isset($givenArray->disable_filesum_check))
		{
			$val = (($givenArray->disable_filesum_check == 'Yes' || $givenArray->disable_filesum_check === true) ? true : false);
			update_option(DTFILECHECKDISABLE, $val);
		}
	}
}
