<?php 

$stage = (isset($_POST['stage'])) ? intval($_POST['stage']) : 1;

if ($stage == 2) {
	DTSettingsView::updateSettings($_POST);
} else {
	DTSettingsView::displaySettings();
}

class DTSettingsView {

	private $filesystem;

    function __construct() {
    }

	public static function displaySettings()
	{
		global $gDTSettingsVals;

		// Double check we are up to date
		$gDTSettings = new DTSettings();
		$gDTSettingsVals = $gDTSettings->getSettings();

		?>

		<div class="wrap">
		<h2>Settings</h2>

		<p>Please note the cron should always be setup when this plugin is installed. You can choose to disable certain parts of this plugin below.</p>

		<form action="" method="post">
		<input type="hidden" name="stage" value="2" />

        <table class="wp-list-table">
        <tbody>

        <tr>
        <td>Cron installed</td>
        <td><?php echo (($gDTSettingsVals->cron_setup) ? 'Yes' : 'No'); ?></td>
        </tr>

        <tr>
        <td>Disable &quot;Altered Files&quot; check</td>
        <td>
			<select name="disable_filesum_check">
				<option value="Yes" <?php if ($gDTSettingsVals->disable_filesum_check == true) { echo ' selected'; } ?>>Yes</option>
				<option value="No" <?php if ($gDTSettingsVals->disable_filesum_check == false) { echo ' selected'; } ?>>No</option>
			</select>
		</td>
        </tr>

        <tr>
        <td>Disable &quot;Outgoing Connections&quot; check</td>
		<?php if ($gDTSettingsVals->http_filter == false): ?>
			<td>WARNING: Hook Disabled</td>
		<?php else: ?>
        <td>
			<select name="disable_http_filter">
				<option value="Yes" <?php if ($gDTSettingsVals->disable_http_filter == true) { echo ' selected'; } ?>>Yes</option>
				<option value="No" <?php if ($gDTSettingsVals->disable_http_filter == false) { echo ' selected'; } ?>>No</option>
			</select>
		</td>
		<?php endif; ?>
        </tr>

        <tr>
        <td></td>
        <td><input type="submit" value="Update" /></td>
        </tr>

        </tbody>
        </table>

		</form>

		</div>

		<?php
	}

	public function updateSettings($givenArray)
	{
		DTSettings::updateSettings((object)$givenArray);
		DTSettingsView::displaySettings();
	}

}
