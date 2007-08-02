<?php

class astinfo {
	var $astman;
	
	function astinfo(&$astman) {
		$this->astman =& $astman;
	}
	
	function get_channel_totals() {
		if (!$this->astman) {
			return array(
				'external_calls'=>0,
				'internal_calls'=>0,
				'total_calls'=>0,
				'total_channels'=>0,
			);
		}
		$response = $this->astman->send_request('Command',array('Command'=>"show channels"));
		$astout = explode("\n",$response['data']);
		
		$external_calls = 0;
		$internal_calls = 0;
		$total_calls = 0;
		$total_channels = 0;
		
		foreach ($astout as $line) {
			if (preg_match('/s@macro-dialout/', $line)) {
				$external_calls++;
			} else if (preg_match('/s@macro-dial:/', $line)) {
				$internal_calls++;
			} else if (preg_match('/^(\d+) active channel/i', $line, $matches)) {
				$total_channels = $matches[1];
			} else if (preg_match('/^(\d+) active call/i', $line, $matches)) {
				$total_calls = $matches[1];
			}
		}
		return array(
			'external_calls'=>$external_calls,
			'internal_calls'=>$internal_calls,
			'total_calls'=>$total_calls,
			'total_channels'=>$total_channels,
		);
	}
	
	function get_peers() {
		
		$return = array(
			'sip_total' => 0,
			'sip_online' => 0,
			'sip_offline' => 0,
			'sip_online_monitored' => 0,
			'sip_offline_monitored' => 0,
			'sip_online_unmonitored' => 0,
			'sip_offline_unmonitored' => 0,
			'iax2_total' => 0,
			'iax2_online' => 0,
			'iax2_offline' => 0,
			'iax2_unmonitored' => 0
		);

		if (!$this->astman) {
			return $return;
		}

		$response = $this->astman->send_request('Command',array('Command'=>"sip show peers"));
		$astout = explode("\n",$response['data']);
		
		foreach ($astout as $line) {
			if (preg_match('/(\d+) sip peers? \[(\d+) online.*(\d+) offline/i', $line, $matches)) {
				// ast 1.2
				$return['sip_total'] = $matches[1];
				$return['sip_online'] = $matches[2];
				$return['sip_offline'] = $matches[3];
			} else if (preg_match('/(\d+) sip peers? \[Monitored.*(\d+) online.*(\d+) offline.*Unmonitored.*(\d+) online.*(\d+) offline/', $line, $matches)) {
				// asterisk 1.4
				// 2 sip peers [Monitored: 1 online, 1 offline Unmonitored: 0 online, 0 offline]
				$return['sip_total'] = $matches[1];
				$return['sip_online'] = $matches[2] + $matches[4];
				$return['sip_offline'] = $matches[3] + $matches[5]; 
				
				$return['sip_online_monitored'] = $matches[2];
				$return['sip_offline_monitored'] = $matches[3];
				$return['sip_online_unmonitored'] = $matches[4];
				$return['sip_offline_unmonitored'] = $matches[5];
			}
		}
		
		
		$response = $this->astman->send_request('Command',array('Command'=>"iax2 show peers"));
		$astout = explode("\n",$response['data']);
		foreach ($astout as $line) {
			if (preg_match('/(\d+) iax2 peers? \[(\d+) online.*(\d+) offline.*(\d+) unmonitored/i', $line, $matches)) {
				$return['iax2_total'] = $matches[1];
				$return['iax2_online'] = $matches[2];
				$return['iax2_offline'] = $matches[3];
				$return['iax2_unmonitored'] = $matches[4];
			}
		}
		
		$return['online'] = $return['sip_online'] + $return['iax2_online'];
		$return['offline'] = $return['sip_offline'] + $return['iax2_offline'];
		$return['total'] = $return['sip_total'] + $return['iax2_total'];
		
		return $return;
	}
	
	function get_registrations() {
		
		$return = array(
			'sip_registered' => 0,
			'sip_total' => 0,
			'iax2_registered' => 0,
			'iax2_total' => 0,
			'registered' => 0,
			'total' => 0,
		);

		if (!$this->astman) {
			return $return;
		}
		
		$response = $this->astman->send_request('Command',array('Command'=>"sip show registry"));
		$astout = explode("\n",$response['data']);
		
		$pos = false;
		foreach ($astout as $line) {
			if ($pos===false) {
				$pos = strpos($line,"State");
			} else {
				if (substr($line,$pos,10) == "Registered") {
					$return['sip_registered']++;
				}
				$return['sip_total']++;
			}
		}
		
		
		
		$response = $this->astman->send_request('Command',array('Command'=>"iax2 show registry"));
		$astout = explode("\n",$response['data']);
		
		$pos = false;
		foreach ($astout as $line) {
			if ($pos===false) {
				$pos = strpos($line,"State");
			} else {
				if (substr($line,$pos,10) == "Registered") {
					$return['iax2_registered']++;
				}
				$return['iax2_total']++;
			}
		}
		
		$return['registered'] = $return['sip_registered'] + $return['iax2_registered'];
		$return['total'] = $return['sip_total'] + $return['iax2_total'];
	}
	
	function get_uptime() {
		/*
		System uptime: 1 week, 4 days, 22 hours, 29 minutes, 21 seconds
		Last reload: 1 week, 1 day, 6 hours, 14 minutes, 49 seconds
		*/
		$output = array(
			'system' => '',
			'reload' => '',
		);

		if (!$this->astman) {
			return $output;
		}

		$response = $this->astman->send_request('Command',array('Command'=>"show uptime"));
		$astout = explode("\n",$response['data']);
			
		foreach ($astout as $line) {
			if (preg_match('/^System uptime: (.*)$/i',$line,$matches)) {
				$output["system"] = preg_replace('/,\s+(\d+ seconds?)?\s*$/', '', $matches[1]);				
			} else if (preg_match('/^Last reload: (.*)$/i',$line,$matches)) {
				$output["reload"] = preg_replace('/,\s+(\d+ seconds?)?\s*$/', '', $matches[1]);
			}
		}
		
		return $output;
	}
	
	function check_asterisk() {
		if (!$this->astman) {
			return false;
		}
		$response = $this->astman->send_request('Command',array('Command'=>"show version"));
		$astout = explode("\n",$response['data']);
		
		if (!preg_match('/^Asterisk /i', $astout[1])) {
			return false;
		} else {
			return $astout[1];
		}
	}
}

?>
