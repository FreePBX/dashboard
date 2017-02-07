<?php

namespace FreePBX\modules\Dashboard;

class Netmon {
	public $output = "/dev/shm/netusage";
	public $watch = "/dev/shm/running";
	public $log = "/dev/shm/netmon-log";
	public $script = false; 

	public function __construct() {
		$this->script = __DIR__."/../netmon.php";
		if (!file_exists($this->script)) {
			throw new \Exception("Error! Netmon script '$netmon' doesn't exist");
		}

		// Touch the running script, so netmon knows we're here.
		touch($this->watch);

		// If it's not currently running, start it.
		if (!file_exists($this->output)) {
			$this->startScript();
		}
	}

	private function startScript() {
		exec($this->script." > ".$this->log." &");
	}

	public function getStats() {
		if (!file_exists($this->output)) {
			// Odd...
			return [];
		}
		$retarr = [];
		$stats = file($this->output);
		foreach ($stats as $line) {
			$tmp = @json_decode($line, true);
			// If it's not an array, something's derped
			if (!is_array($tmp)) {
				continue;
			}
			$retarr[$tmp['timestamp']] = $tmp['data'];
		}
		// Only return the last 50
		return array_slice($retarr, -50, 50, true);
	}
}

