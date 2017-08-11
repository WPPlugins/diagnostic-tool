<?php

class DTResolver {

	public function getResolvers()
	{
		
		$nixNameservers = '/etc/resolv.conf';
		$winCmd = 'ipconfig /all';
		$nameServers=array();
		$searchDomains=array();

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$output='';
			$debug='';
			exec('ipconfig /all', $output, $res);

			$debug .= 'Exec result = '.print_r($res, true);

			$key='';
			$value='';
			for ($i=0; $i<count($output); $i++)
			{
				$debug .= 'Checking line: '.$output[$i];
				if (strpos($output[$i], ':') === false)
				{
					continue;
				}

				list($key, $value) = split(':', $output[$i]);
				$value = rtrim($value);

				if (strpos($key, 'Search List') !== false)
				{
					$debug .= "USING ".$value."\n";
					$searchDomains[]=$value;
				}
				else if (strpos($key, 'DNS Servers') !== false)
				{
					$debug .= "USING ".$value."\n";
					$nameServers[]=$value;
				}

			}

		}
		else
		{
			//if (!is_readable($ninNameservers) === true)
			//{
			//	return array('Unknown');
			//}
    
			try {
				$data = @file_get_contents($nixNameservers);
			} catch (Exception $e) {
				$data=false;
			}

			if ($data === false)
			{
				return array('nameservers'=>array('Unknown due to PHP restriction (common on shared hosting)'), 'searchdomains'=>array('Unknown due to PHP restriction (common on shared hosting)'));
			}

			$lines = explode("\n", $data);

			foreach ($lines as $line)
			{

				$line = trim($line);

				//
				// ignore empty lines, and lines that are commented out
				//
				if ( (strlen($line) == 0) 
					|| ($line[0] == '#') 
					|| ($line[0] == ';') ) 
				{
					continue;
				}

				list($key, $value) = preg_split('/\s+/', $line, 2);

				$key    = trim(strtolower($key));
				$value  = trim(strtolower($value));

				if ($key == 'nameserver')
				{
					//
					// nameserver can be a IPv4 or IPv6 address
					//
					if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $value) == true)
					{
						$nameServers[] = $value;
					}
					else 
					{
						$nameServers[] = 'Unknown';
					}
				}
				else if ($key == 'search')
				{
					$searchDomains=array_merge($searchDomains, explode(' ', $value));
				}
			}
		}
		
		return array('nameservers'=>$nameServers, 'searchdomains'=>$searchDomains);
	}

	public function RunTest()
	{
		DTNonce::checkNonce();

		$url = preg_replace('/[^0-9a-z\-\.]/', '', strtolower($_POST['url']));
		header('Content-Type: application/json');

		$res='';
		$using='';
		if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $url)) {
			$using = 'gethostbyaddr()';	
			$res = gethostbyaddr($url);
			$resText = $res;
			$resHost = $url;
		}
		else
		{
			$using = 'dns_get_record()';
			$res = dns_get_record($url, DNS_A);	
			$resText = @$res[0]['ip'];
			$resHost = @$res[0]['host'];
		}

		if ($res === false) {
			echo json_encode(array('result'=>false, 'message'=>'Lookup failed using '.$using));
			die();
		}

		echo json_encode(array('result'=>true, 'message'=>'Success '.$resHost.' = '.$resText.' using '.$using, 'debug'=>$res));
		die();

	}
}
