<?php // vim: set ai ts=4 sw=4 ft=phtml:
// New Dashboard
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

if (!class_exists('DashboardHooks')) {
	include 'classes/DashboardHooks.class.php';
}
$dashboard 	= FreePBX::Dashboard();
$notif      = FreePBX::Notifications();
$module 	= \module_functions::create();
$result 	= $module->getinfo('sysadmin', MODULE_STATUS_ENABLED);
if(!empty($result)){
	$sa         = FreePBX::Sysadmin();
    $licenses   = sysadmin_get_license();
	$alerts 	= $dashboard->getLicenseStatus($licenses);
	dbug($alerts);
	$renew 		= $expired = 0;
	foreach($alerts as $type => $msg){
		switch($type){
			case "renew":
				$renew++;
				$notif->add_warning("sysadmin", "licrenew", "Module needs renew.", $msg, false, false, true, 1);
			break;
			case "expired":
				$expired++;
				$notif->add_error("sysadmin", "licexpired", "Update or Support expired.", $msg, false, false, true, 1);
			break;
		}
	}
	if($renew == 0){
		$notif->undo_ignore_forever("sysadmin", "licrenew");
		$notif->delete("sysadmin", "licrenew");
	}

	if($expired == 0){
		$notif->undo_ignore_forever("sysadmin", "licexpired");
		$notif->delete("sysadmin", "licexpired");
	}
}


$allhooks = DashboardHooks::genHooks(FreePBX::Dashboard()->getConfig('visualorder'));
$dashboard->setConfig('allhooks', $allhooks);

show_view(__DIR__.'/views/main.php',array("brand" => FREEPBX::Config()->get('DASHBOARD_FREEPBX_BRAND')));

