<?php
namespace NamelessCoder\Numerolog;

/**
 * Class Server
 */
class Server {

	const COMMAND_RECORD = 'record';
	const COMMAND_GET = 'get';

	/**
	 * @return Query
	 */
	public function detectQuery() {
		$query = new Query();
		foreach ($_GET as $name => $value) {
			$setter = 'set' . ucfirst($name);
			if (method_exists($query, $setter)) {
				$query->$setter($value);
			}
		}
		return $query;
	}

	/**
	 * @param Query $query
	 * @return mixed
	 */
	public function query(Query $query) {
		$packageName = $query->getPackage();
		$database = new Database($packageName);
		try {
			$data = $database->query($query);
		} catch (\RuntimeException $error) {
			$data = array(
				'error' => $error->getMessage(),
				'type' => get_class($error),
				'code' => $error->getCode()
			);
		}
		return $data;
	}

}
