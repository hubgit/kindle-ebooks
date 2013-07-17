<?php

date_default_timezone_set('UTC');

// Create keys at https://console.aws.amazon.com/iam/home?#security_credential

class AmazonClient {
	/** @var cURL */
	public $curl;

	public $debug = false;

	private $version;
	private $host;
	private $path = '/onca/xml'; // turn into a host-keyed array if needed

	private $config = array();

	// constructor
	public function __construct($host = 'webservices.amazon.com', $version = '2011-08-01') {
		$this->host = $host;
		$this->version = $version;

		$this->configure();

		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_ENCODING, 'gzip');
		//curl_setopt($this->curl, CURLOPT_VERBOSE, true);
	}

	// GET request
	public function get($params = array()) {
		$params['AWSAccessKeyId'] = $this->config['access_key'];
		$params['AssociateTag'] = $this->config['associate_tag'][$this->host];
		$params['Version'] = $this->version;
		$params['Timestamp'] = date('c');
		$params['Service'] = 'AWSECommerceService';
		$params['Signature'] = $this->sign('GET', $params);

		$url = 'http://' . $this->host . $this->path . '?' . $this->build_query($params);

		if ($this->debug) {
			print "$url\n";
		}

		curl_setopt($this->curl, CURLOPT_URL, $url);

		return $this->exec();
	}

	// read configuration from ~/.config/amazon.ini
	protected function configure() {
		$configFile = getenv('HOME') . '/.config/amazon.ini';

		if (!file_exists($configFile)) {
			exit(sprintf("Config file %s does not exist\n", $configFile));
		}

		$this->config = parse_ini_file($configFile);

		foreach (array('access_key', 'access_secret') as $field) {
			if (!isset($this->config[$field])) {
				printf("Add %s to %s\n", $field, $configFile);
				exit();
			}
		}

		foreach (array('associate_tag') as $field) {
			if (!isset($this->config[$field][$this->host])) {
				printf("Add %s[%s] to %s\n", $field, $this->host, $configFile);
				exit();
			}
		}
	}

	// send the request and parse the response
	protected function exec() {
		$result = curl_exec($this->curl);
		$code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

		switch ($code) {
			case 200:
				$dom = new DOMDocument;
				$dom->preserveWhiteSpace = false;
				$dom->loadXML($result);

				if ($this->debug) {
					$dom->formatOutput = true;
					print $dom->saveXML();
				}

				$xpath = new DOMXPath($dom);
				$xpath->registerNamespace('a', 'http://webservices.amazon.com/AWSECommerceService/' . $this->version);

				return $xpath;

			default:
				print "Error $code\n";
				print_r($result, true);
				return null;
		}
	}

	// build the request signature
	protected function sign($verb = 'GET', $params = array()) {
		ksort($params);
		$request = implode("\n", array($verb, $this->host, $this->path, $this->build_query($params)));
		$signature = hash_hmac('sha256', $request, $this->config['access_secret'], true);

		return base64_encode($signature);
	}

	// build the query string, using '%20' for spaces
	protected function build_query($params = array()) {
		return http_build_query($params, null, '&', PHP_QUERY_RFC3986);
	}
}