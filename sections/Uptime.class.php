<?php

namespace FreePBX\modules\Dashboard\Sections;

class Uptime {
	public $rawname = 'Uptime';

	public function getSections() {
		return array(
			array(
				"title" => _("Uptime"),
				"group" => _("Statistics"),
				"width" => "300px",
				"order" => '300',
				"section" => "uptime"
			)
		);
	}

	public function getContent($section) {
		if (!class_exists('\CPUInfo')) {
			include dirname(__DIR__).'/classes/CPUInfo.class.php';
		}
		if (!class_exists('\TimeUtils')) {
			include dirname(__DIR__).'/classes/TimeUtils.class.php';
		}

		$cpu = new \CPUInfo();
		$time = \TimeUtils::getReadable($this->getUptimeSecs());

		return load_view(dirname(__DIR__).'/views/sections/uptime.php',array("cpu" => $cpu->getAll(), "time" => $time));
	}

	public function getUptimeSecs() {
		$uptime = file_get_contents("/proc/uptime");
		list($secs, $null) = explode(" ", $uptime);
		return round($secs);
	}
}
