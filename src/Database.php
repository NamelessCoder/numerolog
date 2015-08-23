<?php
namespace NamelessCoder\Numerolog;

/**
 * Class Database
 */
class Database {

	/**
	 * @param string $packageName
	 */
	public function __construct($packageName) {
		$this->initialize($packageName);
	}

	/**
	 * @param string $packageName
	 * @return void
	 */
	public function initialize($packageName) {
		$databaseFilename = $this->getDatabaseFileName($packageName);
		$directory = pathinfo($databaseFilename, PATHINFO_DIRNAME);
		if (!file_exists($directory)) {
			mkdir($directory, 0777, TRUE);
		}
	}

	/**
	 * @param Query $query
	 * @return mixed
	 */
	public function query(Query $query) {
		$packageName = $query->getPackage();
		$counterName = $query->getCounter();
		$from = $query->getFrom();
		$to = $query->getTo();
		$count = $query->getCount();
		$action = $query->getAction();
		$result = NULL;
		switch ($action) {
			case Query::ACTION_GET:
				if ($from && $count) {
					$result = $this->getByRange($packageName, $counterName, $from, $to, $count);
				} elseif ($from) {
					$result = $this->getByRange($packageName, $counterName, $from, $to);
				} elseif ($count) {
					$result = $this->getByCount($packageName, $counterName, $count);
				} else {
					$result = $this->getLastValue($packageName, $counterName);
				}
				break;
			case Query::ACTION_SAVE:
				$result = $this->saveValue($packageName, $counterName, $query->getValue());
				break;
			default:
				throw new \RuntimeException(sprintf('Invalid Numerolog action: %s', $action));
		}
		if ((integer) $count === 1 || (integer) $count === 0) {
			return $result;
		} else {
			return array(
				'values' => $result,
				'statistics' => $this->getCalculator()->statistics($result)
			);
		}
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param mixed $value
	 * @return array
	 */
	public function saveValue($packageName, $counterName, $value) {
		$connection = $this->getDatabaseConnection($packageName);
		$connection->exec('CREATE TABLE IF NOT EXISTS ' . $counterName . ' (time REAL, value REAL)');
		$previous = $this->getLastValue($packageName, $counterName);
		$value = $this->getCalculator()->modify($previous, $value);
		$connection->exec('INSERT INTO ' . $counterName . ' (time, value) VALUES (' . microtime(TRUE) . ', ' . $value . ')');
		return array('value' => $value, 'last' => $previous);
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @return float
	 */
	protected function getLastValue($packageName, $counterName) {
		$values = $this->getByCount($packageName, $counterName, 1);
		return empty($values) ? 0 : $values[0]['value'];
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param integer $count
	 * @return array|NULL
	 */
	protected function getByCount($packageName, $counterName, $count) {
		$connection = $this->getDatabaseConnection($packageName);
		$result = $connection->query('SELECT time, value FROM ' . $counterName . ' ORDER BY time DESC LIMIT ' . (string) $count);
		return $result ? $this->convertResultToArray($result) : NULL;
	}

	/**
	 * @param string $packageName
	 * @param string $counterName
	 * @param integer $from Beginning UNIXTIME
	 * @param integer|NULL $to End UNIXTIME
	 * @param integer $count Maximum number of results to return
	 * @return array|NULL
	 */
	protected function getByRange($packageName, $counterName, $from, $to = NULL, $count = 1024) {
		if (!$to) {
			$to = microtime(TRUE);
		}
		$connection = $this->getDatabaseConnection($packageName);
		$result = $connection->query('SELECT time, value FROM ' . $counterName .
			' WHERE time >= ' . $from . ' AND time <= ' . $to .
			' ORDER BY time DESC LIMIT ' . $count);
		return $result ? $this->convertResultToArray($result) : NULL;
	}

	/**
	 * @param \SQLite3Result $result
	 * @return array
	 */
	protected function convertResultToArray(\SQLite3Result $result) {
		$results = array();
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$results[] = $row;
		}
		return $results;
	}

	/**
	 * @return string
	 */
	protected function getDatabaseFileBasePath() {
		return NUMEROLOG_DATABASE_BASEDIR;
	}

	/**
	 * @param string $packageName
	 * @return string
	 */
	protected function getDatabaseFileName($packageName) {
		return $this->getDatabaseFileBasePath() . $packageName . '.sqlite';
	}

	/**
	 * @param string $packageName
	 * @return \SQLite3
	 */
	protected function getDatabaseConnection($packageName) {
		return new \SQLite3($this->getDatabaseFileName($packageName));
	}

	/**
	 * @return Calculator
	 */
	protected function getCalculator() {
		return new Calculator();
	}

}
