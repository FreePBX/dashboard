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

  // DASHBOARD_STATS_UPDATE_TIME
  // 
  $set['value'] = 6;
  $set['defaultval'] =& $set['value'];
  $set['readonly'] = 0;
  $set['hidden'] = 0;
  $set['level'] = 0;
  $set['module'] = 'dashboard';
  $set['category'] = 'GUI Behavior';
  $set['emptyok'] = 1;
  $set['description'] = 'Update rate in seconds of all sections of the System Status panel except the Info box.';
  $set['type'] = CONF_TYPE_UINT;
  $freepbx_conf->define_conf_setting('DASHBOARD_STATS_UPDATE_TIME',$set);

  // DASHBOARD_INFO_UPDATE_TIME
  //
  $set['value'] = 30;
  $set['defaultval'] =& $set['value'];
  $set['readonly'] = 0;
  $set['hidden'] = 0;
  $set['level'] = 0;
  $set['module'] = 'dashboard';
  $set['category'] = 'GUI Behavior';
  $set['emptyok'] = 1;
  $set['description'] = 'Update rate in seconds of the Info section of the System Status panel.';
  $set['type'] = CONF_TYPE_UINT;
  $freepbx_conf->define_conf_setting('DASHBOARD_INFO_UPDATE_TIME',$set);

  // SSHPORT
  //
  $set['value'] = '';
  $set['defaultval'] =& $set['value'];
  $set['readonly'] = 0;
  $set['hidden'] = 0;
  $set['level'] = 2;
  $set['module'] = 'dashboard';
  $set['category'] = 'System Setup';
  $set['emptyok'] = 1;
  $set['description'] = 'SSH port number configured on your system if not using the default port 22, this allows the dashboard monitoring to watch the poper port.';
  $set['type'] = CONF_TYPE_UINT;
  $freepbx_conf->define_conf_setting('SSHPORT',$set);

  // MAXCALLS
  //
  $set['value'] = '';
  $set['defaultval'] =& $set['value'];
  $set['readonly'] = 0;
  $set['hidden'] = 0;
  $set['level'] = 2;
  $set['module'] = 'dashboard';
  $set['category'] = 'GUI Behavior';
  $set['emptyok'] = 1;
  $set['description'] = 'Use this to pre-set the scale for maximum calls on the Dashboard display. If not set, the the scale is dynamically sized based on the active calls on the system.';
  $set['type'] = CONF_TYPE_UINT;
  $freepbx_conf->define_conf_setting('MAXCALLS',$set);

  $freepbx_conf->commit_conf_settings();
