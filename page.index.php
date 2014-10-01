<?php // vim: set ai ts=4 sw=4 ft=phtml:
// New Dashboard

if (!defined('DASHBOARD_FREEPBX_BRAND')) {
	if (!empty($_SESSION['DASHBOARD_FREEPBX_BRAND'])) {
		define('DASHBOARD_FREEPBX_BRAND', $_SESSION['DASHBOARD_FREEPBX_BRAND']);
	} else {
		define('DASHBOARD_FREEPBX_BRAND', \FreePBX::Config()->get("DASHBOARD_FREEPBX_BRAND"));
	}
} else {
	$_SESSION['DASHBOARD_FREEPBX_BRAND'] = DASHBOARD_FREEPBX_BRAND;
}

$brand = DASHBOARD_FREEPBX_BRAND;

show_view(__DIR__.'/views/main.php',array("brand" => $brand));
