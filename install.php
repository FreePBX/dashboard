<?php
$set = array();
// RSSFEEDS
$set['category'] = 'System Setup';
$set['value'] = "http://www.freepbx.org/rss.xml\nhttp://feeds.feedburner.com/InsideTheAsterisk";
$set['defaultval'] = $set['value'];
$set['name'] = 'RSS Feeds';
$set['description'] = 'RSS Feeds that are displayed in UCP and Dashboard. Separate each feed by a new line';
$set['hidden'] = 0;
$set['emptyok'] = 1;
$set['readonly'] = 0;
$set['level'] = 0;
$set['options'] = '';
$set['module'] = '';
$set['type'] = CONF_TYPE_TEXTAREA;
FreePBX::Config()->define_conf_setting('RSSFEEDS',$set);
FreePBX::Config()->commit_conf_settings();
