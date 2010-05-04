<?php foreach ($posts as $post) : ?>
<div class="post">
	<div class="postheader">
		<div class="date shadow">
			<span class="day"><?php echo post_day($post) ?></span>
			<span class="month"><?php echo post_month($post) ?></span>
		<?php if (posted_this_year($post) === false) : ?>
			<span class="year"><?php echo post_year($post) ?></span>
		<?php endif ?>			
		</div>
	</div>
	<div class="postcontent">
		<h2><a href="<?php echo $_SERVER['REQUEST_URI'].'/'.$post->id ?>"><?php echo $post->title ?></a></h2>
		<p><?php echo $post->body ?></p>
	</div>
</div>
<?php endforeach ?>
