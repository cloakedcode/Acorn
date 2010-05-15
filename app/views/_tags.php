<?
$count = count($tags);
$i = 0;

while ($i < $count)
{
	$tag = $tags[$i];

	echo "<a href='".Acorn::url(array('action' => 'tag', 'id' => $tag->slug))."'>{$tag->name}</a>";

	if ($i !== $count -1)
	{
		echo ', ';
	}

	$i++;
}

?>
