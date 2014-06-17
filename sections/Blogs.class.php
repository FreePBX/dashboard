<?php

namespace FreePBX\modules\Dashboard\Sections;

class Blogs {
	public $rawname = 'Blogs';
	private $urls = array(
		"FreePBX" => "http://www.freepbx.org/rss.xml",
		"Digium" => "http://blogs.digium.com/feed/"
	);

	public function getSections() {
		$blogs = array();
		foreach($this->urls as $name => $url) {
			$blogs[] = array(
				"title" => $name . " Blog",
				"group" => _("Blogs"),
				"width" => "400px",
				"order" => '100',
				"section" => $name
			);
		}
		return $blogs;
	}

	public function getContent($section) {
		if(isset($this->urls[$section])) {
			$contents = file_get_contents($this->urls[$section]);
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
		}
		$items = array();
		$limit = 5;
		$c = 0;
		foreach($doc->channel->item as $item) {
			if($c == $limit) {
				break;
			}
			$items[] = json_decode(json_encode($item),true);
			$c++;
		}
		return load_view(dirname(__DIR__).'/views/sections/blog.php',array("items" => $items));
	}

	function display_xml_error($error, $xmlstr) {
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
}
