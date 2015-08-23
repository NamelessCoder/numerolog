<?php
namespace NamelessCoder\Numerolog;

/**
 * Class Database
 */
class Database {

	const DEFAULT_COUNT = 1024;

	const QUERY_COUNTER_TABLE = 'CREATE TABLE IF NOT EXISTS %s (time REAL, value REAL)';
	const QUERY_COUNTER_CHECK = "SELECT name FROM sqlite_master WHERE type = 'table' AND name = '%s'";
	const QUERY_COUNTER_SAVE = "INSERT INTO '%s' (time, value) VALUES (%d, %d)";
	const QUERY_COUNTER_SELECT_BY_COUNT = 'SELECT time, value FROM %s ORDER BY time DESC LIMIT %d';
	const QUERY_COUNTER_SELECT_BY_RANGE = 'SELECT time, value FROM %s WHERE time >= %d AND time <= %d ORDER BY time DESC LIMIT %d';

	const QUERY_TOKEN_TABLE = 'CREATE TABLE IF NOT EXISTS tokens (package STRING, token STRING)';
	const QUERY_TOKEN_CREATE = "INSERT INTO tokens (package, token) VALUES ('%s', '%s')";
	const QUERY_TOKEN_VALIDATE = "SELECT 1 FROM tokens WHERE package = '%s' AND token = '%s'";

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
		$token = $query->getToken();
		$result = NULL;
		$databaseFilename = $this->getDatabaseFileName($packageName);
		switch ($action) {
			case Query::ACTION_GET:
				if (!file_exists($databaseFilename)) {
					throw new NotFoundException(sprintf('Package %s has no counters. Save a value to initialize!', $packageName));
				}
				$this->validatePackageToken($packageName, $token);
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
				if (!file_exists($databaseFilename)) {
					$token = $this->createTokenForPackage($packageName, $token);
				} else {
					$this->validatePackageToken($packageName, $token);
				}
				$result = $this->saveValue($packageName, $counterName, $query->getValue());
				$result['token'] = $token;
				break;
			default:
				throw new \RuntimeException(sprintf('Invalid Numerolog action: %s', $action));
		}
		if ((integer) $count === 1 || (integer) $count === 0) {
			return $result;
		} else {
			return array(
				'values' => $result,
				'statistics' => $this->getCalculator()->statistics($result),
				'token' => $token
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
		if (!$connection->query(sprintf(static::QUERY_COUNTER_CHECK, $counterName))->fetchArray()) {
			#$token = $this->createTokenForCounter
		}
		$connection->exec(sprintf(static::QUERY_COUNTER_TABLE, $counterName));
		$previous = $this->getLastValue($packageName, $counterName);
		$value = $this->getCalculator()->modify($previous, $value);
		$connection->exec(sprintf(static::QUERY_COUNTER_SAVE, $counterName, microtime(TRUE), $value));
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
		$result = $connection->query(sprintf(static::QUERY_COUNTER_SELECT_BY_COUNT, $counterName, (string) $count));
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
	protected function getByRange($packageName, $counterName, $from, $to = NULL, $count = self::DEFAULT_COUNT) {
		if (!$to) {
			$to = microtime(TRUE);
		}
		$connection = $this->getDatabaseConnection($packageName);
		$result = $connection->query(sprintf(static::QUERY_COUNTER_SELECT_BY_RANGE, $counterName, $from, $to, $count));
		return $result ? $this->convertResultToArray($result) : NULL;
	}

	/**
	 * @param string $package
	 * @param string $token
	 * @return void
	 */
	protected function validatePackageToken($package, $token) {
		$connection = $this->getTokenDatabaseConnection();
		if (!$connection->query(sprintf(static::QUERY_TOKEN_VALIDATE, $package, $token))->fetchArray()) {
			throw new AccessException(sprintf('The provided token (%s) is not permitted to access package %s', $token, $package));
		}
	}

	/**
	 * @param string $package
	 * @param string $desiredToken
	 * @return string
	 */
	protected function createTokenForPackage($package, $desiredToken = NULL) {
		if (!$desiredToken) {
			$desiredToken = bin2hex(openssl_random_pseudo_bytes(16));
		}
		$connection = $this->getTokenDatabaseConnection();
		$connection->exec(static::QUERY_TOKEN_TABLE);
		$connection->exec(sprintf(static::QUERY_TOKEN_CREATE, $package, $desiredToken));
		return $desiredToken;
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
	 * @param string $packageName
	 * @return string
	 */
	protected function getTokenDatabaseFileName() {
		return $this->getDatabaseFileBasePath() . 'tokens.sqlite';
	}

	/**
	 * @param string $packageName
	 * @return string
	 */
	protected function getDatabaseFileName($packageName) {
		return $this->getDatabaseFileBasePath() . $packageName . '.sqlite';
	}

	/**
	 * @return \SQLite3
	 */
	protected function getTokenDatabaseConnection() {
		return new \SQLite3($this->getTokenDatabaseFileName());
	}

	/**
	 * @param string $packageName
	 * @return \SQLite3
	 */
	protected function getDatabaseConnection($packageName) {
		return new \SQLite3($this->getDatabaseFileName($packageName));
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	protected function getDatabaseFileBasePath() {
		return NUMEROLOG_DATABASE_BASEDIR;
	}

	/**
	 * @return Calculator
	 * @codeCoverageIgnore
	 */
	protected function getCalculator() {
		return new Calculator();
	}

}
