<?php
// vim: set ai ts=4 sw=4 ft=php:
//
// License for all code of this FreePBX module can be found in the license file inside the module directory
// Copyright 2006-2014 Schmooze Com Inc.

namespace FreePBX\modules\Dashboard\Sections;

class Blogs {
	public $rawname = 'Blogs';
	private $urls = array();

	public function __construct() {
		$feeds = \FreePBX::Config()->get('RSSFEEDS');
		$feeds = str_replace("\r","",$feeds);
		if(!empty($feeds)) {
			$feeds = explode("\n",$feeds);
			$i = 0;
			$this->urls = array();
			foreach($feeds as $feed) {
				$this->urls['rss-'.$i] = $feed;
				$i++;
			}
		}
	}

	public function getSections($order) {
		$blogs = array();
		foreach($this->urls as $name => $url) {
			$title = $this->getTitle($name);
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

	public function getTitle($section) {
		if(isset($this->urls[$section])) {
			$contents = $this->getURL($this->urls[$section]);
			libxml_use_internal_errors(true);
			$doc = simplexml_load_string($contents);
			if (!$doc) {
				return false;
			}

			return $doc->channel->title;
		}
	}

	public function getContent($section) {
		if(isset($this->urls[$section])) {
			$contents = $this->getURL($this->urls[$section]);
			libxml_use_internal_errors(true);
			$doc = simplexml_load_string($contents);
			if (!$doc) {
				$errors = libxml_get_errors();

				$html = '';
				foreach ($errors as $error) {
					$html .= nl2br(display_xml_error($error, $contents)) . "<br/>";
				}

				libxml_clear_errors();
				return $html;
			}

			$items = array();
			$limit = 5;
			$c = 0;
			return load_view(dirname(__DIR__).'/views/sections/blog.php',array("items" => $doc->channel->item, "limit" => 5));
		}
	}

	public function display_xml_error($error, $xmlstr) {
		$xml = explode("\n", $xmlstr);
		$return  = $xml[$error->line - 1] . "\n";
		$return .= str_repeat('-', $error->column) . "^\n";

		switch ($error->level) {
			case LIBXML_ERR_WARNING:
				$return .= "Warning $error->code: ";
			break;
			case LIBXML_ERR_ERROR:
				$return .= "Error $error->code: ";
			break;
			case LIBXML_ERR_FATAL:
				$return .= "Fatal Error $error->code: ";
			break;
		}

		$return .= trim($error->message) .
		"\n  Line: $error->line" .
		"\n  Column: $error->column";

		if ($error->file) {
			$return .= "\n  File: $error->file";
		}

		return "$return\n\n--------------------------------------------\n\n";
	}

	private function getURL($url) {

		// Check to see if we've already grabbed this recently
		$d = \FreePBX::Dashboard();
		$res = $d->getConfig($url, "Blogs");
		// 3 Hours.
		$expired = time() - 10800;
		// Has this expired, or is it new?
		if (!$res || $res['timestamp'] < $expired) {
			$urlParts = parse_url($url);
			$host = !empty($urlParts['port']) ? $urlParts['port'] . ":" . $urlParts['host'] : $urlParts['host'];
			$query = !empty($urlParts['query']) ? '?'.$urlParts['query'] : '';
			$p = new \Pest($urlParts['scheme']."://".$host.$query);
			$p->curl_opts[CURLOPT_FOLLOWLOCATION] = true;
			$p->curl_opts[CURLOPT_CONNECTTIMEOUT] = 10;
			try {
				$contents = $p->get($urlParts['path']);
				$res['timestamp'] = time();
				$res['contents'] = $contents;
				$d->setConfig($url, $res, "Blogs");
			} catch(\Exception $e) {}
		}
		return $res['contents'];
	}
}
