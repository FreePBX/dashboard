<div class="fpbx-container m-0 disk__usage__container">
    <div class="row">
        <div class="col-12 usage__details">
            <div>
                <div class="stacked-bar-graph">
                    <div class="logSizeBar"></div>
                    <div class="backupSizeBar"></div>
                    <div class="voicemailSizeBar"></div>
                    <div class="callRecordingSizeBar"></div>
                    <div class="mediaSizeBar"></div>
                    <div class="otherSizeBar"></div>
                    <div class="freeSpaceBar"></div>
                </div>
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
<script>
  
function displayDiskSpaceUsage() {
    let data = {};
    $.post("ajax.php?module=dashboard&command=getDiskSpaceUsage", data, function (res) {
        if (res) {
            $('#totalSpaceDetails').text(`Disk is ${((res.usedDiskSpace / res.totalDiskSpace) * 100).toFixed(2)} % full (${formatBytes(res.usedDiskSpace)} / ${formatBytes(res.totalDiskSpace)})`);

            $('#logSizeData .file__count').text(`${res.log.totalFilesCount} Files`);
            $('#logSizeData .size').text(`${formatBytes(res.log.folderSize)}`);

            if(res.log.folderSize > 0){
                $('.logSizeBar').css('width',`${calculatePercentage(res.usedDiskSpace,res.log.folderSize)}%`).attr('title',`Logs - ${calculatePercentage(res.usedDiskSpace,res.log.folderSize)}%`);
            }

            $('#backupSizeData .file__count').text(`${res.backup.totalFilesCount} Files`);
            $('#backupSizeData .size').text(`${formatBytes(res.backup.folderSize)}`);
           
            if(res.backup.folderSize > 0){
                $('.backupSizeBar').css('width',`${calculatePercentage(res.usedDiskSpace,res.backup.folderSize)}%`).attr('title',`Backup - ${calculatePercentage(res.usedDiskSpace,res.backup.folderSize)}%`);
            }

            $('#voicemailSizeData .file__count').text(`${res.voicemail.totalFilesCount} Files`);
            $('#voicemailSizeData .size').text(`${formatBytes(res.voicemail.folderSize)}`);
            
            if(res.voicemail.folderSize > 0){
                $('.voicemailSizeBar').css('width',`${calculatePercentage(res.usedDiskSpace,res.voicemail.folderSize)}%`).attr('title',`Voicemail - ${calculatePercentage(res.usedDiskSpace,res.voicemail.folderSize)}%`);
            }

            $('#callRecordingSizeData .file__count').text(`${res.callRecording.totalFilesCount} Files`);
            $('#callRecordingSizeData .size').text(`${formatBytes(res.callRecording.folderSize)}`);
            
            if(res.callRecording.folderSize > 0){
                $('.callRecordingSizeBar').css('width',`${calculatePercentage(res.usedDiskSpace,res.callRecording.folderSize)}%`).attr('title',`Call Recording - ${calculatePercentage(res.usedDiskSpace,res.callRecording.folderSize)}%`);
            }

            $('#mediaSizeData .file__count').text(`${res.media.totalFilesCount} Files`);
            $('#mediaSizeData .size').text(`${formatBytes(res.media.folderSize)}`);
            
            if(res.media.folderSize > 0){
                $('.mediaSizeBar').css('width',`${calculatePercentage(res.usedDiskSpace,res.media.folderSize)}%`).attr('title',`Media - ${calculatePercentage(res.usedDiskSpace,res.media.folderSize)}%`);
            }

            $('#otherSizeData .size').text(`${formatBytes(res.otherSpace)}`);
            
            if(res.otherSpace > 0){
                $('.otherSizeBar').css('width',`${calculatePercentage(res.usedDiskSpace,res.otherSpace)}%`).attr('title',`Other - ${calculatePercentage(res.usedDiskSpace,res.otherSpace)}%`);
            }

            $('#freeSpaceData .size').text(`${formatBytes(res.freeDiskSpace)}`);
            
            if(res.freeDiskSpace > 0){
                $('.freeSpaceBar').css('width',`${calculatePercentage(res.totalDiskSpace,res.freeDiskSpace)}%`).attr('title',`Free Space - ${calculatePercentage(res.totalDiskSpace,res.freeDiskSpace)}%`);
            }
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
    
    displayDiskSpaceUsage();

});

function calculatePercentage(totalDiskSpace,folderSize){
    if(folderSize > 0){
        return ((folderSize/totalDiskSpace)*100).toFixed(2);
    }else{
        return 0;
    }
}
</script>