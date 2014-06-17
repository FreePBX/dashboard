<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

namespace FreePBX\modules\Dashboard\Sections;

class Overview {
	public $rawname = 'Overview';

	public function getSections() {
		return array(
			array(
				"title" => _("System Overview"),
				"group" => _("Overview"),
				"width" => "500px",
				"order" => '1',
				"section" => "overview"
			)
		);
	}

	public function getContent($section) {
		if (!class_exists('TimeUtils')) {
			include dirname(__DIR__).'/classes/TimeUtils.class.php';
		}
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

		try {
			$getsi = \FreePBX::create()->Dashboard->getSysInfo();
		} catch (\Exception $e) {

		}

		$since = time() - $getsi['timestamp'];
		$nots = $this->getNotifications();
		$alerts = $this->getAlerts($nots);

		return load_view(dirname(__DIR__).'/views/sections/overview.php',array("nots" => $nots, "alerts" => $alerts, "brand" => $brand, "version" => get_framework_version(), "since" => $since, "services" => $this->getSummary()));
	}

	private function getNotifications() {
		if (!class_exists('TimeUtils')) {
			include dirname(__DIR__).'/classes/TimeUtils.class.php';
		}
		$showall = true;
		$items = \FreePBX::create()->Notifications->list_all($showall);
		// This is where we map the Notifications priorities to Bootstrap priorities.
		// define("NOTIFICATION_TYPE_CRITICAL", 100) -> 'danger' (orange)
		// define("NOTIFICATION_TYPE_SECURITY", 200) -> 'danger' (red)
		// define("NOTIFICATION_TYPE_UPDATE",   300) -> 'warning' (orange)
		// define("NOTIFICATION_TYPE_ERROR",    400) -> 'info' (blue)
		// define("NOTIFICATION_TYPE_WARNING" , 500) -> 'info' -> (blue)
		// define("NOTIFICATION_TYPE_NOTICE",   600) -> 'success' -> (green)

		$alerts = array(100 => "danger", 200 => "danger", 300 => "warning", 400 => "info", 500 => "info", 600 => "success");
		$final = array();
		foreach ($items as $notification) {
			$final[] = array(
				"id" => $notification['id'],
				"rawlevel" => $notification['level'],
				"level" => !isset($alerts[$notification['level']]) ? 'danger' : $alerts[$notification['level']],
				"candelete" => $notification['candelete'],
				"title" => $notification['display_text'],
				"time" => \TimeUtils::getReadable(time() - $notification['timestamp']),
				"text" => nl2br($notification['extended_text']),
				"module" => $notification['module']
			);
		}
		return $final;
	}

	public function getAlerts($nots = false) {
		// Check notifications and decide what we want to do with them.
		// Start with everything happy
		$state = "success";
		$text = "No critical issues found";
		$foundalerts = array();
		// Go through our notifications now..
		foreach ($nots as $n) {
			// Firstly, check for a security issue. If that happens, we don't care about
			// anything else.
			if ($n['rawlevel'] == 200) {
				// Security vulnerability. This is bad.
				$state = "danger";
				$text = "<center><h4>Security Issue</h4></center><br /><p>".$n['title']."</p><p>This is a critical issue and should be resolved urgently</p>";
				return array("state" => $state, "text" => $text);
			}

			// Now lets find some alerts!
			if (!isset($foundalerts[$n['level']])) {
				$foundalerts[$n['level']] = 1;
			} else {
				$foundalerts[$n['level']]++;
			}
		}

		// Here is where we decide what the 10-word-box shall say.
		// If there's a Critical Issue, report that and a summary.
		if (isset($foundalerts['danger'])) {
			// There's a critical issue. That's what we're doing.
			$state = "danger";
			$text = "Critical Errors found. Please check notifications";
		} elseif (isset($foundalerts['warning'])) {
			$state = "warning";
			$text = "Warning: Please check for errors in the notification section";
		}
		return array("state" => $state, "text" => $text);
	}

	public function getSummary() {
		$svcs = array(
			"asterisk" => _("Asterisk"),
			"mysql" => _("MySQL"),
			"apache" => _("Web Server"),
			"fail2ban" => _("Fail2Ban Service"),
			"isreged" => _("System Registration"),
			"xmpp" => _("XMPP Server"),
			"openvpn" => _("Open VPN Server"),
			"hiav" => _("High Availability"),
		);

		$sysinfo = \FreePBX::create()->Dashboard->getSysInfo();

		$final = array();
		$i = 0;
		foreach (array_keys($svcs) as $svc) {
			if (!method_exists($this, "check$svc")) {
				$final[$i]['type'] = 'unknown';
				$final[$i]['tooltip'] = "Function check$svc doesn't exist!";
			} else {
				$func = "check$svc";
				$final[$i] = $this->$func($sysinfo);
			}
			$final[$i]['title'] = $svcs[$svc];
			$i++;
		}

		return $final;
	}

