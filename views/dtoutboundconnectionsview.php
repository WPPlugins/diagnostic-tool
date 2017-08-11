<?php 

DTOutboundConnectionsView::displayNotes();

class DTOutboundConnectionsView {

    function __construct() {
    }

	static function displayNotes()
	{
		global $gDTSettingsVals;

		$wpHttp = new DTOutboundConnection();
		$transport = $wpHttp->getTransport();

		?>

		<div class="wrap">
		<h2>Outbound Connections</h2>

		<?php if ($gDTSettingsVals->disable_http_filter == true) { echo '<p><strong><em>Outbound connections logging disabled. See <a href="'.DTADMINPAGE.'/views/dtsettings.php">settings</a></em></strong></p>'; } ?>

		<h3>About</h3>
		<p>When WordPress makes an outbound connection, this plugin records the call. The report below shows calls made, and a test can be made to see if the call is successful.</p>

		<h4>Live test</h4>
		<p>This test will make a HTTP request. If this test hangs or stops, you will need to contact your hosting provider and ask them to open outbound connections for port 80.</p>
		<p><em>Make sure the Test URL is valid</em></p>
		<form action="" id="mwshttptest">
			<input id="dt-nonce" type="hidden" value="<?php echo wp_create_nonce('dt-nonce'); ?>" />
			<div class="mws-test-line">
				<label>Transport:</label>
				<span><?php echo $transport; ?></span>
			</div>	
			<div class="mws-test-line">
				<label>Test URL:</label>
				<span><input type="text" id="mwshttptesturl" value="http://www.google.com" /></span>
			</div>	
			<div class="mws-test-line">
				<label>Result:</label>
				<span id="mwshttptesturlresult">Pending.....</span>
			</div>
			<div class="mws-test-line-button">
				<a href="#" id="mwshttptestsubmit" class="like-a-button">Run</a>
			</div>
		</form>

		<h4>Outbound Calls made by WordPress</h4>

		<p>Please note, this is a list of the last 20 calls made by the HTTP_API class built into WordPress. Any plugin may use the cURL, fsocketopen(), PHP Streams or a number of other libraries which will NOT be recorded here. WordPress considers these &quot;transports&quot; and using these is not the prefered way of making outbound connections.</p> 

		<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th>Date</th>
				<th>URL</th>
				<th>Result</th>
			</tr>
		</thead>
		<tbody>

		<?php

		$connections = get_option(DTHTTPFILTERLOG);
		if (is_array($connections)) {
			$connections = array_reverse($connections);
			foreach ($connections as $c) {
				echo '<tr><td>' . $c[1] . '</td><td>' . $c[0] . '</td><td>' . $c[2] . '</td></tr>';
			}
		}

		?>

		</tbody>
		</table>
		</div>

		<?php
	}

}
