<!doctype html>
<meta charset="utf-8">
<title>Browse Kindle eBooks</title>
<link rel="stylesheet" href="books.css">

<? foreach ($sets as $set): ?>
	<h2><?= $set['type'] ?></h2>

<? foreach ($set['items'] as $item) require __DIR__ . '/book.html.php'; ?>

<? endforeach; ?>