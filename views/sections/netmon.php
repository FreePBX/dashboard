<?php

if (!class_exists('\FreePBX\modules\Dashboard\Netmon')) {
	include __DIR__."/../../classes/Netmon.class.php";
}

$netmon = new \FreePBX\modules\Dashboard\Netmon();
// Wait until we actually have some data back
$count = 5;
$stats = $netmon->getStats();
while ($count-- > 0) {
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
	if (!isset($row['rx']) || $row['rx']['bytes'] == 0) {
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
	window.Netchart.clear_timeout();
} else {
	// New instantiation
	window.NetchartObj = Class.extend({
		refresh: false,
		refreshperiod: 500,
		chartdata: [{
			xValueType: "dateTime",
			xValueFormatString: "h:mm:ss tt",
			type: "splineArea",
			dataPoints: [],
			toolTipContent: "<span style='color: {color};'>RX: <strong>{y}</strong>Kb/sec</span>",
		},
		{	name: "TX Kb/s",
			xValueType: "dateTime",
			xValueFormatString: "h:mm:ss tt",
			type: "splineArea",
			dataPoints: [],
			toolTipContent: "<span style='color: {color};'>TX: <strong>{y}</strong>Kb/sec</span>",
		}],
		init: function(intname) {
			var self = this;
			if (typeof intname == "undefined") {
				intname = "";
			}
			this.chart =  new CanvasJS.Chart('netmonout', {
				title: { text: _("Interface") + " " + intname },
				data: self.chartdata,
				saxisX: { valueFormatString: " ", tickLength: 0 },
				axisY: { valueFormatString: " ", tickLength: 0 },
				toolTip: { shared: true },
			});
			this.set_binds();
			this.load_chart(intname);
		},
		set_binds: function() {
			var self = this;
			// Make sure there are none hanging around
			$("#netmon").off("click", "button");

			$("#netmon").on("click", "button", function(e) {
				var intname = $(e.target).data('intname');
				if (typeof intname !== "undefined") {
					self.clear_timeout();
					self.load_chart(intname);
				} else {
					console.log("Bug. No intname from e!", e);
				}
			});
		},
		clear_timeout: function() {
			if (this.refresh !== false) {
				clearTimeout(this.refresh);
				this.refresh = false;
			}
		},
		load_chart: function(intname) {
			console.log("load chart called with "+intname);
			var self = this;
			this.clear_timeout();
			// Get our data
			$.ajax({
				url: FreePBX.ajaxurl,
				longpoll: true,
				data: { command: "netmon", module:'dashboard'},
				success: function(data) {
					self.render_chart(intname, data);
					self.refresh = setTimeout(function() { self.load_chart(intname); }, self.refreshperiod);
				}
			});
		},
		render_chart: function(intname, data) {
			var self = this;
			var count = 0;
			self.chartdata[0]['dataPoints'] = [];
			self.chartdata[1]['dataPoints'] = [];
			// Loop through all the timestamps
			Object.keys(data).forEach(function(k) {
				var rx, lastrx, rxbytes, tx, lasttx, txbytes;
				var timestamp = k * 1000;
				if (typeof data[k][intname] == "undefined") {
					self.chartdata[0]['dataPoints'][count] = { x: timestamp, y: 0, rawval: 0 };
					self.chartdata[1]['dataPoints'][count] = { x: timestamp, y: 0, rawval: 0 };
				} else {
					rx = data[k][intname]['rx']['bytes'];
					tx = data[k][intname]['tx']['bytes'];
					if (count === 0) {
						lastrx = rx;
						lasttx = tx;
					} else {
						lastrx = self.chartdata[0]['dataPoints'][count-1]['rawval'];
						lasttx = self.chartdata[1]['dataPoints'][count-1]['rawval'];
					}
					rxbytes = rx - lastrx;
					txbytes = tx - lasttx;

					self.chartdata[0]['dataPoints'][count] = { x: timestamp, y: Math.floor(rxbytes/1024), rawval: rx };
					self.chartdata[1]['dataPoints'][count] = { x: timestamp, y: Math.floor(txbytes/1024), rawval: tx };
				}
				count++;
			});
			if (typeof self.chart.options.title !== "undefined") {
				self.chart.options.title.text = _("Interface") + " " + intname;
			}
			self.chart.render();
		},
	});

}

// (Re?)Create the window.Netchart object and start it.
window.Netchart = new window.NetchartObj("<?php echo $first; ?>");

</script>
