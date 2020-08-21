<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

namespace FreePBX\modules\Dashboard\Sections;

class Statistics {
	public $rawname = 'Statistics';

	public function getSections($order) {
		$brand = \FreePBX::Config()->get("DASHBOARD_FREEPBX_BRAND");

		return array(
			array(
				"title" => sprintf(_("%s Statistics"),$brand),
				"group" => _("Statistics"),
				"width" => "550px",
				"order" => isset($order['statistics']) ? $order['statistics'] : '300',
				"section" => "statistics"
			),
			array(
				"title" => _("Live Network Usage"),
				"group" => _("Statistics"),
				"width" => "550px",
				"order" => isset($order['statistics']) ? $order['statistics'] : '300',
				"section" => "netmon"
			)
		);
	}

	public function getContent($section) {
		$date = date("d/m/Y H:i:s",strtotime("now"));
error_log('Statistics class getContent start '.$date." \n", 3, "/var/log/asterisk/dashboardload.log");

		if ($section === "statistics") {
			if (class_exists('DOMDocument') && extension_loaded('mbstring')) {
				$date = date("d/m/Y H:i:s",strtotime("now"));
error_log('Statistics class  loading /views/sections/statistics.php'.$date." \n", 3, "/var/log/asterisk/dashboardload.log");
				return load_view(dirname(__DIR__).'/views/sections/statistics.php');
			} elseif(!class_exists('DOMDocument')) {
				$date = date("d/m/Y H:i:s",strtotime("now"));
error_log('Statistics class  loading /views/sections/stats-no-phpxml.php'.$date." \n", 3, "/var/log/asterisk/dashboardload.log");
		
				return load_view(dirname(__DIR__).'/views/sections/stats-no-phpxml.php');
			} elseif(!extension_loaded('mbstring')) {
				$date = date("d/m/Y H:i:s",strtotime("now"));
error_log('Statistics class  loading /views/sections/stats-no-mbstring.php'.$date." \n", 3, "/var/log/asterisk/dashboardload.log");
		
				return load_view(dirname(__DIR__).'/views/sections/stats-no-mbstring.php');
			}
		} elseif ($section === "netmon") {
			$date = date("d/m/Y H:i:s",strtotime("now"));
error_log('Statistics class  loading /views/sections/netmon.php'.$date." \n", 3, "/var/log/asterisk/dashboardload.log");
		
			return load_view(dirname(__DIR__).'/views/sections/netmon.php');
		}
	}
}
