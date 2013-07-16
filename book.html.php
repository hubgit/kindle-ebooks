<article itemscope itemtype="http://schema.org/Book">
	<a itemprop="url" href="<?= $item['url'] ?>" target="amazon">
		<? if (isset($item['image'])): ?>
			<div itemprop="image" class="image" style="background-image: url('<?= $item['image'] ?>')"></div>
		<? endif; ?>
		<div class="meta">
			<div itemprop="name" class="title"><?= $item['title'] ?></div>
			<div itemprop="author"><?= $item['author'] ?></div>
		</div>
		<? if (isset($item['reviews'])): ?>
			<iframe class="reviews" src="<?= $item['reviews'] ?>"></iframe>
		<? endif; ?>
	</a>
</article>