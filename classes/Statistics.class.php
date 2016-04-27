<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

class Statistics {
	public function getHTML() {
		return $this->genGraph();
	}

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

	private function genGraph() {
		$html = "<div class='row'><div class='col-sm-2'>".$this->getSidebar()."</div><div id='builtin_aststat' class='col-sm-10'style='height: 250px'></div></div>";
		$html .= "<script type='text/javascript'>".$this->getTemplates()."
Dashboard.sysstatAjax = { command: 'sysstat', target: 'uptime', period: 'hour', module: window.modulename };
window.observers['builtin_aststat'] = function() {
  $.ajax({
    url: window.ajaxurl,
    data: Dashboard.sysstatAjax,
    success: function(data) { $('#builtin_aststat').chart('clear'); $('#builtin_aststat').chart(data); window.ajaxdata = data; },
   });
};
</script>";
		$html .= "</div>";
		return $html;
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

	public function getSidebar() {
		$uptime = _("Uptime");
		$asterisk = _("Asterisk");
		$cpu = _("CPU");
		$mem = _("Memory");
		$disk = _("Disk");
		$network = _("Network");

		$html = '<div class="btn-group-vertical">
  <div class="btn-group btn-group-lg" data-type="uptime">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
      '.$uptime.' <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
      <li><a href="#" onClick="sbClick()">'._("Hour").'</a></li>
      <li><a href="#" onClick="sbClick()">'._("Day").'</a></li>
      <li><a href="#" onClick="sbClick()">'._("Week").'</a></li>
	  <li><a href="#" onClick="sbClick()">'._("Month").'</a></li>
	</ul>
  </div>
  <div class="btn-group btn-group-lg" data-type="asterisk">
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
	  '.$asterisk.' <span class="caret"></span>
	</button>
	<ul class="dropdown-menu">
	<li><a href="#" onClick="sbClick()">'._("Hour").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Day").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Week").'</a></li>
<li><a href="#" onClick="sbClick()">'._("Month").'</a></li>
	</ul>
  </div>
  <div class="btn-group btn-group-lg" data-type="cpuusage">
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
	  '.$cpu.' <span class="caret"></span>
	</button>
	<ul class="dropdown-menu">
	<li><a href="#" onClick="sbClick()">'._("Hour").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Day").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Week").'</a></li>
<li><a href="#" onClick="sbClick()">'._("Month").'</a></li>
	</ul>
  </div>
  <div class="btn-group btn-group-lg" data-type="memusage">
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
	  '.$mem.' <span class="caret"></span>
	</button>
	<ul class="dropdown-menu">
	<li><a href="#" onClick="sbClick()">'._("Hour").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Day").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Week").'</a></li>
<li><a href="#" onClick="sbClick()">'._("Month").'</a></li>
	</ul>
  </div>
  <div class="btn-group btn-group-lg" data-type="diskusage">
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
	  '.$disk.' <span class="caret"></span>
	</button>
	<ul class="dropdown-menu">
	<li><a href="#" onClick="sbClick()">'._("Hour").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Day").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Week").'</a></li>
<li><a href="#" onClick="sbClick()">'._("Month").'</a></li>
	</ul>
  </div>
  <div class="btn-group btn-group-lg" data-type="networking">
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
	  '.$network.' <span class="caret"></span>
	</button>
	<ul class="dropdown-menu">
	<li><a href="#" onClick="sbClick()">'._("Hour").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Day").'</a></li>
	<li><a href="#" onClick="sbClick()">'._("Week").'</a></li>
<li><a href="#" onClick="sbClick()">'._("Month").'</a></li>
	</ul>
  </div>
</div>
<script type="text/javascript">
function sbClick() {
	event.preventDefault();
	var target = $(event.target);
	Dashboard.sysstatAjax.period = target.text();
	Dashboard.sysstatAjax.target = target.parents(".btn-group").data("type")
	window.observers["builtin_aststat"]();
}
</script>
';
		return $html;
	}

	public function getTemplates() {
		return "$.elycharts.templates['aststat'] = {
 type : 'line',
 margins : [20, 15, 10, 15],
 autoresize : true,
 defaultSeries : {
  plotProps : { 'stroke-width' : 4 },
  dotProps : { stroke : 'white', 'stroke-width' : 2 },
  tooltip : { height: 130, width: 200, offset: [20, 120], frameProps : { stroke : 'yellow', opacity : 0.75 } },
  highlight : { newProps : { fill : 'white', stroke : 'yellow', r : 8 } },
  startAnimation : { active : true, type : 'avg', speed : 1000 },
 },
 features : {
  mousearea : { type : 'index' },
  highlight : {
   indexHighlight : 'auto',
   indexHighlightProps : { 'stroke-dasharray' : '-', 'stroke-width' : 2, opacity : 0.5 }
  },
  grid : {
   forceBorder: true,
   draw : [true, false],
   props : { 'stroke-dasharray' : '-' },
  },
  legend: {
   horizontal : true,
   width : 'auto',
   x : 10,
   y : 0,
   borderProps : { 'fill-opacity' : 0.3, 'stroke-width' : 0 },
  },
 },
};

$.elycharts.templates['memchart'] = {
 type : 'line',
 autoresize : true,
 margins : [20, 40, 40, 30],
 defaultSeries : {
  type : 'bar',
  stacked : true,
  highlight : {
   newProps : { r : 8, opacity : 1 },
   overlayProps : { fill : 'white', opacity : 0.2 }
  },
 },
 series : {
  memfree : { color : '90-#008000-#005000', tooltip : { frameProps : { stroke : 'green' } } },
  cached : { color : '90-#90EE90-#40AA40', tooltip : { frameProps : { stroke : 'lightgreen' } } },
  buffers : { color : '90-#FFA500-#CC6000', tooltip : { frameProps : { stroke : 'orange' } } },
  memused : { color : '90-#FF4500-#CC2200', tooltip : { frameProps : { stroke : 'orangered' } } },
  swappct : { color : 'midnightblue', rounded : false, dot : true, type: 'line', stacked: false,
   dotProps : { r : 0, stroke : 'white', 'stroke-width' : 0, opacity : 0 },
   plotProps : { 'stroke-width' : 3, 'stroke-linecap' : 'round', 'stroke-linejoin' : 'round' },
  },
 },
 legend: {
  memfree: '"._('Free Mem')."',
  cached: '"._('Cached')."',
  buffers: '"._('Buffers')."',
  memused: '"._('Used')."',
  swappct: '"._('Swap Used')."',
 },
 defaultAxis : { labels : true },
 axis : { r : { max: 100, suffix: '%', }, l : { max: 100, suffix: '%', normalize: false } },
 features : { grid : { draw : true, forceBorder : true, ny : 5 },
  legend: { horizontal : true, width : 'auto', x : 10, y : 0, borderProps : { 'fill-opacity' : 0.3, 'stroke-width' : 0 }, },
 },
 barMargins : 1,
};

$.elycharts.templates['astchart'] = {
 type : 'line',
 autoresize : true,
 margins : [20, 30, 40, 0],
 defaultSeries : {
  type: 'line',
  axis: 'r',
  dot: true,
  startAnimation : { active : true, type : 'avg', speed : 1000 },
  dotProps: { r: 0, opacity: 0,  'stroke-width' : 0, },
  plotProps: { 'stroke-width': 2 },
  highlight : {
   newProps : { r : 8, opacity : 1 },
   overlayProps : { fill : 'white', opacity : 0.2 }
  },
 },
 series : {
  uonline : { color : 'green', tooltip : { frameProps : { stroke : 'green' } } },
  uoffline: { color : 'lightgreen', tooltip : { frameProps : { stroke : 'lightgreen' } } },
  tonline : { color : 'orange', tooltip : { frameProps : { stroke : 'orange' } } },
  toffline : { color : 'red', tooltip : { frameProps : { stroke : 'red' } }, fill: true },
  channels : { color : 'blue', tooltip : { frameProps : { stroke : 'blue' } } },
 },
 legend: {
  uonline: '"._('Users Online')."Users Online',
  uoffline: '"._('Users Offline')."Users Offline',
  tonline: '"._('Trunks Reged')."',
  toffline: '"._('Trunks Offline')."Trunks Offline',
  channels: '"._('Active Chans')."Active Chans',
 },
 defaultAxis : { labels : true },
 features : { grid : { draw : true, forceBorder : true, ny : 5 },
  legend: { horizontal : true, width : 'auto', x : 0, y : 0, borderProps : { 'fill-opacity' : 0.3, 'stroke-width' : 0 }, },
 },
 barMargins : 1,
};";
}