	private function genAlertGlyphicon($res, $tt = null) {
		$glyphs = array(
			"ok" => "glyphicon-ok text-success",
			"warning" => "glyphicon-warning-sign text-warning",
			"error" => "glyphicon-remove text-danger",
			"unknown" => "glyphicon-question-sign text-info",
			"info" => "glyphicon-info-sign text-info",
			"critical" => "glyphicon-fire text-danger"
		);
		// Are we being asked for an alert we actually know about?
		if (!isset($glyphs[$res])) {
			return array('type' => 'unknown', "tooltip" => "Don't know what $res is", "glyph-class" => $glyphs['unknown']);
		}

		if ($tt === null) {
			// No Tooltip
			return array('type' => $res, "tooltip" => null, "glyph-class" => $glyphs[$res]);
		} else {
			// Generate a tooltip
			$html = '';
			if (is_array($tt)) {
				foreach ($tt as $line) {
					$html .= htmlentities($line, ENT_QUOTES)."\n";
				}
			} else {
				$html .= htmlentities($tt, ENT_QUOTES);
			}

			return array('type' => $res, "tooltip" => $html, "glyph-class" => $glyphs[$res]);
		}
		return '';
	}

	private function checkasterisk($sysinfo) {
		if (!isset($sysinfo['ast.uptime.system-seconds'])) {
			return $this->genAlertGlyphicon('critical', 'Unable to find Asterisk results');
		}
		$ast = $sysinfo['ast.uptime.system-seconds'];

		// Check to see if Asterisk is up and running.
		if ($ast == -1) {
			return $this->genAlertGlyphicon('error', 'Asterisk not running');
		}

		// Can we connect to asterisk?
		if ($ast == -2) {
			return $this->genAlertGlyphicon('critical', 'Asterisk Manager Interface (astman) failure');
		}

		$uptime = $sysinfo['ast.uptime.system'];
		// Up for less than 10 minutes? Is it crashing?
		if ($ast < 600) {
			return $this->genAlertGlyphicon('warning', "Asterisk running for less than 10 minutes ($uptime)");
		}

		return $this->genAlertGlyphicon('ok', "Asterisk uptime $uptime");
	}

	private function checkmysql() {
		return $this->genAlertGlyphicon('ok', "No Database checks written yet.");
	}

	private function checkapache() {
		// This is here to allow us to fire up a small replacement httpd server if
		// something traumatic happens to apache. For the moment, however, we just
		// say yes.
		return $this->genAlertGlyphicon('ok', "Apache running");
	}

	private function checkhiav() {
		return $this->genAlertGlyphicon('info', "High Av isn't installed");
	}

	private function checkxmpp() {
		// This is a handy feature of exec - it'll APPEND to an array.
		// So we preload the error message, if we need it.
		$output = array("Prosody status check failed. Return text is:");

		exec("service prosody status 2>&1", $output, $ret);
		if ($ret === 0) {
			return $this->genAlertGlyphicon('ok', "Prosody running");
		}

		return $this->genAlertGlyphicon('warning', $output);
	}

	private function checkopenvpn($si) {
		exec("service openvpn status 2>&1", $output, $ret);
		if ($ret === 1) {
			return $this->genAlertGlyphicon('info', "OpenVPN Service not running");
		}
		if ($ret === 0) {
			// Open VPN is running! Let's see if we can figure out what our IP
			// address is.
			$tuns = array();
			foreach ($si as $k => $v) {
				if (strpos($v, "tun") === 0) {
					// Woo, this could be a tunnel address...
					if (preg_match('/psi.Network.NetDevice.(\d+).@attributes.Name/', $k, $match)) {
						// It is! Now, what's its interface number?
						$int = $match[1];
						if (isset($si["psi.Network.NetDevice.$int.@attributes.Info"])) {
							$info = $si["psi.Network.NetDevice.$int.@attributes.Info"];
							list($ip, $null) = split(';', $info);
							$tuns[] = $ip;
						}
					}
				}
			}
			// Note that this is exactly the same as if (count($tuns) === 0)
			if (!$tuns) {
				return $this->genAlertGlyphicon('warning', "OpenVPN Service running, but not connected.");
			}

			if (count($tuns) > 1) {
				$rettext = "OpenVPN Service running. Detected IP Addresses are: ";
			} else {
				$rettext = "OpenVPN Service running. Detected IP Address is ";
			}

			foreach ($tuns as $tun) {
				$rettext .= "$tun, ";
			}

			// Remove the trailing ", "
			return $this->genAlertGlyphicon('ok', substr($rettext, 0, -2));
		}
	}

	private function checkfail2ban() {
		exec("service fail2ban status 2>&1", $output, $ret);
		if ($ret === 0) {
			return $this->genAlertGlyphicon('ok', "Fail2ban running");
		}
		return $this->genAlertGlyphicon('critical', "Fail2Ban should always be running");
	}

	private function checkisreged() {
		// It may or may not exist. Lets be cautious.
		if (!function_exists('sysadmin_get_license')) {
			$dir= \FreePBX::create()->Config->get_conf_setting('AMPWEBROOT');
			if (file_exists($dir."/admin/modules/sysadmin/functions.inc.php")) {
				// Woo. Lets load it!
				include $dir."/admin/modules/sysadmin/functions.inc.php";
			}
		}
		// NOW. Does it exist, and, if it does, is it true?
		if (function_exists('sysadmin_get_license') && sysadmin_get_license()) {
			return $this->genAlertGlyphicon('ok', 'System registered');
		} else {
			return $this->genAlertGlyphicon('info', 'System not registered');
		}
	}

	private function delNotification() {
		// Triggered from above.
		$id = $_REQUEST['id'];
		$mod = $_REQUEST['mod'];
		return FreePBX::create()->Notifications->safe_delete($mod, $id);

	}
}
