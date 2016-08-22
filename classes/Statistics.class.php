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
			"axisY" => array("interval" => 10),
			"legend" => array("verticalAlign" => "top"),
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
				$retarr['data'][0]['dataPoints'][$count] = array( "x" => $key, "y" => $row['psi.Vitals.@attributes.LoadAvg.five']);
				$retarr['data'][1]['dataPoints'][$count] = array( "x" => $key, "y" => $row['psi.Vitals.@attributes.LoadAvg.ten']);
				$retarr['data'][2]['dataPoints'][$count] = array( "x" => $key, "y" => $row['psi.Vitals.@attributes.LoadAvg.fifteen']);
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

		// Discover my disk names and mountpoints.
		$lastsi = FreePBX::create()->Dashboard->getSysInfo();
		$disks = array();
		foreach ($lastsi as $key => $val) {
			if (strpos($key, "psi.FileSystem.Mount.") === 0) {
				$tmparr = explode('.', $key);
				$disks[$tmparr[3]] = array();
			}
		}

		// These are the vars we want to keep.
		$vars = array('Name', 'Free', 'Used', 'Total', 'Percent');
		foreach (array_keys($disks) as $d) {
			foreach ($vars as $v) {
				$disks[$d][$v] = $lastsi['psi.FileSystem.Mount.'.$d.'.@attributes.'.$v];
			}
			// We don't care about tmpfs's
			if ($disks[$d]['Name'] == "tmpfs") {
				unset($disks[$d]);
				continue;
			}
			$retarr['legend'][$d] =  $lastsi['psi.FileSystem.Mount.'.$d.'.@attributes.MountPoint'];
		}

		// Now, generate the graph!
		$tooltips = array();
		foreach ($si as $key => $row) {
			$ttip = "";
			foreach (array_keys($disks) as $d) {
				$var = "psi.FileSystem.Mount.$d.@attributes.Percent";
				if (isset($row[$var])) {
					$retarr['values'][$d][] = $row[$var];
					$ttip .=  $disks[$d]['Name']."<br>&nbsp;&nbsp;".$row[$var]."% used<br>";
				} else {
					$retarr['values'][$d][] = 0;
					$ttip .=  $disks[$d]['Name']."<br>&nbsp;&nbsp;No Information<br>";
				}
			}
			$tooltips[] = $ttip;
		}

		$retarr['template'] = 'aststat';
		$retarr['tooltips'] = $tooltips;
		// 12 colours. That should be enough for almost everyone.
		// Also, 640k is plenty, and there are going to be no more
		// than 7 computers world wide, ever.
		$retarr['series'] = array(
			array( "color" => "blue", "axis" => "r" ),
			array( "color" => "green", "axis" => "r" ), // Note - 1 is often tmpfs, which is stripped out above.
			array( "color" => "cyan", "axis" => "r" ),
			array( "color" => "orange", "axis" => "r" ),
			array( "color" => "fuchsia", "axis" => "r" ),
			array( "color" => "lime", "axis" => "r" ),
			array( "color" => "purple", "axis" => "r" ),
			array( "color" => "royalblue", "axis" => "r" ),
			array( "color" => "teal", "axis" => "r" ),
			array( "color" => "violet", "axis" => "r" ),
			array( "color" => "yellow", "axis" => "r" ),
			array( "color" => "seagreen", "axis" => "r" ),
		);
		$retarr['axis'] = array(
			"r" => array("labels" => true, 'max' => 99, 'suffix' => '%' ),
		);
 		$retarr['margins'] = array (25, 35, 10, 5);
		return $retarr;
	}

	public function getGraphDataNet($period) {

		// Colours for the network lines.
		$colours = array (
			// tx (top), rx (bottom).
			array("90-#00AA00-#99FF99", "90-#006600-#00AA00", "#00AA00"),
			array("90-#00AA00-#99FF99", "90-#006600-#00AA00", "#00AA00"),
			array("90-#0000AA-#9999FF", "90-#000066-#0000AA", "#0000AA"),
			array("90-#669900-#CCDD99", "90-#336600-#339900", "#669900"),
			array("90-#663333-#FFCCCC", "90-#FFCCCC-#663333", "#00AA00"),
			array("90-#339933-#CCDD99", "90-#CCCD99-#339933", "#669900"),
			array("90-#66C000-#66FF00", "90-#66C000-#66C000", "#0000AA"),
			array("90-#00AA00-#99FF99", "90-#006600-#00AA00", "#00AA00"),
			array("90-#0000AA-#9999FF", "90-#000066-#0000AA", "#0000AA"),
			array("90-#669900-#CCDD99", "90-#336600-#339900", "#669900"),
			array("90-#663333-#FFCCCC", "90-#FFCCCC-#663333", "#00AA00"),
			array("90-#339933-#CCDD99", "90-#CCCD99-#339933", "#669900"),
			array("90-#66C000-#66FF00", "90-#66C000-#66C000", "#0000AA"),
			array("90-#00AA00-#99FF99", "90-#006600-#00AA00", "#00AA00"),
			array("90-#0000AA-#9999FF", "90-#000066-#0000AA", "#0000AA"),
			array("90-#669900-#CCDD99", "90-#336600-#339900", "#669900"),
			array("90-#663333-#FFCCCC", "90-#FFCCCC-#663333", "#00AA00"),
			array("90-#339933-#CCDD99", "90-#CCCD99-#339933", "#669900"),
			array("90-#66C000-#66FF00", "90-#66C000-#66C000", "#0000AA"),
		);

		// We want one extra to act as a starting point.
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);

		// Network interfaces!
		$firstsi = isset($si[0])?$si[0]:array();
		$lastsi = FreePBX::create()->Dashboard->getSysInfo();

		$interfaces = array();
		foreach ($lastsi as $key => $val) {
			if (strpos($key, "psi.Network.NetDevice.") === 0) {
				$tmparr = explode('.', $key);
				$interfaces[$tmparr[3]] = array();
			}
		}

		foreach (array_keys($interfaces) as $key) {
			$interfaces[$key]['name'] = $lastsi["psi.Network.NetDevice.$key.@attributes.Name"];
			if ($interfaces[$key]['name'] == "lo") {
				unset($interfaces[$key]);
				continue;
			}
			// Do we know about this interface now? It may be historical.
			if (!isset($lastsi["psi.Network.NetDevice.$key.@attributes.Info"])) {
				// No details about this interface, so we have to guess.
				$interfaces[$key]['ipaddr'] = "Unknown-$key";
			} else {
				// Figure out the address of the interface.  This can be either
				// ipv4;ipv6 or mac;ipv4.
				$tmparr = explode(';', $lastsi["psi.Network.NetDevice.$key.@attributes.Info"]);
				//If no IP address this only returns a mac, no second field. We should not assume anything will be here
				$tmparr[0] = isset($tmparr[0])?$tmparr[0]:'';
				$tmparr[1] = isset($tmparr[1])?$tmparr[1]:'';
				if (filter_var($tmparr[0], FILTER_VALIDATE_IP)) {
					$interfaces[$key]['ipaddr'] = $tmparr[0];
				} elseif (filter_var($tmparr[1], FILTER_VALIDATE_IP)) {
					$interfaces[$key]['ipaddr'] = $tmparr[1];
				} else {
					$interfaces[$key]['ipaddr'] = "0.0.0.0";
				}
			}
			$txb = isset($firstsi["psi.Network.NetDevice.$key.@attributes.TxBytes"])?(int)$firstsi["psi.Network.NetDevice.$key.@attributes.TxBytes"]:0;
			$rxb = isset($firstsi["psi.Network.NetDevice.$key.@attributes.RxBytes"])?(int)$firstsi["psi.Network.NetDevice.$key.@attributes.RxBytes"]:0;
			$interfaces[$key]['previous'] = array(
				"tx" => $txb,
				"rx" => $rxb,
			);
			$retarr['series']["tx$key"] = array( "type" => "bar", "axis" => "r", "stacked" => "rx$key", "color" => $colours[$key][0],
			   	"tooltip" => array( "frameProps" => array("stroke" => $colours[$key][2])));
			$retarr['series']["rx$key"] = array( "type" => "bar", "axis" => "r", "stacked" => "tx$key", "color" => $colours[$key][1],
				"tooltip" => array( "frameProps" => array("stroke" => $colours[$key][2])));

			// Legends.
			$retarr['legend']["tx$key"] = $interfaces[$key]['ipaddr'];
		}

		$allints = array_keys($interfaces);

		// Graph.
		$tooltips = array();

		// Remove interfaces that have no traffic.
		$notnull = array();

		foreach ($si as $key => $row) {
			$ttip = "";
			foreach ($allints as $i) {
				// Difference from the previous view..
				// If we don't HAVE a previous, then just return null, because we can't
				// figure it out.
				if (!$interfaces[$i]['previous']['tx'] || !isset($row["psi.Network.NetDevice.$i.@attributes.TxBytes"])) {
					$tx = null;
				} else {
					$tx = (int) $row["psi.Network.NetDevice.$i.@attributes.TxBytes"] - $interfaces[$i]['previous']['tx'] ;
					// Is it a negative? The counter has wrapped. On a 32 bit machine, this is at 2gb, so.. not much.
					if ($tx < 0) {
						$tx += PHP_INT_MAX;
					}
				}
				if (!$interfaces[$i]['previous']['rx'] || !isset( $row["psi.Network.NetDevice.$i.@attributes.RxBytes"])) {
					$rx = null;
				} else {
					$rx = (int) $row["psi.Network.NetDevice.$i.@attributes.RxBytes"] - $interfaces[$i]['previous']['rx'] ;
					if ($tx < 0) {
						$tx += PHP_INT_MAX;
					}
				}
				$interfaces[$i]['previous']['tx'] = (int) $row["psi.Network.NetDevice.$i.@attributes.TxBytes"];
				$interfaces[$i]['previous']['rx'] = (int) $row["psi.Network.NetDevice.$i.@attributes.RxBytes"];
				if ($tx || $rx) {
					$notnull[$i] = true;
				}
				$tx = $tx / 1024 / 1024;
				$rx = $rx / 1024 / 1024;
				$retarr['values']["rx$i"][] = $rx;
				$retarr['values']["tx$i"][] = $tx;
				$retarr['tooltips']["rx$i"][] = $interfaces[$i]['name'].":\n<br>".round($rx, 2)." MB Rx";
				$retarr['tooltips']["tx$i"][] = $interfaces[$i]['name'].":\n<br>".round($tx, 2)." MB Tx";
			}
		}
 		$retarr['margins'] = array (25, 45, 10, 0);
		$retarr['barmargins'] = 10;
		$retarr['features'] = array(
			"grid" => array( "draw" => true, "forceBorder" => true, "ny" =>  5 ),
			"legend" => array( "horizontal" => true, "width" => "auto", "x" => 10, "y" => 0,
			"borderProps" => array('fill-opacity' => 0.3, 'stroke-width' => 0 )
			),
		);
		$retarr['type'] = "line";
 		$retarr['autoresize'] = true;
		$retarr['axis']['r'] = array("labels" => true, "suffix" => "MB");

		// Now, remove any interfaces that have had no traffic.
		foreach ($allints as $i) {
			if (!isset($notnull[$i])) {
				unset($retarr["values"]["rx$i"]);
				unset($retarr["values"]["tx$i"]);
				unset($retarr["legend"]["tx$i"]);
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
			"legend" => array("verticalAlign" => "top"),
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
