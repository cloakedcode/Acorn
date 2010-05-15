<div class="post">
	<div class="postheader">
		<div class="date shadow">
			<span class="day"><?= post_day($post) ?></span>
			<span class="month"><?= post_month($post) ?></span>
		<?php if (posted_this_year($post) === false) : ?>
			<span class="year"><?= post_year($post) ?></span>
		<?php endif ?>
		</div>
		<?php if (count($tags) > 0) : ?>
		<p class="postinfo">
			<span><strong>Tags</strong></span>
			<span>
				<?= Acorn::renderPartial('./views/tags', $tags) ?>
			</span>
		</p>
		<?php endif ?>
	</div>
	<div class="postcontent">
		<h2><?= $post->title ?></h2>
		<p><?= $post->body ?></p>
	</div>
</div>

<div class="pagination">
	<?php if (empty($previous_post) === false) : ?>
	<span class="leftalign"><a href="<?= Acorn::url(array('id' => $previous_post->id)) ?>">&laquo;&nbsp;<?= $previous_post->title ?></a></span>
	<?php endif ?>
	<?php if (empty($next_post) === false) : ?>
	<span class="rightalign"><a href="<?= Acorn::url(array('id' => $next_post->id)) ?>"><?= $next_post->title ?>&nbsp;&raquo;</a></span>
	<?php endif ?>
	<div class="clear"></div>
</div>
