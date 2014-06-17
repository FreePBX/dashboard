<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

namespace FreePBX\modules\Dashboard\Sections;

class Statistics {
	public $rawname = 'Statistics';

	public function getSections() {
		if (!defined('DASHBOARD_FREEPBX_BRAND')) {
			if (!empty($_SESSION['DASHBOARD_FREEPBX_BRAND'])) {
				define('DASHBOARD_FREEPBX_BRAND', $_SESSION['DASHBOARD_FREEPBX_BRAND']);
			} else {
				define('DASHBOARD_FREEPBX_BRAND', 'FreePBX');
			}
		} else {
			$_SESSION['DASHBOARD_FREEPBX_BRAND'] = DASHBOARD_FREEPBX_BRAND;
		}

		$brand = DASHBOARD_FREEPBX_BRAND;

		return array(
			array(
				"title" => "$brand ". _("Statistics"),
				"group" => _("Statistics"),
				"width" => "700px",
				"order" => '400',
				"section" => "uptime"
			)
		);
	}

	public function getContent($section) {
		return load_view(dirname(__DIR__).'/views/sections/statistics.php');
	}
}
