<?php

require __DIR__ . '/AmazonClient.php';

$client = new AmazonClient;

$params = array(
	'Operation' => 'ItemSearch',
	'BrowseNode' => '154606011', // Kindle eBooks
	'ResponseGroup' => 'ItemAttributes,Reviews,Similarities,EditorialReview,Images',
	'SearchIndex' => 'Books', // KindleStore
	'Sort' => 'reviewrank',
	//'Keywords' => $_GET['keywords'],
);

$items = array();

foreach (range(1, 10) as $page) {
	$params['ItemPage'] = $page;

	$xpath = $client->get($params);

	foreach ($xpath->query('a:Items/a:Item') as $item) {
		$itemAttributes = $xpath->query('a:ItemAttributes', $item)->item(0);

		$items[] = array(
			'url' => $xpath->evaluate('string(a:DetailPageURL)', $item),
			'image' =>  $xpath->evaluate('string(a:LargeImage/a:URL)', $item),
			'reviews' =>  $xpath->evaluate('string(a:CustomerReviews/a:IFrameURL)', $item),
			'title' => $xpath->evaluate('string(a:Title)', $itemAttributes),
			'author' => $xpath->evaluate('string(a:Author)', $itemAttributes),
		);
	}
}

//print_r($sets);

require __DIR__ . '/search.html.php';