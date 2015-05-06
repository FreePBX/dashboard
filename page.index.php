<?php // vim: set ai ts=4 sw=4 ft=phtml:
// New Dashboard
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

show_view(__DIR__.'/views/main.php',array("brand" => FREEPBX::Config()->get('DASHBOARD_FREEPBX_BRAND')));
