<?php 

$view = new DTEmailView();
$view->displayNotes();

class DTEmailView {

	private $filesystem;

    function __construct() {
		$this->filesystem = new DTFileCheckSum();
    }

	public function displayNotes()
	{
		global $wp_filter;

		?>

		<div class="wrap">
		<h2>Email Testing</h2>

		<h3>Mail test</h3>
		<p>By default Wordpress uses the builtin PHP mail() function. In Windows environments, this must be via an SMTP server. In Linux/Unix environments, this will be via Sendmail or equivalent. Alteratively you can use one of the SMTP plugins (http://wordpress.org/extend/plugins/) for WordPress.</p>

		<p><em>This will make 2 tests. 1 using the built in PHP method. The other will use the WordPress wp_mail() which may use a plugin, if you have one installed.</em></p>

		<p><em>Please check your inbox for 2 emails once you have run this test. If these 2 tests work, the ability for your blog to send emails is likely to work.</em></p>

		<form action="" id="mwsmailtest">
			<input id="dt-nonce"  type="hidden" value="<?php echo wp_create_nonce('dt-nonce'); ?>" />
			<div id="mwsmailtestdiv" class="mws-test-line">
				<label>Email Address:</label>
				<input type="text" id="mwsmailtestemail" value="<?php echo get_bloginfo('admin_email', 'raw'); ?>" />
			</div>	
			<div class="mws-test-line">
				<label>mail() PHP builtin function:</label>
				<span id="mwsmailtestresult">Pending.....</span>
			</div>
			<div class="mws-test-line">
				<label>wp_mail() WordPress function:</label>
				<span id="mwswpmailtestresult">Pending.....</span>
			</div>
			<div class="mws-test-line-button">
				<a href="#" id="mwsmailtestsubmit" class="like-a-button">Run</a>
			</div>
		</form>

		<h4>Plugins (using Hooks) that alter the way email is sent in WordPress</h4>

		<pre><?php //var_dump($wp_filter['phpmailer_init']); ?></pre>

		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th>WordPress Hook</th>
					<th>Function</th>
					<th>Plugin File</th>
					<th>Plugin Name</th>
				</tr>
			</thead>
			<tbody>
			
			<?php

			$hooks = $this->filesystem->findHooks(array('phpmailer_init'));

			foreach ($hooks as $plugin)
			{
				echo '<tr>';
				echo '<td>'.$plugin->hook.'</td>';
				echo '<td>'.$plugin->function.'</td>';
				echo '<td>'.$plugin->file.'</td>';
				echo '<td>'.$plugin->plugin.'</td>';
				echo '</tr>';
			}

			if (count($hooks) == 0) {
				echo '<tr><td colspan="4">No plugins altering wp_mail() call.</td></tr>';
			}
			echo '</tbody></table>';
			
			if (count($hooks) > 0) {
				echo '<p><em>Please note plugins that alter the way WordPress sends emails have arbitrary settings. Please check the above plugin(s) are correctly configured. Your best bet is to check that the 2 tests above work.</em></p>';
			}

		?>

		<h4>Plugins that send emails</h4>
		<table class="wp-list-table widefat"><thead>
		<tr><th>Mail Function</th>
		<th>Plugin File</th>
		<th>Plugin Name</th>
		</tr></thead><tbody>

		<?php 

			$plugins = $this->filesystem->findFunctions(array('mail(', 'wp_mail('));

			foreach ($plugins as $plugin)
			{
				echo '<tr>';
				echo '<td>'.$plugin->function.'</td>';
				echo '<td>'.$plugin->file.'</td>';
				echo '<td>'.$plugin->plugin.'</td>';
				echo '</tr>';
			}

			if (count($plugins) == 0)
				echo '<tr><td colspan="3">No mail functions used within plugins</td></tr>';

		?>
		</tbody>
		</table>
		</div>

		<?php
	}

}
