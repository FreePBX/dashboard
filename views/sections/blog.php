<ul>
<?php 
foreach($items as $item) {
	if ($item['description']) {
		$desc = $item['description'];
		$tooltip = substr(strip_tags($desc,'<br>'),0,200)."...";
	} else {
		$tooltip = "";
	}
	$href = $item['link'];
	$title = $item['title'];
	print "<li><small><a data-toggle='tooltip' title='$tooltip' href='$href' target='_blank'>$title</a></small></li>\n";
}
?>
</ul>
<script>
	$('li a').tooltip();
</script>
