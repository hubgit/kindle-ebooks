<?php

require __DIR__ . '/AmazonClient.php';

$client = new AmazonClient;

$params = array(
	'Operation' => 'BrowseNodeLookup',
	'BrowseNodeId' => '154606011', // Kindle eBooks
	'ResponseGroup' => 'TopSellers,MostGifted,NewReleases',
);

$xpath = $client->get($params);

$sets = array();

foreach ($xpath->query('a:BrowseNodes/a:BrowseNode/a:TopItemSet') as $topItemSet) {
	$items = array();

	foreach ($xpath->query('a:TopItem', $topItemSet) as $topItem) {
		$items[] = array(
			'url' => $xpath->evaluate('string(a:DetailPageURL)', $topItem),
			'title' => $xpath->evaluate('string(a:Title)', $topItem),
			'author' => $xpath->evaluate('string(a:Author)', $topItem),
		);
	}

	$sets[] = array(
		'type' => $xpath->evaluate('string(a:Type)', $topItemSet),
		'items' => $items,
	);
}

//print_r($sets);

require __DIR__ . '/browse.html.php';