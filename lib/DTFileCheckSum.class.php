<?php

class DTFileCheckSum {

    private $counter;
    private $sum;
    private $files;
	private $_pluginsDir;

    function __construct() {
        $this->counter = 0;
        $this->sum = '';
		$this->_pluginsDir=DTPLUGINBASE;
		$ul=wp_upload_dir();
		$this->_uploadsDir=$ul['basedir'];
    }

    function listFolderFiles($dir) {
        $ffs = scandir($dir);

        foreach ($ffs as $ff) {
            if ($ff != '.' && $ff != '..') {
				$fullPath=implode(DIRECTORY_SEPARATOR, array($dir, $ff));
				$fullPath=str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $fullPath);

                if (!is_dir($fullPath)) {
                    $sum = md5_file($fullPath);
                   
                    $this->files[$fullPath] = $sum;
                    $this->counter++;
                    $this->sum.= $sum;
                } else {
					if ($fullPath != $this->_uploadsDir) {
                    	$this->listFolderFiles($fullPath);
					}
                }

             
            }
        }
    }

    public function getCounter() {
        return $this->counter;
    }

    public function getSum() {
        return $this->sum;
    }

    public function getFiles() {
        return $this->files;
    }

	public function getChanges($options) {

		$limit = (isset($options['limit'])) ? $options['limit'] : 10;
		$offset = (isset($options['offset'])) ? $options['limit'] : 0;
		$changeset = (isset($options['changeset'])) ? $options['changeset'] : null;

		$data=get_option(DTFILECHECKLOG);

		if (!is_array($data))
			return array();

		$returnVar = array();
		$changeset=0;


		foreach ($data as $row) {
			$returnVar[] = (object) array (
									'date' =>$row[0],
									'filecount' =>$row[1],
									'md5' =>$row[2],
									'filechanges' =>$row[3],
									'changeset' => $changeset
									);
			$changeset++;
		}

		return array_reverse($returnVar);
	}

	public function runCron() {

		$this->listFolderFiles(ABSPATH);
		$info = get_option(DTFILECHECKLOG);
		$infoOld = $info;

		$date = new DateTime();
		$lastFiles = get_option(DTFILECHECKLOGLIST);

		$currentFiles=$this->getFiles();
		if ($infoOld[0][2] != md5($this->getSum())) {
			$diff1 = array_diff_assoc($currentFiles, $lastFiles);
			$diff2 = array_diff_assoc($lastFiles, $currentFiles);
			$diff = array_merge($diff1, $diff2);
		}

		// Altered or deleted?
		$diffFiltered=array();
		foreach ($diff as $thisFile => $md5sum) {
			$diffFiltered[$thisFile]=array();
			if (isset($currentFiles[$thisFile])) {
				if (!isset($lastFiles[$thisFile])) {
					$diffFiltered[$thisFile]['change'] = 'ADDED';
					$diffFiltered[$thisFile]['md5current']=$currentFiles[$thisFile];
				} else {
					$diffFiltered[$thisFile]['change'] = 'ALTERED';
					$diffFiltered[$thisFile]['md5current']=$currentFiles[$thisFile];
					$diffFiltered[$thisFile]['md5previous']=$lastFiles[$thisFile];
				}
			} else {
				$diffFiltered[$thisFile]['change'] = 'DELETED';
			}
		}

		$info[] = array($date->format('Y-m-d H:i:s'), $this->getCounter(), md5($this->getSum()), $diffFiltered);

		/*$shortenedInfo=array();
		$totalLines=0;
		arsort($info);
		foreach ($info as $key => $vals) {
			var_dump($key);
			var_dump($vals);

			$totalLines+=count($vals[3]);

			if ($totalLines > 100 && count($shortenedInfo) != 0) {
				break;
			}

			$shortenedInfo[]=$vals;

		}
		arsort($shortenedInfo); */
		update_option(DTFILECHECKLOG, $info);
		update_option(DTFILECHECKLOGLIST, $currentFiles);
	}

	function findHooks($givenHooks)
	{
		global $wp_filter;

		$this->listFolderFiles($this->_pluginsDir);
		$files=$this->getFiles();
		$return=array();
		foreach ($givenHooks as $searchHook)
		{
			if (!isset($wp_filter[$searchHook]))
				continue;

			foreach ($wp_filter[$searchHook] as $hookkey => $hookArray)
			{
				foreach ($hookArray as $functionName => $functionValues)
				{

					$hookIsClass=false;
					if (gettype($functionValues['function']) == "string") {
						$searchFor = $functionName;
					} else {
						$rawName = print_r($functionValues['function'][0], true);
						$searchFor = substr($rawName, 0, strpos($rawName, ' Object'));
						$hookIsClass=true;
					}

					$pluginFile=false;
					foreach ($files as $filename => $md5) {
						if (strpos($filename, 'php') === false)
							continue;

						$contents = file_get_contents($filename);
						if (strpos($contents, $searchFor) !== false) {
							$pluginFile=$filename;
							break;
						}
					}
					reset($files);

					$pluginName=$this->getPluginName($pluginFile);

					$return[] = (object)array(
												'hook' => $searchHook,
												'class' => $hookIsClass,
												'function' => $searchFor,
												'plugin' => $pluginName,
												'file' => $pluginFile
											);
				}
			}
		}

		return $return;
	}

	function findFunctions($givenFunctions)
	{

		$this->listFolderFiles($this->_pluginsDir);
		$files=$this->getFiles();
		$return=array();
		$pluginOutput=array();

		foreach ($files as $filename => $md5) {

			if (strpos($filename, 'php') === false)
				continue;

			try
			{
				$contents = file_get_contents($filename);
			} catch (Exception $e) { 
				error_log('Failed to open file. '.$e->getMessage());	
				next;
			}

			if ($contents===false) {
				error_log('file_get_contents() returns false. '.$e->getMessage());	
				next;
			}

			$contents=str_replace(' ', '', $contents);
	
			$matches=array();
			$relFileName = str_replace($this->_pluginsDir, '', $filename);
			preg_match('/\/([a-zA-Z0-9\-_]*)/', $relFileName, $matches);
			$pluginParentDir = $matches[1];

			foreach ($givenFunctions as $searchFunction)
			{
				if (strpos($contents, $searchFunction) !== false) // && strpos($filename, 'diagnostic-tool') === false)
				{
					$pluginLoc=str_replace($this->_pluginsDir, '', $filename);
					$pluginOutput[] = array($searchFunction.')', $pluginLoc, $pluginParentDir);
				}
			}
		}

		foreach ($pluginOutput as $key => $values)
		{
			$pluginName=$this->getPluginName($values[1]);

			$return[] = (object) array(
									'function' => $values[0],
									'file' => $values[1],
									'plugin' => $pluginName
							);
		}

		return $return;
	}

	function getPluginName($file)
	{

		$e=explode('/', $file);
		$checkFile=implode('/', array($this->_pluginsDir, $e[1], $e[1].'.php'));

		if (!file_exists($checkFile)) {
			$checkFile=implode('/', array($this->_pluginsDir, $e[1], str_replace('-', '_', $e[1]).'.php'));
			if (!file_exists($checkFile)) {
				return 'Unknown';
			}
		}

		$thePluginName='Unknown';
		try {
			$pluginData=get_plugin_data($checkFile);
			$thePluginName=$pluginData['Name'];
		} catch (Exception $e) {
			$thePluginName='Unknown';
		}
		if ($thePluginName=='') {
			$thePluginName='Unknown';
		}

		return $thePluginName;
	}

}
