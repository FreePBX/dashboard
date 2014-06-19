<?php
$bootstrap_settings['freepbx_error_handler']=false;
include '/etc/freepbx.conf';

$z = FreePBX::Dashboard();

$_REQUEST['command']="gethooks";

$c = $z->ajaxHandler();
print "I have ".count($c)." results\n";


