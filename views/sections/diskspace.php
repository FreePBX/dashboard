<div class="fpbx-container m-0 disk__usage__container">
    <div class="row">
        <div class="col-12 usage__details">
            <div>
                <canvas id="diskUsageChart" width="200" height="200"></canvas>
            </div>
            <h4 id="totalSpaceDetails"></h4>
        </div>
        <div class="col-12 usage__details">
            <div class="row">
                <div class="col-xs-6" id="logSizeData">
                    <div class="d-flex flex-row align-items-center">
                        <div class="d-flex flex-column">
                            <div class="file__name">
                                <div class="color" style="background-color:#d63b37;"></div>
                                <div>Logs</div>
                            </div>
                            <div class="file__count"></div>
                        </div>
                        <div class="ml-auto size">
                            
                        </div>
                    </div>
                </div>
                <div class="col-xs-6" id="callRecordingSizeData">
                    <div class="d-flex flex-row align-items-center">
                        <div class="d-flex flex-column">
                            <div class="file__name">
                                <div class="color" style="background-color:#D1BE00;"></div>
                                <div>Call Recordings</div>
                            </div>
                            <div class="file__count"></div>
                        </div>
                        <div class="ml-auto size">
                            
                        </div>
                    </div>
                </div>
                <div class="col-xs-6"  id="voicemailSizeData">
                    <div class="d-flex flex-row align-items-center">
                        <div class="d-flex flex-column">
                            <div class="file__name">
                                <div class="color" style="background-color:#855f83;"></div>
                                <div>Voicemail</div>
                            </div>
                            <div class="file__count"></div>
                        </div>
                        <div class="ml-auto size">
                            
                        </div>
                    </div>
                </div>
                <div class="col-xs-6" id="mediaSizeData">
                    <div class="d-flex flex-row align-items-center">
                        <div class="d-flex flex-column">
                            <div class="file__name">
                                <div class="color" style="background-color:#00a0af;"></div>
                                <div>Media/Sounds</div>
                            </div>
                            <div class="file__count"></div>
                        </div>
                        <div class="ml-auto size">
                            
                        </div>
                    </div>
                </div>
                <div class="col-xs-6" id="backupSizeData">
                    <div class="d-flex flex-row align-items-center">
                        <div class="d-flex flex-column">
                            <div class="file__name">
                                <div class="color" style="background-color:#FF7800;"></div>
                                <div>Backups</div>
                            </div>
                            <div class="file__count"></div>
                        </div>
                        <div class="ml-auto size">
                            
                        </div>
                    </div>
                </div>
                <div class="col-xs-6" id="otherSizeData">
                    <div class="d-flex flex-row align-items-center">
                        <div class="d-flex flex-column">
                            <div class="file__name">
                                <div class="color" style="background-color:#55B17F;"></div>
                                <div>Other</div>
                            </div>
                            <div class="file__count">---</div>
                        </div>
                        <div class="ml-auto size">
                            
                        </div>
                    </div>
                </div>
                <div class="col-xs-6" id="freeSpaceData">
                    <div class="d-flex flex-row align-items-center">
                        <div class="d-flex flex-column">
                            <div class="file__name">
                                <div class="color" style="background-color:#DFDFDF;"></div>
                                <div>Free Space</div>
                            </div>
                            <div class="file__count">---</div>
                        </div>
                        <div class="ml-auto size">
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="fpbx-container disk__noti__wrapper" style="display:none">
	<div class='row'>
		<div class='col-sm-12'>
			<div class="alert alert-info disk__noti">
                <div><?= _('All the storage related settings can be managed from'); ?> <a href="/admin/config.php?display=sysadmin&view=storage"><b>Sysadmin</b></a> <?= _('page'); ?></div>
                <i class="fa fa-times-circle"  title="<?php echo _('Delete This')?>" onClick="removeDiskNoti()"></i>
            </div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.min.js"></script>
<script>

function removeDiskNoti(){
    let data = {};
    $.post("ajax.php?module=dashboard&command=removeDiskNoti", data, function (res) {
        displayDiskSpaceUsage();
    });
}

