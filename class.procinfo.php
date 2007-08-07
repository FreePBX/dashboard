<?php

class procinfo {
	var $distro;
	
	function procinfo($distro = false) {
		$this->distro = $distro;
	}
	
	function check_port($port, $server = "localhost") {
		$timeout = 5;
		if ($sock = @fsockopen($server, $port, $errno, $errstr, $timeout)) {
			fclose($sock);
			return true;
		}
		return false;
	}
	
	function check_fop_server() {
		return $this->check_port(4445);
	}
	
	function check_mysql($hoststr) {
		$host = 'localhost';
		$port = '3306';
		if (preg_match('/^([^:]+)(:(\d+))?$/',$hoststr,$matches)) {
			// matches[1] = host, [3] = port
			$host = $matches[1];
			if (!empty($matches[3])) {
				$port = $matches[3];
			}
		}
		return $this->check_port($port, $host);
	}
}

?>
