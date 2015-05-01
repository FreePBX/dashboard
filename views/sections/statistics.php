<div class="row">
	<div class="col-sm-2">
		<div class="btn-group-vertical">
			<div class="btn-group btn-group-lg" data-type="asterisk">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					Asterisk <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="#" class="graph-button" data-period="hour"><?php echo _("Hour")?></a></li>
					<li><a href="#" class="graph-button" data-period="day"><?php echo _("Day")?></a></li>
					<li><a href="#" class="graph-button" data-period="week"><?php echo _("Week")?></a></li>
					<li><a href="#" class="graph-button" data-period="month"><?php echo _("Month")?></a></li>
				</ul>
			</div>
			<div class="btn-group btn-group-lg" data-type="uptime">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<?php echo _("Uptime")?> <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="#" class="graph-button" data-period="hour"><?php echo _("Hour")?></a></li>
					<li><a href="#" class="graph-button" data-period="day"><?php echo _("Day")?></a></li>
					<li><a href="#" class="graph-button" data-period="week"><?php echo _("Week")?></a></li>
					<li><a href="#" class="graph-button" data-period="month"><?php echo _("Month")?></a></li>
				</ul>
			</div>
			<div class="btn-group btn-group-lg" data-type="cpuusage">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					CPU <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="#" class="graph-button" data-period="hour"><?php echo _("Hour")?></a></li>
					<li><a href="#" class="graph-button" data-period="day"><?php echo _("Day")?></a></li>
					<li><a href="#" class="graph-button" data-period="week"><?php echo _("Week")?></a></li>
					<li><a href="#" class="graph-button" data-period="month"><?php echo _("Month")?></a></li>
				</ul>
			</div>
			<div class="btn-group btn-group-lg" data-type="memusage">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<?php echo _("Memory")?> <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="#" class="graph-button" data-period="hour"><?php echo _("Hour")?></a></li>
					<li><a href="#" class="graph-button" data-period="day"><?php echo _("Day")?></a></li>
					<li><a href="#" class="graph-button" data-period="week"><?php echo _("Week")?></a></li>
					<li><a href="#" class="graph-button" data-period="month"><?php echo _("Month")?></a></li>
				</ul>
			</div>
			<div class="btn-group btn-group-lg" data-type="diskusage">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<?php echo _("Disk")?> <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="#" class="graph-button" data-period="hour"><?php echo _("Hour")?></a></li>
					<li><a href="#" class="graph-button" data-period="day"><?php echo _("Day")?></a></li>
					<li><a href="#" class="graph-button" data-period="week"><?php echo _("Week")?></a></li>
					<li><a href="#" class="graph-button" data-period="month"><?php echo _("Month")?></a></li>
				</ul>
			</div>
			<div class="btn-group btn-group-lg" data-type="networking">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<?php echo _("Network")?> <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="#" class="graph-button" data-period="hour"><?php echo _("Hour")?></a></li>
					<li><a href="#" class="graph-button" data-period="day"><?php echo _("Day")?></a></li>
					<li><a href="#" class="graph-button" data-period="week"><?php echo _("Week")?></a></li>
					<li><a href="#" class="graph-button" data-period="month"><?php echo _("Month")?></a></li>
				</ul>
			</div>
		</div>
		<script type="text/javascript">
		$(".graph-button").click(function(event) {
			event.preventDefault();
			var target = $(this);
			Dashboard.sysstatAjax.period = target.data("period");
			Dashboard.sysstatAjax.target = target.parents(".btn-group").data("type")
			window.observers["builtin_aststat"]();
		})
		</script>
		<style>
		#page_Main_Statistics_statistics .btn-group {
			width: 90px;
		}
		</style>
	</div>
	<div id="builtin_aststat" class="col-sm-10" style="height: 200px">
		Loading Graph....
	</div>
