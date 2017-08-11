<?php 

$stage = (isset($_GET['stage'])) ? intval($_GET['stage']) : 1;
if ($stage == 99) {
	$page = new DTFileCheckSumView();
	$page->dump();
} elseif ($stage == 2) {
	$page = new DTFileCheckSumView();
	$page->displayReport();
} else {
	DTFileCheckSumView::displayNotes();
}


class DTFileCheckSumView {

	private $filesystem;

    function __construct() {
		$this->filesystem = new DTFileCheckSum();
    }

	static function displayNotes()
	{
		global $gDTSettingsVals;

		echo '<div class="wrap">';
		echo '<h2>Altered Files</h2>';

		if ($gDTSettingsVals->disable_filesum_check == true) { echo '<p><strong><em>File checks disabled. See <a href="'.DTADMINPAGE.'/views/dtsettings.php">settings</a></em></strong></p>'; }

		echo '<h3>About</h3>';
		echo '<p>Altered Files looks at all files in your wordpress directory and records any alterations. This can be useful for spotting files that have changed without your knowledge. In managed environments, you can query your hosting company as to when and if updates have taken place. This is run as a &quot;cron&quot; - every 1 hour - and produces a report that you can view by clicking on the below.</p>';
		echo '<p><a href="'.DTADMINPAGE.'/views/dtfilechecksumview.php&stage=2" class="like-a-button">View Report</a></p>';
		echo '</div>';
	}

	public function displayReport()
	{
		global $gDTSettingsVals;

		echo '<div class="wrap">';
		echo '<h2>Altered Files</h2>';

		if ($gDTSettingsVals->disable_filesum_check == true) { echo '<p><strong><em>File checks disabled. See <a href="'.DTADMINPAGE.'/views/dtsettings.php">settings</a></em></strong></p>'; }

		echo '<table class="wp-list-table widefat">';
		echo '<thead>';
		echo '<tr> ';
		echo '<th><strong>Date</strong></th>';
		echo '<th><strong>Check</strong></th>';
		echo '<th><strong>Total Files</strong></th>';
		echo '<th><strong>Change</strong></th>';
		echo '<th><strong>File</strong></th>';
		echo '</tr>';
		echo '</thead>';

		$info = $this->filesystem->getChanges(array('offset' => 0, 'limit' => 10));

		echo '<tbody>';
		$lastCS='';
		$baseDir=$_SERVER['DOCUMENT_ROOT'];
		foreach ($info as $o) {

			if (($o->md5 != $lastCS) && !empty($o->filechanges))
			{
				echo '<tr>';
				echo '<td>' . $o->date . '</td>'; //<font style="font-size:0.7em">'.$o->md5.'</font></td>';
				echo '<td>' . $o->changeset . '</td>';
				echo '<td>' . $o->filecount . '</td>';
				$firstChange=true;
				foreach ($o->filechanges as $key => $details) {

					if (!$firstChange) {
						echo '<tr><td colspan="3">&nbsp;</td>';
					}
						
					echo '<td>'.$details['change'].'</td>';
					echo '<td>'.str_replace($baseDir, '', $key).'</td>';
					echo '</tr>';
					$firstChange=false;
				}
				$lastCS=$o->md5;
			}
		}
		echo '</tbody>';
		echo '</table>';
		echo '</div>';

	} // displayReport

	public function dump() {
		echo '<pre>';
		$this->filesystem->listFolderFiles(ABSPATH);
		var_dump($this->filesystem->getFiles());
		echo '</pre>';
	}

} // Class
