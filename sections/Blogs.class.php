<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

namespace FreePBX\modules\Dashboard\Sections;
use PicoFeed\Reader\Reader;

class Blogs {
	public $rawname = 'Blogs';
	private $urls = array();
	private $fastFeed = null;

	public function __construct() {
		$reader = new Reader;

		$feeds = \FreePBX::Config()->get('RSSFEEDS');
		$feeds = str_replace("\r","",$feeds);
		if(!empty($feeds)) {
			$feeds = explode("\n",$feeds);
			$i = 0;
			$this->urls = array();
			$d = \FreePBX::Dashboard();

			foreach($feeds as $feed) {

				$etag = $d->getConfig($feed, "etag");
				$last_modified = $d->getConfig($feed, "last_modified");
				try {
					$resource = $reader->download($feed, $last_modified, $etag);
					if ($resource->isModified()) {

						$parser = $reader->getParser(
							$resource->getUrl(),
							$resource->getContent(),
							$resource->getEncoding()
						);

						$content = $parser->execute();
						$etag = $resource->getEtag();
						$last_modified = $resource->getLastModified();

						$d->setConfig($feed, $content, "content");
						$d->setConfig($feed, $etag, "etag");
						$d->setConfig($feed, $last_modified, "last_modified");
					} else {
						$content = $d->getConfig($feed, "content");
					}
					$this->urls['rss-'.$i] = $content;
					$i++;
				}	catch (\PicoFeed\PicoFeedException $e) {
					$content = $d->getConfig($feed, "content");
					if(!empty($content)) {
						$this->urls['rss-'.$i] = $content;
						$i++;
					}
				}
			}
		}
	}

	public function getSections($order) {
		foreach($this->urls as $name => $url) {
			$feed = $this->urls[$name];
			$title = $feed->title;
			if(!empty($title)) {
				$blogs[] = array(
					"title" => $title . " " . _('Feed'),
					"group" => _("Blogs"),
					"width" => "550px",
					"order" => isset($order[$name]) ? $order[$name] : '100',
					"section" => $name
				);
			} else {
				$blogs[] = array(
					"title" => $name . " " . _('Feed'),
					"group" => _("Blogs"),
					"width" => "500px",
					"order" => isset($order[$name]) ? $order[$name] : '100',
					"section" => $name
				);
			}
		}
		return $blogs;
	}

	public function getContent($section) {
		$feed = $this->urls[$section];
		return load_view(dirname(__DIR__).'/views/sections/blog.php',array("items" => $feed->items, "limit" => 5));
	}
}
