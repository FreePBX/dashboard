<?php
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

class DashboardHooks {

	private static array $pages = [];

	public static function genHooks($order) {
		self::$pages[] = ["pagename" => "Main", "entries" => self::getMainEntries($order)];
		self::addExtraPages();
		return self::$pages;
	}

	private static function getMainEntries($order) {
		// If we have a registered system, change the layout.
		if (!function_exists('sysadmin_get_license')) {
			$dir= FreePBX::create()->Config->get_conf_setting('AMPWEBROOT');
			if (file_exists($dir."/admin/modules/sysadmin/functions.inc.php")) {
				// Woo. Lets load it!
				include $dir."/admin/modules/sysadmin/functions.inc.php";
			}
		}
		$reged = (function_exists('sysadmin_get_license') && sysadmin_get_license());

		$sections = [];
		foreach(glob(dirname(__DIR__).'/sections/*.class.php') as $file) {
			$class = "\\FreePBX\\modules\\Dashboard\\Sections\\".str_replace('.class','',pathinfo((string) $file,PATHINFO_FILENAME));
			if (!class_exists($class)) {
				include $file ;
			}
			$class = new $class();
			foreach($class->getSections($order) as $section) {
				//avoid duplicate orders
				while(isset($sections[$section['order']])) {
					$section['order']++;
				}
				$sections[$section['order']] = ["group" => $section['group'], "title" => $section['title'], "width" => $section['width'], "rawname" => $class->rawname, "section" => $section['section']];
			}
		}
		# Call dashboard disk graph hook
		$freePBX = FreePBX::create();
		if ($freePBX->Modules->checkStatus("sysadmin") && method_exists($freePBX->Sysadmin, 'DashboardGraph')) {
			$sections[] = $freePBX->Sysadmin->DashboardGraph()->getSections();
		}
		ksort($sections);

		return array_values($sections);
	}

	private static function addExtraPages() {
		/*
		// No support for extra pages yet
		$retarr = array("pagename" => "FreePBX HA", "entries" =>
			array(100 => array("group" => "Status", "title" => "High Availability Status", "width" => 12, "func" => "freepbx_ha_status"))
		);
		self::$pages[] = $retarr;
		*/
		return false;
	}

	public static function runHook($hook) {
		if (str_starts_with((string) $hook, "builtin_")) {
			// It's a builtin module.
			return FreePBX::create()->Dashboard->doBuiltInHook($hook);
		}

		if (str_starts_with((string) $hook, "freepbx_ha_")) {
			return "This is not the hook you want";
		}

		throw new Exception("Extra hooks not done yet");
	}
}
