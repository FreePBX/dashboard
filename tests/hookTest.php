<?php

class HookTest extends PHPUnit_Framework_TestCase {

	protected static $f;

	public static function setUpBeforeClass() {
		global $amp_conf, $db;
		include '/etc/freepbx.conf';
		restore_error_handler();
		error_reporting(-1);
		include 'classes/DashboardHooks.class.php';
	}

	public function testDefaultHooks() {
		$hooks = DashboardHooks::genHooks();
		$this->assertTrue(is_array($hooks), "genHooks didn't return an array");
		$this->assertTrue(isset($hooks[0]['entries'][0]['group']), "genHooks didn't return the correct hooks");
		$this->assertEquals($hooks[0]['entries'][0]['group'], "Overview",  "genHooks didn't return sane hooks");
	}

	public function testNotExistingHookErrors() {
		try {
			DashboardHooks::runHook("notexist");
		} catch (Exception $e) {
			return;
		}
		$this->fail("runHooks ran a hook that didn't exist");
	}

	public function testMissingHookErrors() {
		try {
			DashboardHooks::runHook("fake");
		} catch (Exception $e) {
			return;
		}
		$this->fail("runHooks found a function that didn't exist!");
	}

	public function testAllHooks() {
		$hooks = DashboardHooks::genHooks();
		return;
		$allhooks = array();
		foreach ($hooks as $page) {
			foreach ($page['entries'] as $item) {
				$allhooks[] = $item['func'];
			}
		}

		foreach ($allhooks as $hookname) {
			$this->assertNotNull(DashboardHooks::runHook($hookname), "Hook $hookname returned null");
		}
	}

}
