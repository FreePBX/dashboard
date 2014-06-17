<?php

/** Ajax Handler
 *
 * Note to remember - you're inside the BMO Dashboard object, and have
 * full access to all BMO functions.
 */

if (!class_exists('DashboardHooks')) {
	include 'classes/DashboardHooks.class.php';
}

print "I have ".$_REQUEST['command'];


