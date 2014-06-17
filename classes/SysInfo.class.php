<?php
// vim: set ai ts=4 sw=4 ft=php:
// Wrapper for PhpSysInfo
// Note: Licence under AGPL v3+ -and- GPL v2

class SysInfo {
	private static $obj = false;
	private $psi = false;
	private $astinfo = false;

	public static function create() {
		if (!self::$obj) {
			self::$obj = new SysInfo();
		}
		return self::$obj;
	}

	private $flat;

	private function initPSI() {
		if (!$this->psi) {
			// Load PSI Object
			if (class_exists('OS')) {
				throw new Exception('Something has already loaded the PhpSysInfo classes. Fix this.');
			}
			// Cast the correct incantions to let PSI Init.
			$path = __DIR__."/phpsysinfo/";
			define('APP_ROOT', $path);
			include "$path/includes/autoloader.inc.php";
			spl_autoload_register('psi_autoloader');
			include "$path/config.php";
			spl_autoload_unregister('psi_autoloader');
			$this->psi = true;
		}
	}


	public function getSysInfo() {
		$this->flat = array();

		$this->initPSI();
		spl_autoload_register('psi_autoloader');
		$x = new WebpageXML(true, null);
		$xml = $x->getXMLObject()->getXML();
		spl_autoload_unregister('psi_autoloader');
		$this->fixPSI($xml);
		$this->getAstinfo();
		return $this->flat;
	}

	private function fixPSI($xml) {
		// Flatten everything.
		foreach ((array) $xml as $key => $val) {
			$this->flatten("psi.$key", $val);
		}

		// Make the timestamp more visible
		$this->flat['timestamp'] = $this->flat['psi.Generation.@attributes.timestamp'];

		// Explode out the load average
		list ($five, $ten, $fifteen) = explode(' ', $this->flat['psi.Vitals.@attributes.LoadAvg']);
		$this->flat['psi.Vitals.@attributes.LoadAvg.five']    = $five;
		$this->flat['psi.Vitals.@attributes.LoadAvg.ten']     = $ten;
		$this->flat['psi.Vitals.@attributes.LoadAvg.fifteen'] = $fifteen;
	}


	private function flatten($key, $val) {
		if (is_array($val) || is_object($val)) {
			foreach ((array)$val as $k => $v) {
				$this->flatten("$key.$k", $v);
			}
		} else {
			$this->flat[$key] = $val;
		}
	}

	public function getAstInfo() {

		if (!class_exists('AsteriskInfo')) {
			include 'AsteriskInfo.class.php';
			$this->astinfo = new AsteriskInfo();
		}

		if ($this->astinfo) {
			$retarr['chan_totals'] = $this->astinfo->get_channel_totals();
			$retarr['connections'] = $this->astinfo->get_connections();
			$retarr['uptime'] = $this->astinfo->get_uptime();
		} else {
			$retarr['chan_totals'] = -1;
			$retarr['connections'] = -1;
			$retarr['uptime'] = -1;
		}

		foreach ($retarr as $key => $val) {
			$this->flatten("ast.$key", $val);
		}
	}

	public function getAvg() { return false; }
}


