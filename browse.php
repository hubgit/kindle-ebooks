<?php

require __DIR__ . '/AmazonClient.php';

$client = new AmazonClient;

$params = array(
	'Operation' => 'BrowseNodeLookup',
	'BrowseNodeId' => '154606011', // Kindle eBooks
	'ResponseGroup' => 'TopSellers,MostGifted,NewReleases',
);

$xpath = $client->get($params);
$nodes = $xpath->query('BrowseNodes/BrowseNode');