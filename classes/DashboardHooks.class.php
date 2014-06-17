<?php
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

class DashboardHooks {

	private static $pages = array();

	public static function genHooks() {
		self::$pages[] = array("pagename" => "Main", "entries" => self::getMainEntries());
		self::addExtraPages();

		// Add things to make the Javascript easier.
		// Add a 'groups' to each page.
		foreach (self::$pages as $id => $page) {
			$groups = array();
			foreach ($page['entries'] as $entry) {
				self::$pages[$id]['groups'][$entry['group']][] = $entry;
			}
		}
		return self::$pages;
	}

	private static function getMainEntries() {
		// If we have a registered system, change the layout.
		if (!function_exists('sysadmin_get_license')) {
			$dir= FreePBX::create()->Config->get_conf_setting('AMPWEBROOT');
			if (file_exists($dir."/admin/modules/sysadmin/functions.inc.php")) {
				// Woo. Lets load it!
				include $dir."/admin/modules/sysadmin/functions.inc.php";
			}
		}
		$reged = (function_exists('sysadmin_get_license') && sysadmin_get_license());

		/*
		$ov = _("Overview");
		$sysov = _("System Overview");
		$blogposts = _("Blog Posts");
		$sysstat = _("System Statistics");
		$stats = _("Statistics");
		$name = _("FreePBX");
		$uptime = _("Uptime");
		$srvstat = _("Service Status");
		$reginfo = _("Registration Info");
		*/

		//$retarr = array();
		$sections = array();
		foreach(glob(dirname(__DIR__).'/sections/*.class.php') as $file) {
			$class = "\\FreePBX\\modules\\Dashboard\\Sections\\".str_replace('.class','',pathinfo($file,PATHINFO_FILENAME));
			if (!class_exists($class)) {
				include $file ;
			}
			$class = new $class();
			foreach($class->getSections() as $section) {
				//avoid duplicate orders
				while(isset($sections[$section['order']])) {
					$section['order']++;
				}
				$sections[$section['order']] = array("group" => $section['group'], "title" => $section['title'], "width" => $section['width'], "rawname" => $class->rawname, "section" => $section['section']);
			}
			//$sections[$class->rawname] =
		}
		ksort($sections);

		return array_values($sections);
		//dbug($sections)
		//die();
		/*
		// Built in Hooks here.
		if (!$reged) {
			$retarr[100] = array("group" => $ov, "title" => $sysov, "width" => 7, "func" => "builtin_overview");
			$retarr[110] = array("group" => $ov, "title" => $blogposts, "width" => 5, "func" => "builtin_blog");
		} else {
			$retarr[100] = array("group" => $ov, "title" => $sysov, "width" => 8, "func" => "builtin_overview");
			$retarr[110] = array("group" => $ov, "title" => $reginfo, "width" => 4, "func" => "dashboard", "module" => "sysadmin");
			$retarr[200] = array("group" => $ov, "title" => $blogposts, "width" => 12, "func" => "builtin_blog");
		}
		$retarr[300] = array("group" => $stats, "title" => $sysstat, "width" => 4, "func" => "builtin_sysstat");
		$retarr[310] = array("group" => $stats, "title" => "$name $stats", "width" => 8, "func" => "builtin_aststat");
		$retarr[320] = array("group" => $stats, "title" => $uptime, "width" => 12, "func" => "builtin_uptime");
		$retarr[400] = array("group" => $stats, "title" => $srvstat, "width" => 12, "func" => "builtin_srvstat");
		*/
		//return $retarr;
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
		if (strpos($hook, "builtin_") === 0) {
			// It's a builtin module.
			return FreePBX::create()->Dashboard->doBuiltInHook($hook);
		}

		if (strpos($hook, "freepbx_ha_") === 0) {
			return "This is not the hook you want";
		}

		throw new Exception("Extra hooks not done yet");
	}
}
