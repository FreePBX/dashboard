<ul>
<?php
$c = 0;
foreach($items as $item) {
	if($c > $limit) {
		break;
	}
	if ((string)$item->description) {
		$desc = (string)$item->description;
		$tooltip = substr(strip_tags($desc,'<br>'),0,200)."...";
	} else {
		$tooltip = "";
	}
	$href = (string)$item->link;
	$title = (string)$item->title;
	print "<li><small><a data-toggle='tooltip' title='$tooltip' href='$href' target='_blank'>$title</a></small></li>\n";
	$c++;
}
?>
</ul>
<script>
	$('li a').tooltip();
</script>
