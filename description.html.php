<!doctype html>
<meta charset="utf-8">
<title>Kindle eBooks</title>
<style>body { font-family: sans-serif; } label { display: block; margin: 10px 0; }</style>

<form>
	<label>Keywords <input name="keywords" type="search" size="50"></label>

	<label>Sort <select name="sort">
		<option selected>reviewrank</option>
		<option>salesrank</option>
	</select></label>

	<label>Page <input name="page" type="number" min="1" max="10" value="1"></label>

	<button type="submit">Search</button>
</form>