function displayDiskSpaceUsage() {
    let data = {};
    $.post("ajax.php?module=dashboard&command=getDiskSpaceUsage", data, function (res) {
        if (res) {
            console.log(res.showDiskNoti);
            if(res.showDiskNoti == "0"){
                $('.disk__noti__wrapper').hide();
            }else{
                $('.disk__noti__wrapper').show();
            }
            $('#totalSpaceDetails').text(`Disk is ${((res.usedDiskSpace / res.totalDiskSpace) * 100).toFixed(2)} % full (${formatBytes(res.usedDiskSpace)} / ${formatBytes(res.totalDiskSpace)})`);

            $('#logSizeData .file__count').text(`${res.log.totalFilesCount} Files`);
            $('#logSizeData .size').text(`${formatBytes(res.log.folderSize)}`);

            $('#backupSizeData .file__count').text(`${res.backup.totalFilesCount} Files`);
            $('#backupSizeData .size').text(`${formatBytes(res.backup.folderSize)}`);

            $('#voicemailSizeData .file__count').text(`${res.voicemail.totalFilesCount} Files`);
            $('#voicemailSizeData .size').text(`${formatBytes(res.voicemail.folderSize)}`);

            $('#callRecordingSizeData .file__count').text(`${res.callRecording.totalFilesCount} Files`);
            $('#callRecordingSizeData .size').text(`${formatBytes(res.callRecording.folderSize)}`);

            $('#mediaSizeData .file__count').text(`${res.media.totalFilesCount} Files`);
            $('#mediaSizeData .size').text(`${formatBytes(res.media.folderSize)}`);

            $('#otherSizeData .size').text(`${formatBytes(res.otherSpace)}`);
            $('#freeSpaceData .size').text(`${formatBytes(res.freeDiskSpace)}`);

            Chart.defaults.doughnutLabels = Chart.helpers.clone(Chart.defaults.doughnut);
            Chart.defaults.defaultFontColor = "#000000";

            var helpers = Chart.helpers;

            Chart.controllers.doughnutLabels = Chart.controllers.doughnut.extend({
                updateElement: function (arc, index, reset) {
                    var _this = this;
                    var chart = _this.chart,
                        chartArea = chart.chartArea,
                        opts = chart.options,
                        animationOpts = opts.animation,
                        arcOpts = opts.elements.arc,
                        centerX = (chartArea.left + chartArea.right) / 2,
                        centerY = (chartArea.top + chartArea.bottom) / 2,
                        startAngle = opts.rotation, // non reset case handled later
                        endAngle = opts.rotation, // non reset case handled later
                        dataset = _this.getDataset(),
                        circumference = reset && animationOpts.animateRotate ? 0 : arc.hidden ? 0 : _this.calculateCircumference(dataset.data[index]) * (opts.circumference / (2.0 * Math.PI)),
                        innerRadius = reset && animationOpts.animateScale ? 0 : 170,
                        outerRadius = reset && animationOpts.animateScale ? 0 : _this.outerRadius,
                        custom = arc.custom || {},
                        valueAtIndexOrDefault = helpers.getValueAtIndexOrDefault;

                    helpers.extend(arc, {
                        // Utility
                        _datasetIndex: _this.index,
                        _index: index,

                        // Desired view properties
                        _model: {
                            x: centerX + chart.offsetX,
                            y: centerY + chart.offsetY,
                            startAngle: startAngle,
                            endAngle: endAngle,
                            circumference: circumference,
                            outerRadius: outerRadius,
                            innerRadius: innerRadius,
                            label: valueAtIndexOrDefault(dataset.label, index, chart.data.labels[index])
                        },

                        draw: function () {
                            var ctx = this._chart.ctx,
                                vm = this._view,
                                sA = vm.startAngle,
                                eA = vm.endAngle,
                                opts = this._chart.config.options;

                            var labelPos = this.tooltipPosition();
                            var segmentLabel = vm.circumference / opts.circumference * 100;

                            ctx.beginPath();

                            ctx.arc(vm.x, vm.y, vm.outerRadius, sA, eA);
                            ctx.arc(vm.x, vm.y, vm.innerRadius, eA, sA, true);

                            ctx.closePath();
                            ctx.strokeStyle = vm.borderColor;
                            ctx.lineWidth = vm.borderWidth;

                            ctx.fillStyle = vm.backgroundColor;

                            ctx.fill();
                            ctx.lineJoin = 'bevel';

                            if (vm.borderWidth) {
                                ctx.stroke();
                            }

                            if (vm.circumference > 0.0015) { // Trying to hide label when it doesn't fit in segment
                                ctx.beginPath();
                                let fontSize = "15";
                                ctx.font = helpers.fontString(fontSize, opts.defaultFontStyle, opts.defaultFontFamily);
                                ctx.fillStyle = "#000000";
                                ctx.textBaseline = "top";
                                ctx.textAlign = "center";
                                // Round percentage in a way that it always adds up to 100%
                                ctx.fillText(segmentLabel.toFixed(2) + "%", labelPos.x, labelPos.y);
                            }
                            //display in the center the total sum of all segments
                            var total = dataset.data.reduce((sum, val) => sum + val, 0);
                            //ctx.fillText('Total = ' + total, vm.x, vm.y - 20, 200);
                            ctx.fillStyle = "#808080";
                            ctx.fillText(`${formatBytes(res.freeDiskSpace)} Free Space`, vm.x, vm.y - 70, 200);
                            ctx.fillText('0%', vm.x - 150, vm.y);
                            ctx.fillText('100%', vm.x + 150, vm.y);
                        }
                    });

                    var model = arc._model;
                    model.backgroundColor = custom.backgroundColor ? custom.backgroundColor : valueAtIndexOrDefault(dataset.backgroundColor, index, arcOpts.backgroundColor);
                    model.hoverBackgroundColor = custom.hoverBackgroundColor ? custom.hoverBackgroundColor : valueAtIndexOrDefault(dataset.hoverBackgroundColor, index, arcOpts.hoverBackgroundColor);
                    model.borderWidth = custom.borderWidth ? custom.borderWidth : valueAtIndexOrDefault(dataset.borderWidth, index, arcOpts.borderWidth);
                    model.borderColor = custom.borderColor ? custom.borderColor : valueAtIndexOrDefault(dataset.borderColor, index, arcOpts.borderColor);

                    // Set correct angles if not resetting
                    if (!reset || !animationOpts.animateRotate) {
                        if (index === 0) {
                            model.startAngle = opts.rotation;
                        } else {
                            model.startAngle = _this.getMeta().data[index - 1]._model.endAngle;
                        }

                        model.endAngle = model.startAngle + model.circumference;
                    }

                    arc.pivot();
                }
            });

            var config = {
                type: 'doughnutLabels',
                data: {
                    datasets: [{
                        data: [
                            res.log.folderSize,
                            res.callRecording.folderSize,
                            res.voicemail.folderSize,
                            res.media.folderSize,
                            res.backup.folderSize,
                            res.otherSpace,
                            res.freeDiskSpace,
                        ],
                        backgroundColor: [
                            "#d63b37",
                            "#D1BE00",
                            "#855f83",
                            "#00a0af",
                            "#FF7800",
                            "#55B17F",
                            "#DFDFDF"
                        ]
                    }],
                    labels: [
                        "Logs",
                        "Call Recordings",
                        "Voicemail",
                        "Sounds",
                        "Backups",
                        "Other",
                        "Free Space",
                    ]
                },
                options: {
                    circumference: Math.PI,
                    rotation: 1.0 * Math.PI,
                    legend: {
                        display: false,
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    },
                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem, data) {

                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var total = dataset.data.reduce(function (previousValue, currentValue, currentIndex, array) {
                                    return previousValue + currentValue;
                                });
                                var currentValue = dataset.data[tooltipItem.index];
                                //var precentage = Math.floor(((currentValue / total) * 100) + 0.5);
                                return window.upDownChart.data.labels[tooltipItem.index] + " " + formatBytes(currentValue);
                            }
                        }
                    }
                }
            };

            var ctx = document.getElementById("diskUsageChart").getContext("2d");
            window.upDownChart = new Chart(ctx, config);
        }
    });

}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

$(document).ready(function () {

    $(".size").empty();
    $(".size").html(`<i class="fa fa-spinner fa-spin"></i>`);
    
    waitUntillChartJsIsLoaded();

});

function waitUntillChartJsIsLoaded() {
    if (typeof Chart != "undefined") {
        displayDiskSpaceUsage();
    } else {
        setTimeout(function() { waitUntillChartJsIsLoaded() }, 50);
    }
}
</script>