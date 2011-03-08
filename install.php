<?php
//This file is part of FreePBX.
//
//    FreePBX is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    FreePBX is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with FreePBX.  If not, see <http://www.gnu.org/licenses/>.
//
// Copyright (C) 2011 Philippe Lindheimer
//

$freepbx_conf =& freepbx_conf::create();

// DASHBOARD_STATS_UPDATE_TIME and DASHBOARD_INFO_UPDATE_TIME are initialized as CONF_TYPE_INT during migration. We need
// first time after upgrade we need to change to the proper type and set the value as close to what they had it before
// as we can come up with.
//
$stats_options = array(6,10,20,30,45,60,120,300,600);
$info_options = array(15,30,60,120,300,600);

$stats_value = 6;
$info_value = 30;

if ($freepbx_conf->conf_setting_exists('DASHBOARD_STATS_UPDATE_TIME') || $freepbx_conf->conf_setting_exists('DASHBOARD_INFO_UPDATE_TIME')) {
  $full_settings =& $freepbx_conf->get_conf_settings();

  if ($full_settings['DASHBOARD_STATS_UPDATE_TIME']['type'] != CONF_TYPE_SELECT) {
    $old_val = $full_settings['DASHBOARD_STATS_UPDATE_TIME']['value'];
    $freepbx_conf->remove_conf_setting('DASHBOARD_STATS_UPDATE_TIME');
    $stats_value = $stats_options[0];
    foreach ($stats_options as $val) {
      if ($old_val < $val) {
        break;
      }
      $stats_value = $val;
    }
    if ($stats_value != $old_val) {
      out(sprintf(_("%s changed from %s to %s"),'DASHBOARD_STATS_UPDATE_TIME',$old_val,$stats_value));
    }
  }
  if ($full_settings['DASHBOARD_INFO_UPDATE_TIME']['type'] != CONF_TYPE_SELECT) {
    $old_val = $full_settings['DASHBOARD_INFO_UPDATE_TIME']['value'];
    $freepbx_conf->remove_conf_setting('DASHBOARD_INFO_UPDATE_TIME');
    $info_value = $info_options[0];
    foreach ($info_options as $val) {
      if ($old_val < $val) {
        break;
      }
      $info_value = $val;
    }
    if ($info_value != $old_val) {
      out(sprintf(_("%s changed from %s to %s"),'DASHBOARD_INFO_UPDATE_TIME',$old_val,$info_value));
    }
  }
  unset($full_settings);
}

// DASHBOARD_STATS_UPDATE_TIME
// 
$set['value'] = $stats_value;
$set['defaultval'] =& $set['value'];
$set['options'] = $stats_options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'dashboard';
$set['category'] = 'GUI Behavior';
$set['emptyok'] = 0;
$set['name'] = 'Dashboard Stats Update Frequency';
$set['description'] = 'Update rate in seconds of all sections of the System Status panel except the Info box.';
$set['type'] = CONF_TYPE_SELECT;
$freepbx_conf->define_conf_setting('DASHBOARD_STATS_UPDATE_TIME',$set);

// DASHBOARD_INFO_UPDATE_TIME
//
$set['value'] = $info_value;
$set['defaultval'] =& $set['value'];
$set['options'] = $info_options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'dashboard';
$set['category'] = 'GUI Behavior';
$set['emptyok'] = 0;
$set['name'] = 'Dashboard Info Update Frequency';
$set['description'] = 'Update rate in seconds of the Info section of the System Status panel.';
$set['type'] = CONF_TYPE_SELECT;
$freepbx_conf->define_conf_setting('DASHBOARD_INFO_UPDATE_TIME',$set);

// SSHPORT
//
$set['value'] = '';
$set['defaultval'] =& $set['value'];
$set['options'] = array(1,65535);
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 2;
$set['module'] = 'dashboard';
$set['category'] = 'System Setup';
$set['emptyok'] = 1;
$set['name'] = 'Dashboard Non-Std SSH Port';
$set['description'] = 'SSH port number configured on your system if not using the default port 22, this allows the dashboard monitoring to watch the poper port.';
$set['type'] = CONF_TYPE_INT;
$freepbx_conf->define_conf_setting('SSHPORT',$set);

// MAXCALLS
//
$set['value'] = '';
$set['defaultval'] =& $set['value'];
$set['options'] = array(0,3000);
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 2;
$set['module'] = 'dashboard';
$set['category'] = 'GUI Behavior';
$set['emptyok'] = 1;
$set['name'] = 'Dashboard Max Calls Initial Scale';
$set['description'] = 'Use this to pre-set the scale for maximum calls on the Dashboard display. If not set, the the scale is dynamically sized based on the active calls on the system.';
$set['type'] = CONF_TYPE_INT;
$freepbx_conf->define_conf_setting('MAXCALLS',$set);

$freepbx_conf->commit_conf_settings();
