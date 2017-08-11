<?php 

DTResolverView::displayNotes();

class DTResolverView {

	function __construct() {
	}

	static function displayNotes()
	{
		$resolver = new DTResolver();
		$res = $resolver->getResolvers();
		$servers = $res['nameservers'];
		$search = $res['searchdomains'];

		?>

		<div class="wrap">
		<h2>DNS Resolver test</h2>

		<h3>About</h3>
		<p>This is designed to test your DNS Resolver. Note that resolving of domain names is done by the operating system and not by PHP. Therefore you can not change these servers. This utility simply allows you to see any potential issues with certain lookups. </p>
		<p>On *nix based systems, the search domain will be queried if no result is found for a Test URL, resulting in mutliple DNS queries. If you have a wildcard on your domain name, and the OS has this set as a search domain, you might get unexpected results that will still show as &quot;success&quot;.</p>
		<p>DNS Resolvers: <?php echo implode(', ', $servers); ?>
		<br/>Search Domains: <?php echo implode(', ', $search); ?></p>

		<h4>Test</h4>
		<p><em>Please make sure the Test URL is correct.</em></p>
		<form action="" id="mwsdnstest">
			<input id="dt-nonce" type="hidden" value="<?php echo wp_create_nonce('dt-nonce'); ?>" />
			<div class="mws-test-line">
				<label>Test URL:</label>
				<span><input type="text" id="mwsdnstesturl" value="www.google.com" /></span>
			</div>	
			<div class="mws-test-line">
				<label>Result:</label>
				<span id="mwsdnstestresult">Pending.....</span>
			</div>
			<div class="mws-test-line-button">
				<a href="#" id="mwsdnstestsubmit" class="like-a-button">Run</a>
			</div>
		</form>
		</div>

		<?php
	}

}