</div>
<script type="text/javascript">
	$.elycharts.templates['aststat'] = {
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
				newProps : {
					r : 8,
					opacity : 1
				},
				overlayProps : {
					fill : 'white',
					opacity : 0.2
				}
			},
		},
		series : {
			memfree : {
				color : '90-#008000-#005000',
				tooltip : {
					frameProps : {
						stroke : 'green'
					}
				}
			},
			cached : {
				color : '90-#90EE90-#40AA40',
				tooltip : {
					frameProps : {
						stroke : 'lightgreen'
					}
				}
			},
			buffers : {
				color : '90-#FFA500-#CC6000',
				tooltip : {
					frameProps : {
						stroke : 'orange'
					}
				}
			},
			memused : {
				color : '90-#FF4500-#CC2200',
				tooltip : {
					frameProps : {
						stroke : 'orangered'
					}
				}
			},
			swappct : {
				color : 'midnightblue',
				rounded : false,
				dot : true,
				type: 'line',
				stacked: false,
			},
			dotProps : {
				r : 0,
				stroke :
				'white',
				'stroke-width' : 0,
				opacity : 0
			},
			plotProps : {
				'stroke-width' : 3,
				'stroke-linecap' :
				'round',
				'stroke-linejoin' :
				'round'
			},
		},
		legend: {
			memfree: '<?php echo _("Free Mem")?>',
			cached: '<?php echo _("Cached")?>',
			buffers: '<?php echo _("Buffers")?>',
			memused: '<?php echo _("Used")?>',
			swappct: '<?php echo _("Swap Used")?>',
		},
		defaultAxis : { labels : true },
		axis : { r : { max: 100, suffix: '%', }, l : { max: 100, suffix: '%', normalize: false } },
		features : { grid : { draw : true, forceBorder : true, ny : 5 },
		legend: {
			horizontal : true,
			width : 'auto',
			x : 10,
			y : 0,
			borderProps : {
				'fill-opacity' : 0.3,
				'stroke-width' : 0 },
			},
		},
		barMargins : 1,
	};

	$.elycharts.templates['astchart'] = {
		type : 'line',
		autoresize : true,
		margins : [25, 30, 5, 15],
		defaultSeries : {
			type: 'line',
			axis: 'r',
			dot: true,
			startAnimation : {
				active : true,
				type : 'avg',
				speed : 1000
			},
			dotProps: {
				r: 0,
				opacity: 0,
				'stroke-width' : 0,
			},
			plotProps: {
				'stroke-width': 2
			},
			highlight : {
				newProps : {
					r : 8,
					opacity : 1
				},
				overlayProps : {
					fill : 'white',
					opacity : 0.2
				}
			},
		},
		series : {
			uonline : {
				color : 'green',
				tooltip : {
					frameProps : {
						stroke : 'green'
					}
				}
			},
			uoffline: {
				color : 'lightgreen',
				tooltip : {
					frameProps : {
						stroke : 'lightgreen'
					}
				}
			},
			tonline : {
				color : 'orange',
				tooltip : {
					frameProps : {
						stroke : 'orange'
					}
				}
			},
			toffline : {
				color : 'red',
				tooltip : {
					frameProps : {
						stroke : 'red'
					}
				},
				fill: true
			},
			channels : {
				color : 'blue',
				tooltip : {
					frameProps : {
						stroke : 'blue'
					}
				}
			},
		},
		legend: {
			uonline: '<?php echo _("Users Online")?>',
			uoffline: '<?php echo _("Users Offline")?>',
			tonline: '<?php echo _("Trunks Reged")?>',
			toffline: '<?php echo _("Trunks Offline")?>',
			channels: '<?php echo _("Active Calls")?>',
		},
		defaultAxis : {
			labels : true
		},
		features : {
			grid : {
				draw : true,
				forceBorder : true,
				ny : 5
			},
			legend: {
				horizontal : true,
				width : 'auto',
				x : 0,
				y : 0,
				borderProps : {
					'fill-opacity' : 0.3,
					'stroke-width' : 0
				},
			},
		},
		barMargins : 1,
	};

	Dashboard.sysstatAjax = {
		command: 'sysstat',
		target: 'uptime',
		period: 'hour',
		module: window.modulename
	};
	window.observers['builtin_aststat'] = function() {
		$('#page_Main_Statistics_uptime .shadow').fadeIn('fast');
		//console.log('Running'); console.log(Dashboard.sysstatAjax);
		$.ajax({
			url: window.ajaxurl,
			data: Dashboard.sysstatAjax,
			success: function(data) {
				$('#page_Main_Statistics_uptime .shadow').fadeOut('fast');
				$('#builtin_aststat').html('');
				$('#builtin_aststat').chart('clear'); $('#builtin_aststat').chart(data); window.ajaxdata = data;
			},
		});
	};
	Dashboard.sysstatAjax = {command: "sysstat", target: "asterisk", period: "Hour", module: "dashboard"};
	window.observers["builtin_aststat"]();
</script>
