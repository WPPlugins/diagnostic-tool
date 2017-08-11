<?php 

$view = new DTOutboundUntrackedView();
$view->render();

class DTOutboundUntrackedView {

	private $filesystem;

    function __construct() {
		$this->filesystem = new DTFileCheckSum();
    }

	public function render()
	{
		global $wp_filter;

		?>

		<div class="wrap">
		<h2>Untracked Transports</h2>

		<p>A list of plugins that are not using the provided WordPress Class to make outbound http/https calls.</p>

		<p><em>These are very difficult to debug</em></p>

		<h4>Plugins directly using &quot;Transports&quot; to make outbound calls.</h4>
		<table class="wp-list-table widefat"><thead>
		<tr><th>Function</th>
		<th>Plugin File</th>
		<th>Plugin Name</th>
		</tr></thead><tbody>

		<?php 

			$plugins = $this->filesystem->findFunctions(array('curl_init('));

			foreach ($plugins as $plugin)
			{
				echo '<tr>';
				echo '<td>'.$plugin->function.'</td>';
				echo '<td>'.$plugin->file.'</td>';
				echo '<td>'.$plugin->plugin.'</td>';
				echo '</tr>';
			}

			if (count($plugins) == 0)
				echo '<tr><td colspan="3">No dodgy functions used within plugins</td></tr>';

		?>
		</tbody>
		</table>
		</div>

		<?php
	}

}
