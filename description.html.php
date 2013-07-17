<!doctype html>
<meta charset="utf-8">
<title>Kindle eBooks</title>
<style>body { font-family: sans-serif; } label { display: block; margin: 10px 0; }</style>

<form>
	<label>Keywords <input name="keywords" type="search" size="50"></label>

	<label>Country <select name="host">
		<? foreach ($options['hosts'] as $key => $value): ?>
			<option value="<?= $key ?>"><?= $value ?></option>
		<? endforeach; ?>
	</select></label>

	<label>Sort <select name="sort">
		<? foreach ($options['sorts'] as $key => $value): ?>
			<option value="<?= $key ?>"><?= $value ?></option>
		<? endforeach; ?>
	</select></label>

	<label>Page <input name="page" type="number" min="1" max="10" value="1"></label>

	<button type="submit">Search</button>
</form>

<a href="https://github.com/hubgit/kindle-ebooks/"><img style="position: absolute; top: 0; right: 0; border: 0;"
	src="https://s3.amazonaws.com/github/ribbons/forkme_right_orange_ff7600.png" alt="Fork me on GitHub"></a>