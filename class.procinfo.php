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
	
	function check_mysql() {
		return $this->check_port(3306);
	}
}

?>