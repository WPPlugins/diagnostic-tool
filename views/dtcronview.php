<?php 

DTCronView::render();

class DTCronView {

	static function render()
	{
		$dateFormat = 'r';	
		global $gDTSettingsVals; 

		?>

		<div class="wrap">
		<h2>Cron Overview</h2>

		<h3>About</h3>
		<p>The cron is a place to store scheduled tasks that perform non user triggered jobs. Like purging a database, cleaning up cache files or anything put into the cron by a 3rd party plugin. This plugin uses the cron to look for Altered Files which is <em><?php if ($gDTSettingsVals->disable_filesum_check == true) { echo 'NOT'; } else { echo ''; } ?>currently enabled.</em></p>

		<p>WordPress cron: <em><?php echo ($gDTSettingsVals->cron_on_load) ? 'On Page Load' : 'Disabled'; ?></em></p>

		<p>
		<?php if ($gDTSettingsVals->cron_on_load): ?>
			<em>On Page Load is Bad</em>. Every time	a page is loaded, the cron is checked. This overhead is not acceptable. We seriously suggest you disable the cron and setup a scheduled task which will trigger the cron once every 10 times.
		<?php else: ?>
			Disabled is Good <em>as long as you have setup a scheduled task to run the cron</em>. If you have tasks below that are overdue (by more than 10 mins), you need to look into why this is happening.
		<?php endif; ?>
		</p>
		
		<?php $timestamp = time(); ?>
		<p>Server Time Now: <?php echo $timestamp; ?>
		<br>Server Time Now: <?php echo date($dateFormat, $timestamp); ?></p>

		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th>Cron name</th>
					<th>Scheduled</th>
					<th>Scheduled</th>
					<th>Scheduled Every</th>
					<th>Overdue</th>
					<th>Overdue By</th>
				</tr>
			</thead>
			</tbody>

		<?php

		foreach ($gDTSettingsVals->cron_full as $cronRunTime => $cronArray)
		{
			if (!is_array($cronArray)) {
				continue;
			}


			foreach ($cronArray as $thisCronName => $thisCronDetails)
			{
				echo '<tr>';
				echo '<td>'.$thisCronName.'</td>';
				echo '<td>'.$cronRunTime.'</td>';
				echo '<td>'.date($dateFormat, $cronRunTime).'</td>';

				foreach ($thisCronDetails as $ingored => $thisCronFinerDetails)
				{
				
					echo '<td>'.$thisCronFinerDetails['interval'].'</td>';

					$overdue=0;
					if ($cronRunTime > $timestamp) {
						echo '<td>No</td>';
					} else {
						echo '<td>Yes</td>';
						$overdue=$timestamp - $cronRunTime;
					}

					echo '<td>'.$overdue.' seconds</td>';
				}
				echo '</tr>';
			}
		}

		?>

			</tbody>
		</table>
		</div>

		<?php
	}

}
