<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

namespace FreePBX\modules\Dashboard\Sections;

class Blogs {
	public $rawname = 'Blogs';

	public function getSections($order) {
		$feeds = \FreePBX::Config()->get('RSSFEEDS');
		$feeds = str_replace("\r","",(string) $feeds);
		$blogs = [];
		if(!empty($feeds)) {
			$feeds = explode("\n",$feeds);
			$i = 0;
			$urls = [];
			foreach($feeds as $feed) {
				$data = $this->getFeed($feed);
				if(!empty($data)) {
					$title = $data['title'];
					if(!empty($title)) {
						$blogs[] = ["title" => sprintf(_('%s Feed'),$title), "group" => _("Blogs"), "width" => "550px", "order" => $order[$feed] ?? '100', "section" => $i];
					} else {
						$blogs[] = ["title" => sprintf(_('%s Feed'),$feed), "group" => _("Blogs"), "width" => "500px", "order" => isset($order[$feed]) ? $order[$feed] : '100', "section" => $i];
					}
				}
				$i++;
			}
		}
		return $blogs;
	}

	public function getContent($section) {
		$feeds = \FreePBX::Config()->get('RSSFEEDS');
		$feeds = str_replace("\r","",(string) $feeds);
		if(empty($feeds)) {
			return '';
		}
		$feeds = explode("\n",$feeds);
		if(empty($feeds[$section])) {
			return '';
		}
		$feed = $this->getFeed($feeds[$section]);
		if(empty($feed)) {
			return '';
		}
		return load_view(dirname(__DIR__).'/views/sections/blog.php',["items" => $feed['items'], "limit" => 5]);
	}

	/**
	 * Get the feed from cache or retrieve it
	 * @param  string $feed The feed URL
	 * @return object       Reader object
	 */
	private function getFeed($feed) {
		$d = \FreePBX::Dashboard();
		try {
			$reader = new \SimplePie();
			$reader->set_cache_location(\FreePBX::Config()->get('ASTSPOOLDIR'));
			$reader->set_cache_class("SimplePie_Cache_File");

			$reader->set_feed_url($feed);
			$reader->enable_cache(true);
			$reader->init();

			$items = $reader->get_items();
			$content = ["title" => $reader->get_title(), "description" => $reader->get_description(), "items" => []];
			foreach ($items as $item) {
				$content['items'][] = ["title" => $item->get_title(), "url" => $item->get_permalink(), "content" => $item->get_description()];
			}
			$d->setConfig($feed, $content, "content");
		}	catch (\Exception) {
			$content = $d->getConfig($feed, "content");
		}
		return $content;
	}
}
