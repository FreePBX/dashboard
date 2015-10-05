<ul>
<?php
$c = 0;
foreach($items as $item) {
	if($c > $limit) {
		break;
	}
	$description = $item->content;
	if ($description) {
		$tooltip = substr(strip_tags($description,'<br>'),0,200)."...";
	} else {
		$tooltip = "";
	}
	$href = $item->url;
	$title = $item->title;
	print "<li><small><a data-toggle='tooltip' title='$tooltip' href='$href' target='_blank'>$title</a></small></li>\n";
	$c++;
}
?>
</ul>
<script>
	$('li a').tooltip();
</script>
