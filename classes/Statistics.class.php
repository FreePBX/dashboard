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

		$defs = array(
			"hour" => "MINUTES",
			"day" => "HALFHR",
			"week" => "QTRDAY",
			"month" => "DAY",
		);

		if (!isset($defs[strtolower($_REQUEST['period'])])) {
			return _('Invalid period');
		}

		$settings = $defs[strtolower($_REQUEST['period'])];
		// We've been asked for data to generate a graph!
		switch ($t) {
		case 'uptime':
			return $this->getGraphDataUptime($settings);
		case 'cpuusage':
			return $this->getGraphDataCPU($settings);
		case 'diskusage':
			return $this->getGraphDataDisk($settings);
		case 'networking':
			return $this->getGraphDataNet($settings);
		case 'memusage':
			return $this->getGraphDataMem($settings);
		case 'asterisk':
			return $this->getGraphDataAst($settings);
		}
		// Or else...
		return 'Code not written';
	}

	public function getGraphDataUptime($period) {
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);

		if (!class_exists('TimeUtils')) {
			include 'TimeUtils.class.php';
		}

		$tooltips = array();
		$sysuptime = array();
		$astuptime = array();
		$astreload = array();
		foreach ($si as $key => $row) {
			if (!isset($row['ast.uptime.system-seconds'])) {
				$us = 0;
				$rs = 0;
				$ut = 0;
			} else {
				$us = $row['ast.uptime.system-seconds'];
				$rs = $row['ast.uptime.reload-seconds'];
				$ut = $row['psi.Vitals.@attributes.Uptime'];
			}

			$ttip = date('c', $key)."<br>";
			$ttip .= _("System").":<br>&nbsp;&nbsp; ".TimeUtils::getReadable($ut, 3)."<br>";
			$ttip .= "Asterisk:<br>&nbsp;&nbsp; ".TimeUtils::getReadable($us, 3)."<br>";
			$ttip .= _("Since Reload").":<br>&nbsp;&nbsp; ".TimeUtils::getReadable($rs, 3);
			$tooltips[] = $ttip;

			$sysuptime[] = $ut;
			$astuptime[] = $us;
			$astreload[] = $rs;
		}

		$retarr['template'] = 'aststat';
		$retarr['tooltips'] = $tooltips;
		$retarr['values'] = array( "sysuptime" => $sysuptime, "astuptime" => $astuptime, "astreload" => $astreload );
		$retarr['series'] = array(
			"sysuptime" => array( "color" => "red", "axis" => "l" ),
			"astuptime" => array( "color" => "green", "axis" => "l" ),
			"astreload" => array( "color" => "blue", "axis" => "r" ),
		);
		$retarr['axis'] = array(
			"r" => array("title" => _("Reload"), "titleDistance" => 8 ),
			"l" => array("title" => _("System"), "titleDistance" => 8 ),
		);

		$retarr['legend'] = array(
			"sysuptime" => _('System Uptime'),
			"astuptime" => _('Asterisk Uptime'),
			"astreload" => _('Since Reload'),
		);
		return $retarr;
	}

	public function getGraphDataCPU($period) {
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$retarr = array(
			"width" => $this->width,
			"toolTip" => array("shared" => true),
			"axisX" => array("valueFormatString" => " ", "tickLength" => 0),
			"legend" => array("verticalAlign" => "bottom", "horizontalAlign" => "left"),
			"data" => array(
				0 => array(
					"xValueType" => "dateTime",
					"name" => "5 Min Average",
					"type" => "line",
					"showInLegend" => true,
					"dataPoints" => array(),
				),
				1 => array(
					"xValueType" => "dateTime",
					"name" => "10 Min Average",
					"type" => "line",
					"showInLegend" => true,
					"dataPoints" => array(),
				),
				2 => array(
					"xValueType" => "dateTime",
					"name" => "15 Min Average",
					"type" => "line",
					"showInLegend" => true,
					"dataPoints" => array(),
				),
				3 => array(
					"xValueType" => "dateTime",
					"name" => "CPU Temperature",
					"type" => "column",
					"showInLegend" => false,
					"dataPoints" => array(),
				),
			),
		);

		$foundtemps = false;
		$cputemp = array();

		$count=0;
		foreach ($si as $utime => $row) {
			$key = $utime * 1000;
			if (!isset($row['psi.Vitals.@attributes.LoadAvg.five'])) {
				$retarr['data'][0]['dataPoints'][$count] = array( "x" => $key, "y" => null);
				$retarr['data'][1]['dataPoints'][$count] = array( "x" => $key, "y" => null);
				$retarr['data'][2]['dataPoints'][$count] = array( "x" => $key, "y" => null);
				$retarr['data'][3]['dataPoints'][$count] = array( "x" => $key, "y" => null);
			} else {
				$retarr['data'][0]['dataPoints'][$count] = array( "x" => $key, "y" => round($row['psi.Vitals.@attributes.LoadAvg.five'], 2));
				$retarr['data'][1]['dataPoints'][$count] = array( "x" => $key, "y" => round($row['psi.Vitals.@attributes.LoadAvg.ten'], 2));
				$retarr['data'][2]['dataPoints'][$count] = array( "x" => $key, "y" => round($row['psi.Vitals.@attributes.LoadAvg.fifteen'], 2));
			}

			if (isset($row['psi.Hardware.CPU.CpuCore.0.@attributes.CpuTemp'])) {
				$retarr['data'][3]['dataPoints'][$count] = array( "x" => $key, "y" => $row['psi.Hardware.CPU.CpuCore.0.@attributes.CpuTemp']);
				$retarr['data'][3]['showInLegend'] = true;
			}
			$count++;	
		}
		return $retarr;
	}

	public function getGraphDataDisk($period) {
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$retarr = array(
			"width" => $this->width,
			"toolTip" => array("shared" => true),
			"axisX" => array("valueFormatString" => " ", "tickLength" => 0),
			"axisY" => array("interval" => 25, "maximum" => 100, "valueFormatString" => "#'%'"),
			"legend" => array("verticalAlign" => "bottom", "horizontalAlign" => "left"),
			"data" => array(),
		);

		// Discover my disk names and mountpoints.
		$lastsi = FreePBX::create()->Dashboard->getSysInfo();
		$disks = array();
		foreach ($lastsi as $key => $val) {
			if (strpos($key, "psi.FileSystem.Mount.") === 0) {
				$tmparr = explode('.', $key);
				$disks[$tmparr[3]] = array();
			}
		}

		// Get the identity and type of all the current disks
		$vars = array('Name', 'MountPoint');
		// This is our graph index
		$index = 0;
		foreach (array_keys($disks) as $d) {
			foreach ($vars as $v) {
				$disks[$d][$v] = $lastsi['psi.FileSystem.Mount.'.$d.'.@attributes.'.$v];
			}

			// We don't care about tmpfs's
			if ($disks[$d]['Name'] == "tmpfs") {
				unset($disks[$d]);
				continue;
			}

			// Set our graph index for this disk
			$disks[$d]['index'] = $index++;
		}

		// Build the retarr
		foreach ($disks as $id => $val) {
			$retarr['data'][$val['index']] = array(
				"xValueType" => "dateTime",
				"name" => $val['MountPoint'].' ('.$val['Name'].')',
				"type" => "line",
				"showInLegend" => true,
				"dataPoints" => array(),
				"markerSize" => 1,
			);
		}

		// Now, generate the graph!
		$count = 0;
		foreach ($si as $utime => $row) {
			$key = $utime * 1000;
			// Loop through our known disks and get the percentage used
			foreach ($disks as $diskid => $val) {
				$keyval = "psi.FileSystem.Mount.$diskid.@attributes.Percent";
				$retarr['data'][$val['index']]['dataPoints'][$count] = array( "x" => $key, "y" => (int) $row[$keyval]);
			}
			$count++;
		}
		return $retarr;
	}

	public function getGraphDataNet($period) {

		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);

		$retarr = array(
			"width" => $this->width,
			"toolTip" => array("shared" => true),
			"axisX" => array("valueFormatString" => " ", "tickLength" => 0),
			"legend" => array("verticalAlign" => "bottom", "horizontalAlign" => "left"),
			"dataPointMinWidth" => 5,
			"data" => array(),
		);

		// This is a temporary holding array that's rebuilt before being handed to the
		// graphing software. This is done because the device ID may change from run to
		// run, so we can't trust it.
		$interfaces = array();
		$bytes = array();
		foreach ($si as $utime => $tmparr) {
			$key = $utime * 1000;
			// Loop through tmparr finding all our network related bits
			foreach ($tmparr as $k => $v) {
				if (preg_match("/psi.Network.NetDevice.(\d+).@attributes.([TR]xBytes)/", $k, $out)) {
					// Save our bytecount for mangling.
					$netid = $out[1];
					$txrx = $out[2];
					$bytes[$key][$netid][$txrx] = $v;
				} elseif (preg_match("/psi.Network.NetDevice.(\d+).@attributes.(\w+)/", $k, $out)) {
					// Otherwise if it's anything else, keep it.
					$netid = $out[1];
					$section = $out[2];
					$interfaces[$key][$netid][$section] = $v;
				}
			}
		}

		// Now we need to re-run through that array and use the *name* as the authoritative
		// source, not the id that was discovered in the previous step
		$data = array();
		foreach ($interfaces as $key => $tmparr) {
			// tmparr contains any number of interfaces.
			foreach ($tmparr as $netid => $int) {
				if ($int['Name'] == 'lo') {
					// We never care about lo
					continue;
				}
				// It's a real interface. Woo.
				$data[$int['Name']][$key] = isset($bytes[$key][$netid])?$bytes[$key][$netid]:array("TxBytes" => 0, "RxBytes" => 0);
			}
		}

		// Now loop through our data array (sigh, at least it's n*3 not n^3) and generate
		// the actual data to send to the graph.
		$count = 0;
		foreach ($data as $name => $tmparr) {
			$txid = $count++;
			$rxid = $count++;
			$retarr['data'][$txid] = array(
				"xValueType" => "dateTime",
				"name" => "$name TX MB",
				"type" => "line",
				"showInLegend" => true,
				"dataPoints" => array(),
				"markerSize" => 1,
			);
			$retarr['data'][$rxid] = array(
				"xValueType" => "dateTime",
				"name" => "$name RX MB",
				"type" => "line",
				"showInLegend" => true,
				"dataPoints" => array(),
				"markerSize" => 1,
			);
			// Take the first value as the starting point
			$lastval = false;
			foreach ($tmparr as $time => $bytearr) {
				if ($lastval === false) {
					$lastval = $bytearr;
				}
				// Now add the bytes to the dataPoints array
				$txdiff = $bytearr['TxBytes'] - $lastval['TxBytes'];
				while ($txdiff < 0) {
					$txdiff += pow(2, 31);
				}
				$retarr['data'][$txid]['dataPoints'][] = array("x" => $time, "y" => round($txdiff / 1024 / 1024, 2));
				$rxdiff = $bytearr['RxBytes'] - $lastval['RxBytes'];
				while($rxdiff < 0) {
					$rxdiff += pow(2, 31);
				}
				$retarr['data'][$rxid]['dataPoints'][] = array("x" => $time, "y" => round($rxdiff / 1024 / 1024, 2));
				$lastval = $bytearr;
			}
		}
		return $retarr;
	}

	public function getGraphDataMem($period) {
		// Grab our memory info...
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$retarr = array(
			"width" => $this->width,
			"toolTip" => array("shared" => true),
			"axisX" => array("valueFormatString" => " ", "tickLength" => 0),
			"axisY" => array("interval" => 10),
			"legend" => array("verticalAlign" => "bottom", "horizontalAlign" => "left"),
			"dataPointMinWidth" => 5,
			"data" => array(
				0 => array(
					"xValueType" => "dateTime",
					"name" => "% In Use",
					"type" => "stackedColumn100",
					"showInLegend" => true,
					"dataPoints" => array(),
				),
				1 => array(
					"xValueType" => "dateTime",
					"name" => "% Buffers",
					"type" => "stackedColumn100",
					"showInLegend" => true,
					"dataPoints" => array(),
				),
				2 => array(
					"xValueType" => "dateTime",
					"name" => "% Cache",
					"type" => "stackedColumn100",
					"showInLegend" => true,
					"dataPoints" => array(),
				),
				3 => array(
					"xValueType" => "dateTime",
					"name" => "% Unused",
					"type" => "stackedColumn100",
					"showInLegend" => true,
					"dataPoints" => array(),
				),
				4 => array(
					"xValueType" => "dateTime",
					"name" => "% Swap Utilized",
					"type" => "line",
					"color" => "red",
					"showInLegend" => true,
					"dataPoints" => array(),
				),
			),
		);
		$count = 0;
		foreach ($si as $utime => $val) {
			$key = $utime * 1000;
			if (!isset($val['psi.Memory.@attributes.Percent'])) {
				$retarr['data'][0]['dataPoints'][$count] = array( "x" => $key, "y" => null);
				$retarr['data'][1]['dataPoints'][$count] = array( "x" => $key, "y" => null);
				$retarr['data'][2]['dataPoints'][$count] = array( "x" => $key, "y" => null);
				$retarr['data'][3]['dataPoints'][$count] = array( "x" => $key, "y" => null);
				$retarr['data'][4]['dataPoints'][$count] = array( "x" => $key, "y" => null);
			} else {
				$retarr['data'][0]['dataPoints'][$count] = array( "x" => $key, "y" => (int) $val['psi.Memory.Details.@attributes.AppPercent']);
				$retarr['data'][1]['dataPoints'][$count] = array( "x" => $key, "y" => (int) $val['psi.Memory.Details.@attributes.BuffersPercent']);
				$retarr['data'][2]['dataPoints'][$count] = array( "x" => $key, "y" => (int) $val['psi.Memory.Details.@attributes.CachedPercent']);
				$retarr['data'][3]['dataPoints'][$count] = array( "x" => $key, "y" => (int) 100 - $val['psi.Memory.@attributes.Percent']);
				$retarr['data'][4]['dataPoints'][$count] = array( "x" => $key, "y" => (int) $val['psi.Memory.Swap.@attributes.Percent']);
			}
			$count++;
		}
		return $retarr;
	}

	public function getGraphDataAst($period) {
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$retarr = array(
			"width" => $this->width,
			"toolTip" => array("shared" => true),
			"axisX" => array("valueFormatString" => " ", "tickLength" => 0),
			"axisY" => array("interval" => 10),
			"legend" => array("verticalAlign" => "bottom", "horizontalAlign" => "left"),
			"data" => array(
				0 => array(
					"xValueType" => "dateTime",
					"name" => "Users Online",
					"type" => "line",
					"showInLegend" => true,
					"dataPoints" => array(),
					"markerSize" => 1,
				),
				1 => array(
					"xValueType" => "dateTime",
					"name" => "Users Offline",
					"type" => "line",
					"showInLegend" => true,
					"dataPoints" => array(),
					"markerSize" => 1,
				),
				2 => array(
					"xValueType" => "dateTime",
					"name" => "Trunks Online",
					"type" => "line",
					"showInLegend" => true,
					"dataPoints" => array(),
					"markerSize" => 1,
				),
				3 => array(
					"xValueType" => "dateTime",
					"name" => "Trunks Offline",
					"type" => "line",
					"showInLegend" => true,
					"dataPoints" => array(),
					"markerSize" => 1,
				),
				4 => array(
					"xValueType" => "dateTime",
					"legendText" => "Channels In Use",
					"name" => "In Use",
					"type" => "column",
					"showInLegend" => true,
					"dataPoints" => array(),
					"markerSize" => 1,
				),
			)
		);
		$count = 0;
		$trunkoffline = false;
		$uonline = array();
		$uoffline = array();
		$tonline = array();
		$toffline = array();
		$channels = array();
		$timestamps = array();
		foreach ($si as $utime => $val) {
			$key = $utime * 1000;
			$timestamps[$count] = $key;
			if (!isset($val['ast.connections.users_online'])) {
				$uonline[$count] = array( "x" => $key, "y" => null);
				$uoffline[$count] = array( "x" => $key, "y" => null);
				$tonline[$count] = array( "x" => $key, "y" => null);
				$toffline[$count] = array( "x" => $key, "y" => null);
				$channels[$count] = array( "x" => $key, "y" => null);
			} else {
				$uonline[$count] = array( "x" => $key, "y" => (int) $val['ast.connections.users_online']);
				$uoffline[$count] = array( "x" => $key, "y" => (int) $val['ast.connections.users_offline']);
				$tonline[$count] = array( "x" => $key, "y" => (int) $val['ast.connections.trunks_online']);
				if ($val['ast.connections.trunks_offline'] != 0) {
					if (!$trunkoffline) {
						$trunkoffline = true;
						if ($count > 1) {
							$toffline[$count-1] = 0;
						}
					}
					$toffline[$count] = array( "x" => $key, "y" => (int) $val['ast.connections.trunks_offline']);
				} else {
					// We only want to render a line to zero immediately after it was not zero, so the line
					// goes back down the bottom of the graph before vanishing.
					if ($trunkoffline) {
						$toffline[$count] = 0;
						$trunkoffline = false;
					} else {
						// $retarr['values']['toffline'][$count] = null;
					}
				}
				$channels[$count] = array( "x" => $key, "y" => (int) $val['ast.chan_totals.total_calls']);
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
}
