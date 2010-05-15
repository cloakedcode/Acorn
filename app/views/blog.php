<? foreach ($posts as $post) : ?>
<div class="post">
	<div class="postheader">
		<div class="date shadow">
			<span class="day"><?= post_day($post) ?></span>
			<span class="month"><?= post_month($post) ?></span>
		<? if (posted_this_year($post) === false) : ?>
			<span class="year"><?= post_year($post) ?></span>
		<? endif ?>			
		</div>
		<p class="postinfo">
			<span><strong>Tags</strong></span>
			<span>
				<?= Acorn::renderPartial('tag', $tags) ?>
			</span>
		</p>
	</div>
	<div class="postcontent">
		<h2><a href="<?= $_SERVER['REQUEST_URI'].'/'.$post->id ?>"><?= $post->title ?></a></h2>
		<p><?= $post->body ?></p>
	</div>
</div>
<? endforeach ?>

<div class="pagination">
	<span class="leftalign"><a href="#">Previous</a></span>
	<span class="rightalign"><a href="#">Next</a></span>
	<div class="clear"></div>
</div>
