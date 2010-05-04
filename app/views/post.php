<div class="post">
	<div class="postheader">
		<div class="date shadow">
			<span class="day"><?php echo post_day($post) ?></span>
			<span class="month"><?php echo post_month($post) ?></span>
		<?php if (posted_this_year($post) === false) : ?>
			<span class="year"><?php echo post_year($post) ?></span>
		<?php endif ?>
		</div>
		<?php if (count($tags) > 0) : ?>
		<p class="postinfo">
			<span><strong>Tags</strong></span>
			<span>
				<?php echo Acorn::renderPartial('./views/tags', $tags) ?>
			</span>
		</p>
		<?php endif ?>
	</div>
	<div class="postcontent">
		<h2><?php echo $post->title ?></h2>
		<p><?php echo $post->body ?></p>
	</div>
</div>

<div class="pagination">
	<?php if (empty($previous_post) === false) : ?>
	<span class="leftalign"><a href="<?php echo Acorn::url(array('id' => $previous_post->id)) ?>">&laquo;&nbsp;<?php echo $previous_post->title ?></a></span>
	<?php endif ?>
	<?php if (empty($next_post) === false) : ?>
	<span class="rightalign"><a href="<?php echo Acorn::url(array('id' => $next_post->id)) ?>"><?php echo $next_post->title ?>&nbsp;&raquo;</a></span>
	<?php endif ?>
	<div class="clear"></div>
</div>
