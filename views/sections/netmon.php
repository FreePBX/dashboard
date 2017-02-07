<?php

if (!class_exists('\FreePBX\modules\Dashboard\Netmon')) {
	include __DIR__."/../../classes/Netmon.class.php";
}

$netmon = new \FreePBX\modules\Dashboard\Netmon();
// Wait until we actually have some data back
$count = 5;
$stats = $netmon->getStats();
while ($count > 0) {
	if (empty($stats)) {
		usleep(500000); // half a second
	} else {
		break;
	}
	$stats = $netmon->getStats();
}

if (empty($stats)) {
	echo "Error. Unable to get Netmon stats\n";
	return;
}

// Excellent. We have data!
?>
<div class="row" id="netmon">
	<div class="col-sm-2">
		<div class="btn-group-vertical">
<?php
// Grab the first one, to get the interface names
$raw = array_shift($stats);

$first = false;
foreach ($raw as $name => $row) {
	// If this is lo, skip
	if ($name === 'lo') {
		continue;
	}
	// If there has been no traffic received on this interface, skip
	// Why rx? If there's traffic coming in AT ALL, it means it's plugged
	// into something. Even if it's not being used.
	if ($row['rx']['bytes'] == 0) {
		continue;
	}

	if (!$first) {
		// We need to remember the first interface
		$first = $name;
	}
	// This is a valid interface, so give it a button.
	echo "<button type='button' class='btn btn-default' data-intname='$name'>$name</button>\n";
}
?>
		</div>
	</div>
	<div class='col-sm-10' id='netmonout' style='min-height:200px; width: 75%'><p><i><?php echo sprintf(_("Loading Interface %s..."), $first); ?></i></p></div>
</div>

<script>
// Remote anything hanging around if we've been reloaded.
if (typeof window.Netchart !== "undefined") {
	if (typeof window.Netchart.refresh !== "undefined") {
		clearTimeout(window.Netchart.refresh);
	}
	
	// Make sure we don't have any onclick handlers hanging around
	$("#netmon").off("click", "button");
} else {
	// New instantiation
	window.Netchart = {};
}

function load_netmon(intname) {
	if (typeof intname == "undefined") {
		// No interface?
		return;
	}
	// Build our chart data. This is stuck to the window DOM,
	// so it can be updated without needing to recreate the whole
	// object.
	window.Netchart['chartdata'] = [
		{ xValueType: "dateTime", xValueFormatString: "HH:mm:ss", type: "splineArea", dataPoints: [] }, // rxdata
		{ xValueType: "dateTime", xValueFormatString: "HH:mm:ss", type: "splineArea", dataPoints: [] }, // txdata
	];
	window.Netchart['chart'] = new CanvasJS.Chart('netmonout', {
		title:{ text: _("Interface") + " " + intname },
		// animationEnabled: true,
		data: window.Netchart.chartdata,
		axisX: { valueFormatString: " ", tickLength: 0 },
		axisY: { valueFormatString: " ", tickLength: 0 },
	});
	load_chart(intname);
}

function load_chart(intname) {
	// Get our data
	$.ajax({
		url: FreePBX.ajaxurl,
		data: { command: "netmon", module:'dashboard' },
		success: function(data) {
			render_chart(intname, data);
			window.Netchart.refresh = setTimeout(function() { load_chart(intname); }, 500);
		},
	});
}

function render_chart(intname, data) {
	var count = 0;
	Object.keys(data).forEach(function(k) { // Timestamp
		var rx, lastrx, rxbytes, tx, lasttx, txbytes;
		var timestamp = k * 1000;
		if (typeof data[k][intname] == "undefined") {
			window.Netchart.chartdata[0]['dataPoints'][count] = { x: timestamp, y: 0, rawval: 0 };
			window.Netchart.chartdata[1]['dataPoints'][count] = { x: timestamp, y: 0, rawval: 0 };
		} else {
			rx = data[k][intname]['rx']['bytes'];
			tx = data[k][intname]['tx']['bytes'];
			if (count === 0) {
				lastrx = rx;
				lasttx = tx;
			} else {
				lastrx = window.Netchart.chartdata[0]['dataPoints'][count-1]['rawval'];
				lasttx = window.Netchart.chartdata[1]['dataPoints'][count-1]['rawval'];
			}
			rxbytes = rx - lastrx;
			txbytes = tx - lasttx;

			window.Netchart.chartdata[0]['dataPoints'][count] = { x: timestamp, y: Math.floor(rxbytes/1024), rawval: rx };
			window.Netchart.chartdata[1]['dataPoints'][count] = { x: timestamp, y: Math.floor(txbytes/1024), rawval: tx };
		}
		count++;
	});
	window.Netchart.chart.render();
}

<?php
// Now actually trigger a load.
echo "load_netmon('$first');\n";
?>
</script>
