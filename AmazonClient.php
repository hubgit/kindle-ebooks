<?php

date_default_timezone_set('UTC');

// Create keys at https://console.aws.amazon.com/iam/home?#security_credential

class AmazonClient {
	/** @var cURL */
	public $curl;

	public $version = '2011-08-01';

	private $config = array();

	public function __construct() {
		$this->configure();

		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_VERBOSE, true);
		curl_setopt($this->curl, CURLOPT_ENCODING, 'gzip,deflate');
	}

	public function get($params = array(), $host = 'webservices.amazon.com') {
		$params['AWSAccessKeyId'] = $this->config['access_key'];
		$params['AssociateTag'] = $this->config['associate_tag'];
		$params['Version'] = $this->version;
		$params['Timestamp'] = date('c');
		$params['Service'] = 'AWSECommerceService';
		$params['Signature'] = $this->sign('GET', $params, $host);

		$url = 'http://' . $host . '/onca/xml?' . http_build_query($params);
		print "$url\n";

		curl_setopt($this->curl, CURLOPT_URL, $url);

		return $this->exec();
	}

	protected function configure() {
		$configFile = getenv('HOME') . '/.config/amazon.ini';

		if (!file_exists($configFile)) {
			exit(sprintf("Config file %s does not exist\n", $configFile));
		}

		$this->config = parse_ini_file($configFile);

		foreach (array('access_key', 'access_secret', 'associate_tag') as $field) {
			if (!isset($this->config[$field])) {
				printf("Add %s to %s\n", $field, $configFile);
				exit();
			}
		}
	}

	protected function exec() {
		$result = curl_exec($this->curl);
		$code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

		switch ($code) {
			case 200:
				$dom = new DOMDocument;
				$dom->preserveWhiteSpace = false;
				$dom->loadXML($result);
				$dom->formatOutput = true;

				//print $dom->saveXML();

				$xpath = new DOMXPath($dom);
				$xpath->registerNamespace('a', 'http://webservices.amazon.com/AWSECommerceService/' . $this->version);

				return $xpath;

			default:
				print "Error $code\n";
				print_r($result, true);
				return null;
		}
	}

	protected function sign($verb = 'GET', $params = array(), $host = 'webservices.amazon.com', $path = '/onca/xml') {
		ksort($params);
		$request = implode("\n", array($verb, $host, $path, http_build_query($params)));
		$signature = hash_hmac('sha256', $request, $this->config['access_secret'], true);

		return base64_encode($signature);
	}
}