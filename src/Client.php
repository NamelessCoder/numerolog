<?php
namespace NamelessCoder\Numerolog;

/**
 * Class Client
 */
class Client {

	/**
	 * @param Query $query
	 * @return string
	 */
	public function query(Query $query) {
		return file_get_contents($this->getEndpointUrl() . $query->toQueryString());
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param integer $count
	 * @return string
	 */
	public function get($packageName, $counterName, $count = 1) {
		$query = new Query();
		$query->setPackage($packageName);
		$query->setCounter($counterName);
		$query->setCount($count);
		return $this->query($query);
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param string $from
	 * @param string $to
	 * @param integer $count
	 * @return string
	 */
	public function range($packageName, $counterName, $from, $to = NULL, $count = 1024) {
		$query = new Query();
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
		$query->setPackage($packageName);
		$query->setCounter($counterName);
		$query->setValue($value);
		return $this->query($query);
	}

	/**
	 * @return string
	 */
	protected function getEndpointUrl() {
		return 'http://numerolog.namelesscoder.net/index.php?';
	}

}
