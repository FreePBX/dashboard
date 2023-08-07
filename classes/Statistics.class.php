<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

class Statistics {

	public $width = 445;

	public function getStats() {
		if (!isset($_REQUEST['target']) || !isset($_REQUEST['period'])) {
			return _('Invalid Selection');
		}
		$t = $_REQUEST['target'];

		$defs = ["hour" => "MINUTES", "day" => "HALFHR", "week" => "QTRDAY", "month" => "DAY"];

		if (!isset($defs[strtolower((string) $_REQUEST['period'])])) {
			return _('Invalid period');
		}

		$settings = $defs[strtolower((string) $_REQUEST['period'])];
  return match ($t) {
      'uptime' => $this->getGraphDataUptime($settings),
      'cpuusage' => $this->getGraphDataCPU($settings),
      'diskusage' => $this->getGraphDataDisk($settings),
      'networking' => $this->getGraphDataNet($settings),
      'memusage' => $this->getGraphDataMem($settings),
      'asterisk' => $this->getGraphDataAst($settings),
      default => 'Code not written',
  };
	}

	public function getGraphDataUptime($period) {
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$xvfs = $this->getDateFormatString($period);
		$retarr = ["width" => $this->width, "toolTip" => ["shared" => true], "axisX" => ["valueFormatString" => " ", "tickLength" => 0], "axisY" => ["valueFormatString" => " ", "tickLength" => 0], "legend" => ["verticalAlign" => "bottom", "horizontalAlign" => "left"], "data" => [0 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => _("System"), "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1], 1 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "Asterisk", "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1], 2 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => _("Since Reload"), "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1]]];
		if (!class_exists('TimeUtils')) {
			include 'TimeUtils.class.php';
		}
		$count=0;
		foreach ($si as $utime => $row) {
			$key = $utime * 1000;
			$sysuptime = isset($row['psi.Vitals.@attributes.Uptime']) ? (int) $row['psi.Vitals.@attributes.Uptime'] : 0;
			$astuptime = isset($row['ast.uptime.system-seconds']) ? (int) $row['ast.uptime.system-seconds'] : 0;
			$astreload = isset($row['ast.uptime.reload-seconds']) ? (int) $row['ast.uptime.reload-seconds'] : 0;
			$retarr['data'][0]['dataPoints'][$count] = ["x" => $key, "y" => $sysuptime, "toolTipContent" => "<span style='color: {color};'>{name}: ".TimeUtils::getReadable($sysuptime, 3)."</span>"];
			$retarr['data'][1]['dataPoints'][$count] = ["x" => $key, "y" => $astuptime, "toolTipContent" => "<span style='color: {color};'>{name}: ".TimeUtils::getReadable($astuptime, 3)."</span>"];
			$retarr['data'][2]['dataPoints'][$count] = ["x" => $key, "y" => $astreload, "toolTipContent" => "<span style='color: {color};'>{name}: ".TimeUtils::getReadable($astreload, 3)."</span>"];
			$count++;
		}
		return $retarr;
	}

	public function getGraphDataCPU($period) {
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$xvfs = $this->getDateFormatString($period);
		$retarr = ["width" => $this->width, "toolTip" => ["shared" => true], "axisX" => ["valueFormatString" => " ", "tickLength" => 0], "legend" => ["verticalAlign" => "bottom", "horizontalAlign" => "left"], "data" => [0 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "5 Min Average", "type" => "line", "showInLegend" => true, "dataPoints" => []], 1 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "10 Min Average", "type" => "line", "showInLegend" => true, "dataPoints" => []], 2 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "15 Min Average", "type" => "line", "showInLegend" => true, "dataPoints" => []], 3 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "CPU Temperature", "type" => "column", "showInLegend" => false, "dataPoints" => []]]];

		$foundtemps = false;
		$cputemp = [];

		$count=0;
		foreach ($si as $utime => $row) {
			$key = $utime * 1000;
			if (!isset($row['psi.Vitals.@attributes.LoadAvg.five'])) {
				$retarr['data'][0]['dataPoints'][$count] = ["x" => $key, "y" => null];
				$retarr['data'][1]['dataPoints'][$count] = ["x" => $key, "y" => null];
				$retarr['data'][2]['dataPoints'][$count] = ["x" => $key, "y" => null];
				$retarr['data'][3]['dataPoints'][$count] = ["x" => $key, "y" => null];
			} else {
				$retarr['data'][0]['dataPoints'][$count] = ["x" => $key, "y" => round($row['psi.Vitals.@attributes.LoadAvg.five'], 2)];
				$retarr['data'][1]['dataPoints'][$count] = ["x" => $key, "y" => round($row['psi.Vitals.@attributes.LoadAvg.ten'], 2)];
				$retarr['data'][2]['dataPoints'][$count] = ["x" => $key, "y" => round($row['psi.Vitals.@attributes.LoadAvg.fifteen'], 2)];
			}

			if (isset($row['psi.Hardware.CPU.CpuCore.0.@attributes.CpuTemp'])) {
				$retarr['data'][3]['dataPoints'][$count] = ["x" => $key, "y" => $row['psi.Hardware.CPU.CpuCore.0.@attributes.CpuTemp']];
				$retarr['data'][3]['showInLegend'] = true;
			}
			$count++;	
		}
		return $retarr;
	}

	public function getGraphDataDisk($period) {
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$retarr = ["width" => $this->width, "toolTip" => ["shared" => true], "axisX" => ["valueFormatString" => " ", "tickLength" => 0], "axisY" => ["interval" => 25, "maximum" => 100, "valueFormatString" => "#'%'"], "legend" => ["verticalAlign" => "bottom", "horizontalAlign" => "left"], "data" => []];

		// Discover my disk names and mountpoints.
		$lastsi = FreePBX::create()->Dashboard->getSysInfo();
		$disks = [];
		foreach ($lastsi as $key => $val) {
			if (str_starts_with((string) $key, "psi.FileSystem.Mount.")) {
				$tmparr = explode('.', (string) $key);
				$disks[$tmparr[3]] = [];
			}
		}

		// Get the identity and type of all the current disks
		$vars = ['Name', 'MountPoint'];
		// This is our graph index
		$index = 0;
		foreach (array_keys($disks) as $d) {
			foreach ($vars as $v) {
				$disks[$d][$v] = $lastsi['psi.FileSystem.Mount.'.$d.'.@attributes.'.$v];
			}

			// We don't care about tmpfs's
			if ($disks[$d]['Name'] == "tmpfs" || $disks[$d]['Name'] == "devtmpfs") {
				unset($disks[$d]);
				continue;
			}

			// Set our graph index for this disk
			$disks[$d]['index'] = $index++;
		}

		$xvfs = $this->getDateFormatString($period);
		// Build the retarr
		foreach ($disks as $id => $val) {
			$retarr['data'][$val['index']] = ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => $val['MountPoint'].' ('.$val['Name'].')', "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1];
		}

		// Now, generate the graph!
		$count = 0;
		foreach ($si as $utime => $row) {
			$key = $utime * 1000;
			// Loop through our known disks and get the percentage used
			foreach ($disks as $diskid => $val) {
				$keyval = "psi.FileSystem.Mount.$diskid.@attributes.Percent";
				$retarr['data'][$val['index']]['dataPoints'][$count] = ["x" => $key, "y" => (int) $row[$keyval]];
			}
			$count++;
		}
		return $retarr;
	}

	public function getGraphDataNet($period) {

		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);

		$retarr = ["width" => $this->width, "toolTip" => ["shared" => true], "axisX" => ["valueFormatString" => " ", "tickLength" => 0], "legend" => ["verticalAlign" => "bottom", "horizontalAlign" => "left"], "dataPointMinWidth" => 5, "data" => []];

		// This is a temporary holding array that's rebuilt before being handed to the
		// graphing software. This is done because the device ID may change from run to
		// run, so we can't trust it.
		$interfaces = [];
		$bytes = [];
		foreach ($si as $utime => $tmparr) {
			$key = $utime * 1000;
			// Loop through tmparr finding all our network related bits
			foreach ($tmparr as $k => $v) {
				if (preg_match("/psi.Network.NetDevice.(\d+).@attributes.([TR]xBytes)/", (string) $k, $out)) {
					// Save our bytecount for mangling.
					$netid = $out[1];
					$txrx = $out[2];
					$bytes[$key][$netid][$txrx] = $v;
				} elseif (preg_match("/psi.Network.NetDevice.(\d+).@attributes.(\w+)/", (string) $k, $out)) {
					// Otherwise if it's anything else, keep it.
					$netid = $out[1];
					$section = $out[2];
					$interfaces[$key][$netid][$section] = $v;
				}
			}
		}

		// Now we need to re-run through that array and use the *name* as the authoritative
		// source, not the id that was discovered in the previous step
		$data = [];
		foreach ($interfaces as $key => $tmparr) {
			// tmparr contains any number of interfaces.
			foreach ($tmparr as $netid => $int) {
				if ($int['Name'] == 'lo') {
					// We never care about lo
					continue;
				}
				// It's a real interface. Woo.
				$data[$int['Name']][$key] = $bytes[$key][$netid] ?? ["TxBytes" => 0, "RxBytes" => 0];
			}
		}

		// Now loop through our data array (sigh, at least it's n*3 not n^3) and generate
		// the actual data to send to the graph.
		$count = 0;
		$xvfs = $this->getDateFormatString($period);
		foreach ($data as $name => $tmparr) {
			$txid = $count++;
			$rxid = $count++;
			$retarr['data'][$txid] = ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "$name TX MB", "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1];
			$retarr['data'][$rxid] = ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "$name RX MB", "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1];
			// Take the first value as the starting point
			$lastval = false;
			foreach ($tmparr as $time => $bytearr) {
				if ($lastval === false) {
					$lastval = $bytearr;
				}
				// Now add the bytes to the dataPoints array
				$txdiff = $bytearr['TxBytes'] - $lastval['TxBytes'];
				if ($txdiff < 0) {
					// If it's negative, then just set it to the last value. The machine
					// may have rebooted, or it may have wrapped around. We're not caring about
					// exact numbers, this is just good enough to get a feeling.
					$txdiff = $bytearr['TxBytes'];
				}
				$retarr['data'][$txid]['dataPoints'][] = ["x" => $time, "y" => round($txdiff / 1024 / 1024, 2)];
				$rxdiff = $bytearr['RxBytes'] - $lastval['RxBytes'];
				if ($rxdiff < 0) {
					// Same here.
					$rxdiff = $bytearr['RxBytes'];
				}
				$retarr['data'][$rxid]['dataPoints'][] = ["x" => $time, "y" => round($rxdiff / 1024 / 1024, 2)];
				$lastval = $bytearr;
			}
		}
		return $retarr;
	}

	public function getGraphDataMem($period) {
		// Grab our memory info...
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$xvfs = $this->getDateFormatString($period);
		$retarr = ["width" => $this->width, "toolTip" => ["shared" => true], "axisX" => ["valueFormatString" => " ", "tickLength" => 0], "axisY" => ["valueFormatString" => " ", "tickLength" => 0, "interval" => 10], "legend" => ["verticalAlign" => "bottom", "horizontalAlign" => "left"], "dataPointMinWidth" => 5, "data" => [0 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "% In Use", "type" => "stackedColumn100", "showInLegend" => true, "dataPoints" => []], 1 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "% Buffers", "type" => "stackedColumn100", "showInLegend" => true, "dataPoints" => []], 2 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "% Cache", "type" => "stackedColumn100", "showInLegend" => true, "dataPoints" => []], 3 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "% Unused", "type" => "stackedColumn100", "showInLegend" => true, "dataPoints" => []], 4 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "% Swap Utilized", "type" => "line", "color" => "red", "showInLegend" => true, "dataPoints" => []]]];
		$count = 0;
		foreach ($si as $utime => $val) {
			$key = $utime * 1000;
			if (!isset($val['psi.Memory.@attributes.Percent'])) {
				$retarr['data'][0]['dataPoints'][$count] = ["x" => $key, "y" => null];
				$retarr['data'][1]['dataPoints'][$count] = ["x" => $key, "y" => null];
				$retarr['data'][2]['dataPoints'][$count] = ["x" => $key, "y" => null];
				$retarr['data'][3]['dataPoints'][$count] = ["x" => $key, "y" => null];
				$retarr['data'][4]['dataPoints'][$count] = ["x" => $key, "y" => null];
			} else {
				$retarr['data'][0]['dataPoints'][$count] = ["x" => $key, "y" => (int) $val['psi.Memory.Details.@attributes.AppPercent']];
				$retarr['data'][1]['dataPoints'][$count] = ["x" => $key, "y" => (int) $val['psi.Memory.Details.@attributes.BuffersPercent']];
				$retarr['data'][2]['dataPoints'][$count] = ["x" => $key, "y" => (int) $val['psi.Memory.Details.@attributes.CachedPercent']];
				$retarr['data'][3]['dataPoints'][$count] = ["x" => $key, "y" => (int) 100 - $val['psi.Memory.@attributes.Percent']];
				if (isset($val['psi.Memory.Swap.@attributes.Percent'])) {
					$retarr['data'][4]['dataPoints'][$count] = ["x" => $key, "y" => (int) $val['psi.Memory.Swap.@attributes.Percent']];
				} else {
					$retarr['data'][4]['dataPoints'][$count] = ["x" => $key, "y" => 0];
				}
			}
			$count++;
		}
		return $retarr;
	}

	public function getGraphDataAst($period) {
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$xvfs = $this->getDateFormatString($period);
		$retarr = ["width" => $this->width, "toolTip" => ["shared" => true], "axisX" => ["valueFormatString" => " ", "tickLength" => 0], "axisY" => ["interval" => 10], "legend" => ["verticalAlign" => "bottom", "horizontalAlign" => "left"], "data" => [0 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "Users Online", "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1], 1 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "Users Offline", "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1], 2 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "Trunks Online", "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1], 3 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "name" => "Trunks Offline", "type" => "line", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1], 4 => ["xValueType" => "dateTime", "xValueFormatString" => $xvfs, "legendText" => "Channels In Use", "name" => "In Use", "type" => "column", "showInLegend" => true, "dataPoints" => [], "markerSize" => 1]]];
		$count = 0;
		$trunkoffline = false;
		$uonline = [];
		$uoffline = [];
		$tonline = [];
		$toffline = [];
		$channels = [];
		$timestamps = [];
		foreach ($si as $utime => $val) {
			$key = $utime * 1000;
			$timestamps[$count] = $key;
			if (!isset($val['ast.connections.users_online'])) {
				$uonline[$count] = ["x" => $key, "y" => null];
				$uoffline[$count] = ["x" => $key, "y" => null];
				$tonline[$count] = ["x" => $key, "y" => null];
				$toffline[$count] = ["x" => $key, "y" => null];
				$channels[$count] = ["x" => $key, "y" => null];
			} else {
				$uonline[$count] = ["x" => $key, "y" => (int) $val['ast.connections.users_online']];
				$uoffline[$count] = ["x" => $key, "y" => (int) $val['ast.connections.users_offline']];
				$tonline[$count] = ["x" => $key, "y" => (int) $val['ast.connections.trunks_online']];
				if ($val['ast.connections.trunks_offline'] != 0) {
					if (!$trunkoffline) {
						$trunkoffline = true;
						if ($count > 1) {
							$toffline[$count-1] = 0;
						}
					}
					$toffline[$count] = ["x" => $key, "y" => (int) $val['ast.connections.trunks_offline']];
				} else {
					// We only want to render a line to zero immediately after it was not zero, so the line
					// goes back down the bottom of the graph before vanishing.
					if ($trunkoffline) {
						$toffline[$count] = ["x" => $key, "y" => 0];
						$trunkoffline = false;
					} else {
						// $retarr['values']['toffline'][$count] = null;
					}
				}
				$channels[$count] = ["x" => $key, "y" => (int) $val['ast.chan_totals.total_calls']];
			}
			$count++;
	   	}
		$retarr['data'][0]['dataPoints'] = $uonline;
		$retarr['data'][1]['dataPoints'] = $uoffline;
		$retarr['data'][2]['dataPoints'] = $tonline;
		$retarr['data'][3]['dataPoints'] = $toffline;
		$retarr['data'][4]['dataPoints'] = $channels;
		return $retarr;
	}

	private function getDateFormatString($period) {
		return match ($period) {
      'MINUTES' => 'MMM DD HH:mm:ss',
      'DAY' => 'MMM DD YYYY',
      default => 'MMM DD YYYY HH:mm',
  };
	}

}
