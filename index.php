<?php

// configuration
$config = parse_ini_file(__DIR__ . '/config.ini');
define('BASE_URL', $config['base_url']);

// description form
$options = array(
	'sorts' => array(
		'reviewrank' => 'Top Rated',
		'salesrank' => 'Top Selling',
	)
);

if (empty($_GET)) {
	header('Content-Type: text/html;charset=UTF-8');
	require __DIR__ . '/description.html.php';
	exit();
}

// input
$input = array(
	'sort' => isset($_GET['sort']) && ($sort = $_GET['sort']) && array_key_exists($sort, $options['sorts']) ? $sort : 'reviewrank',
	'page' => isset($_GET['page']) && ($page = $_GET['page']) ? min(10, max(1, (int) $page)) : 1,
	'keywords' => isset($_GET['keywords']) && $_GET['keywords'] ? $_GET['keywords'] : null,
);

// client
require __DIR__ . '/AmazonClient.php';

$host = 'webservices.amazon.com';
$client = new AmazonClient($host);
//$client->debug = true;

// request

// Kindle eBooks browse nodes
$browseNodes = array(
	'webservices.amazon.com' => '154606011',
	'webservices.amazon.co.uk' => '341689031',
);

$params = array(
	'Operation' => 'ItemSearch',
	'BrowseNode' => $browseNodes[$host],
	'SearchIndex' => 'Books', // or KindleStore
	'ResponseGroup' => 'ItemAttributes,Images,Reviews',
	'Sort' => $input['sort'],
	'ItemPage' => $input['page'],
	'Keywords' => $input['keywords'],
);

$xpath = $client->get($params);

// response
$items = array();

foreach ($xpath->query('a:Items/a:Item') as $item) {
	$itemAttributes = $xpath->query('a:ItemAttributes', $item)->item(0);

	$pages = $xpath->evaluate('number(a:NumberOfPages)', $itemAttributes);
	$isbn = $xpath->evaluate('string(a:EISBN)', $itemAttributes);

	$items[] = array(
		'@context' => 'http://git.macropus.org/amazon-schema/book.json',
		'@type' => 'http://schema.org/Book',
		'@id' => 'http://amazon.com/dp/' . $xpath->evaluate('string(a:ASIN)', $item),
		'url' => $xpath->evaluate('string(a:DetailPageURL)', $item),
		'author' =>  $xpath->evaluate('string(a:Author)', $itemAttributes),
		'title' =>  $xpath->evaluate('string(a:Title)', $itemAttributes),
		'date' =>  $xpath->evaluate('string(a:PublicationDate)', $itemAttributes),
		'image' =>  $xpath->evaluate('string(a:LargeImage/a:URL)', $item),
		'pages' =>  is_nan($pages) ? null : $pages,
		'isbn' =>  $isbn ? $isbn : null,
		'reviews' =>  $xpath->evaluate('string(a:CustomerReviews/a:IFrameURL)', $item),
	);
}

// output
$data = array(
	'_links' => build_links($xpath, $input),
	'_embedded' => $items,
);

header('Content-Type: application/json;charset=UTF-8');
print json_encode($data, JSON_PRETTY_PRINT);

function build_links($xpath, $input) {
	ksort($input);

	$links = array('self' => array(), 'alternate' => array());

	// self
	$links['self']['href'] = build_url($input);

	// HTML alternate
	$links['alternate']['text/html']['href'] = $xpath->evaluate('string(a:Items/a:MoreSearchResultsUrl)');

	// pagination
	$totalPages = min(10, $xpath->evaluate('number(a:Items/a:TotalPages)'));

	if ($input['page'] < $totalPages) {
		$links['next']['href'] = build_url(array('page' => $input['page'] + 1) + $input);
	}

	if ($input['page'] > 1) {
		$links['prev']['href'] = build_url(array('page' => $input['page'] - 1) + $input);
	}

	return $links;
}

function build_url($params) {
	return BASE_URL . '?' . http_build_query($params);
}