	public function getGraphDataCPU($period) {
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);

		$tooltips = array();
		$loadfive = array();
		$loadten = array();
		$loadfifteen = array();

		$foundtemps = false;
		$cputemp = array();

		foreach ($si as $key => $row) {
			if (!isset($row['psi.Vitals.@attributes.LoadAvg.five'])) {
				$lfive = 0;
				$lten = 0;
				$lfifteen = 0;
			} else {
				$lfive = $row['psi.Vitals.@attributes.LoadAvg.five'];
				$lten = $row['psi.Vitals.@attributes.LoadAvg.ten'];
				$lfifteen = $row['psi.Vitals.@attributes.LoadAvg.fifteen'];
			}

			if (isset($row['psi.Hardware.CPU.CpuCore.0.@attributes.CpuTemp'])) {
				$temp = $row['psi.Hardware.CPU.CpuCore.0.@attributes.CpuTemp'];
				$foundtemps = true;
			} else {
				$temp = false;
			}

			$ttip = date('c', $key)."<br>";
			$ttip .= "$lfive<br>$lten<br>$lfifteen";
			if ($temp) {
				$cputemp[] = $temp;
				$ttip .= "<br>"._('Temp').": $temp";
			} else {
				$cputemp[] = 0;
			}

			$tooltips[] = $ttip;

			$loadfive[] = $lfive;
			$loadten[] = $lten;
			$loadfifteen[] = $lfifteen;
		}

