<?php
namespace NamelessCoder\Numerolog;

/**
 * Class Client
 */
class Client {

	/**
	 * @var string
	 */
	protected $endPointUrl = 'http://numerolog.namelesscoder.net/index.php?';

	/**
	 * @var string|NULL
	 */
	protected $package = NULL;

	/**
	 * @var string|NULL
	 */
	protected $token = NULL;

	/**
	 * @param Query $query
	 * @return string
	 */
	public function query(Query $query) {
		if (!$query->getPackage() && $this->package) {
			$query->setPackage($this->getPackage());
		}
		if (!$query->getToken() && $this->token) {
			$query->setToken($this->getToken());
		}
		$body = file_get_contents($this->getEndpointUrl() . $query->toQueryString());
		$decoded = json_decode($body, JSON_OBJECT_AS_ARRAY);
		if (NULL === $decoded) {
			throw new NumerologException($body);
		}
		if (!empty($decoded['error'])) {
			$exceptionClass = (isset($decoded['type']) ? $decoded['type'] : 'NamelessCoder\\Numerolog\\NumerologException');
			throw new $exceptionClass($decoded['error'], $decoded['code']);
		}
		return $decoded;
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param integer $count
	 * @return array
	 */
	public function get($packageName, $counterName, $count = 1) {
		$query = new Query();
		$query->setAction(Query::ACTION_GET);
		$query->setPackage($packageName);
		$query->setCounter($counterName);
		$query->setCount($count);
		return $this->query($query);
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param string $poll
	 * @param string|NULL $from
	 * @param string|NULL $to
	 * @param integer $count
	 * @return array
	 */
	public function poll($packageName, $counterName, $poll, $from = NULL, $to = NULL, $count = 1024) {
		$query = new Query();
		$query->setAction(Query::ACTION_POLL);
		$query->setPackage($packageName);
		$query->setCounter($counterName);
		$query->setCount($count);
		$query->setFrom($from);
		$query->setTo($to);
		$query->setPoll($poll);
		return $this->query($query);
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param string $from
	 * @param string $to
	 * @param integer $count
	 * @return array
	 */
	public function range($packageName, $counterName, $from, $to = NULL, $count = 1024) {
		$query = new Query();
		$query->setAction(Query::ACTION_GET);
		$query->setPackage($packageName);
		$query->setCounter($counterName);
		$query->setCount($count);
		$query->setFrom($from);
		$query->setTo($to);
		return $this->query($query);
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param float $value
	 * @return string
	 */
	public function save($packageName, $counterName, $value) {
		$query = new Query();
		$query->setAction(Query::ACTION_SAVE);
		$query->setPackage($packageName);
		$query->setCounter($counterName);
		$query->setValue($value);
		return $this->query($query);
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param float $value
	 * @return string
	 */
	public function compare($packageName, $counterName, $value) {
		$query = new Query();
		$query->setAction(Query::ACTION_COMPARE);
		$query->setPackage($packageName);
		$query->setCounter($counterName);
		$query->setValue($value);
		return $this->query($query);
	}

	/**
	 * @return string
	 */
	public function getEndPointUrl() {
		return $this->endPointUrl;
	}

	/**
	 * @param string $endPointUrl
	 * @return void
	 */
	public function setEndPointUrl($endPointUrl) {
		$this->endPointUrl = $endPointUrl;
	}

	/**
	 * @return string|NULL
	 */
	public function getPackage() {
		return $this->package;
	}

	/**
	 * @param string|NULL $package
	 * @return void
	 */
	public function setPackage($package) {
		$this->package = $package;
	}

	/**
	 * @return string|NULL
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * @param string|NULL $token
	 * @return void
	 */
	public function setToken($token) {
		$this->token = $token;
	}

}
