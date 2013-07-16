<?php

require __DIR__ . '/AmazonClient.php';

$client = new AmazonClient;

$params = array(
	'Operation' => 'ItemSearch',
	'BrowseNode' => '154606011', // Kindle eBooks
	'ResponseGroup' => 'ItemAttributes,Reviews,Similarities,EditorialReview',
	'SearchIndex' => 'Books',
	'Sort' => 'reviewrank',
	'Keywords' => $_GET['keywords'],
);

$xpath = $client->get($params);
$nodes = $xpath->query('Items/Item');