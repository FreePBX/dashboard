<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2022 Schmooze Com Inc.

namespace FreePBX\modules\Dashboard\Sections;

class Diskspace {
	public $rawname = 'Diskspace';

	public function __construct() {
		$this->FreePBX = \FreePBX::create();
	}

	public function getSections($order) {
		return array(
			array(
				"title" => _("System Disk Usage"),
				"group" => _("Disk Usage"),
				"width" => "550px",
				"order" => isset($order['diskspace']) ? $order['diskspace'] : '1',
				"section" => "diskspace"
			)
		);
	}

	public function getContent($section) {
		return load_view(dirname(__DIR__).'/views/sections/diskspace.php',array());
	}

	public function getDiskSpaceUsage(){

		$logFolderPath = '/var/log';
		$log['folderSize'] = $this->getDirectorySize($logFolderPath);
		$log['totalFilesCount'] = $this->getTotalFilesInDirectory($logFolderPath);
	
		$callRecordingFolderPath = $this->FreePBX->Config->get("ASTSPOOLDIR") . '/monitor';
		$callRecording['folderSize'] = $this->getDirectorySize($callRecordingFolderPath);
		$callRecording['totalFilesCount'] = $this->getTotalFilesInDirectory($callRecordingFolderPath);
	
		$backupFolderPath = $this->FreePBX->Config->get("ASTSPOOLDIR") . '/backup';
		$backup['folderSize'] = $this->getDirectorySize($backupFolderPath);
		$backup['totalFilesCount'] = $this->getTotalFilesInDirectory($backupFolderPath);

		$voicemailFolderPath = $this->FreePBX->Config->get("ASTSPOOLDIR") . '/voicemail';
		$voicemail['folderSize'] = $this->getDirectorySize($voicemailFolderPath);
		$voicemail['totalFilesCount'] = $this->getTotalFilesInDirectory($voicemailFolderPath);

		$mediaFolder = $this->FreePBX->Config->get("ASTVARLIBDIR")."/sounds";
		$media['folderSize'] = $this->getDirectorySize($mediaFolder);
		$media['totalFilesCount'] = $this->getTotalFilesInDirectory($mediaFolder);

        $totalDiskSpace =  disk_total_space(".");
        $freeDiskSpace =  disk_free_space(".");
        $usedDiskSpace =  $totalDiskSpace - $freeDiskSpace;
        
        $otherSpace =	$usedDiskSpace - ($log['folderSize']+$callRecording['folderSize']+$backup['folderSize']+$voicemail['folderSize']+$media['folderSize'] );
		$showDiskNoti = $this->FreePBX->Dashboard->getConfig('disk-noti');
		$showDiskNoti = $showDiskNoti === false ? 1 : $showDiskNoti;

		return array(
					'log' => $log,
					'callRecording' => $callRecording,
					'backup' => $backup,
					'voicemail' => $voicemail,
					'media' => $media,
					'freeDiskSpace' => $freeDiskSpace,
					'totalDiskSpace' => $totalDiskSpace,
					'otherSpace' => $otherSpace,
					'usedDiskSpace' => $usedDiskSpace,
					'showDiskNoti' => $showDiskNoti
			);
    }


	/**
	 * function to get directory size 
	 * @method getDirectorySize
	 * @param  string $directory - path of the directory
	 */
	private function getDirectorySize($directory)
	{
		if(is_dir($directory)){
			$cmd = "du -s -B1 $directory 2> /dev/null | cut -f1";
			exec($cmd, $output, $return);
			
			if ($return == 1) {
				return _('Not able to find the size of the directory');
			} else {
				$size = preg_replace('/\s+/', ' ', $output[0]);
				$size = explode(' ',$size);
				$size = join(" ",$size);
				return (int)$size;
			}
		}else{
			return 0;
		}
	}

    private function getTotalFilesInDirectory($directory){
        
        if(is_dir($directory)){
			$cmd = "find $directory -type f | wc -l 2> /dev/null | cut -f1";
			exec($cmd, $output, $return);
			
			if ($return == 1) {
				return _('Not able to find the files counts of the directory');
			} else {
				return count($output) > 0 ? $output[0] : 0;
			}
		}else{
			return 0;
		}
    }
}