		$retarr['template'] = 'aststat';
		$retarr['tooltips'] = $tooltips;
		if (!$foundtemps) {
			$retarr['values'] = array( $loadfive, $loadten, $loadfifteen );
			$retarr['legend'] = array( sprintf(_('%s Min Avg'),1), sprintf(_('%s Min Avg'),5), sprintf(_('%s Min Avg'),15));
		} else {
			$retarr['values'] = array( $loadfive, $loadten, $loadfifteen, $cputemp );
			$retarr['legend'] = array( sprintf(_('%s Min Avg'),1), sprintf(_('%s Min Avg'),5), sprintf(_('%s Min Avg'),15), _('CPU Temp'));
		}
		$retarr['series'] = array(
			array( "color" => "blue", "axis" => "l" ),
			array( "color" => "green", "axis" => "l" ),
			array( "color" => "red", "axis" => "l" ),
			array( "color" => "orange", "axis" => "r" ),
		);
		$retarr['axis'] = array(
			"l" => array("title" => _("Load"), "titleDistance" => 8, "labels" => true ),
			"r" => array("title" => _("Temp"), "titleDistance" => 8, "labels" => true ),
		);

 		$retarr['margins'] = array (25, 35, 10, 35);

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
		$retarr['template'] = 'memchart';
		$count = 0;
		foreach ($si as $key => $val) {
			if (!isset($val['psi.Memory.@attributes.Percent'])) {
				$retarr['values']['memused'][$count] = null;
				$retarr['values']['buffers'][$count] = null;
				$retarr['values']['cached'][$count]  = null;
				$retarr['values']['memfree'][$count] = null;
				$retarr['values']['swappct'][$count] = null;
				$retarr['tooltips']['memused'][$count] = null;
				$retarr['tooltips']['buffers'][$count] = null;
				$retarr['tooltips']['cached'][$count]  = null;
				$retarr['tooltips']['memfree'][$count] = null;
				$retarr['tooltips']['swappct'][$count] = null;
			} else {
				$retarr['values']['memused'][$count] = (int) $val['psi.Memory.Details.@attributes.AppPercent'];
				$retarr['values']['buffers'][$count] = (int) $val['psi.Memory.Details.@attributes.BuffersPercent'];
				$retarr['values']['cached'][$count]  = (int) $val['psi.Memory.Details.@attributes.CachedPercent'];
				$retarr['values']['memfree'][$count] = 100 - $val['psi.Memory.@attributes.Percent'];
				$retarr['values']['swappct'][$count] = (int) $val['psi.Memory.Swap.@attributes.Percent'];
				$retarr['tooltips']['memused'][$count] = "Used: ".$val['psi.Memory.Details.@attributes.AppPercent']."%";
				$retarr['tooltips']['buffers'][$count] = "Buffers: ".$val['psi.Memory.Details.@attributes.BuffersPercent']."%";
				$retarr['tooltips']['cached'][$count]  = "Cached: ".$val['psi.Memory.Details.@attributes.CachedPercent']."%";
				$retarr['tooltips']['memfree'][$count] = "Free: ".(100 - $val['psi.Memory.@attributes.Percent'])."%";
				$retarr['tooltips']['swappct'][$count] = "Swap: ".$val['psi.Memory.Swap.@attributes.Percent']."%";
			}
			$count++;
		}
		return $retarr;
	}

	public function getGraphDataAst($period) {
		// Grab our memory info...
		$si = FreePBX::create()->Dashboard->getSysInfoPeriod($period);
		$retarr['template'] = 'astchart';
		$count = 0;
		$trunkoffline = false;
		foreach ($si as $key => $val) {
			if (!isset($val['ast.connections.users_online'])) {
				$retarr['values']['uonline'][$count] = null;
				$retarr['values']['uoffline'][$count] = null;
				$retarr['values']['tonline'][$count] = null;
				$retarr['values']['toffline'][$count] = null;
				$retarr['values']['channels'][$count] = null;
			} else {
				$retarr['values']['uonline'][$count] = (int) $val['ast.connections.users_online'];
				$retarr['tooltips']['uonline'][$count] = $val['ast.connections.users_online']." users online";
				$retarr['values']['uoffline'][$count] = (int) $val['ast.connections.users_offline'];
				$retarr['tooltips']['uoffline'][$count] = $val['ast.connections.users_offline']." users offline";
				$retarr['values']['tonline'][$count] = (int) $val['ast.connections.trunks_online'];
				$retarr['tooltips']['tonline'][$count] = $val['ast.connections.trunks_online']." trunks online";;
				if ($val['ast.connections.trunks_offline'] != 0) {
					if (!$trunkoffline) {
						$trunkoffline = true;
						if ($count > 1) {
							$retarr['values']['toffline'][$count-1] = 0;
						}
					}
					$retarr['values']['toffline'][$count] = (int) $val['ast.connections.trunks_offline'];
					$retarr['tooltips']['toffline'][$count] = $val['ast.connections.trunks_offline']." trunks offline";
				} else {
					// We only want to render a line to zero immediately after it was not zero, so the line
					// goes back down the bottom of the graph before vanishing.
					if ($trunkoffline) {
						$retarr['values']['toffline'][$count] = 0;
						$trunkoffline = false;
					} else {
						// $retarr['values']['toffline'][$count] = null;
					}
				}
				$retarr['values']['channels'][$count] = (int) $val['ast.chan_totals.total_calls'];
				$retarr['tooltips']['channels'][$count] = $val['ast.chan_totals.total_calls']." active calls";
			}
			$count++;
	   	}
		return $retarr;
	}
}
