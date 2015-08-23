<?php
namespace NamelessCoder\Numerolog;

/**
 * Class Query
 */
class Query {

	const ACTION_SAVE = 'save';
	const ACTION_GET = 'get';

	/**
	 * @var string
	 */
	protected $package;

	/**
	 * @var string
	 */
	protected $counter;

	/**
	 * @var string
	 */
	protected $action = self::ACTION_GET;

	/**
	 * @var mixed
	 */
	protected $value = 0;

	/**
	 * @var string|NULL
	 */
	protected $from;

	/**
	 * @var string|NULL
	 */
	protected $to;

	/**
	 * @var integer|NULL
	 */
	protected $count;

	/**
	 * @param array $parameters
	 */
	public function __construct(array $parameters = array()) {
		if (isset($parameters[0])) {
			// listed parameters with --name "value"
			$name = NULL;
			$value = NULL;
			foreach ($parameters as $parameter) {
				if (substr($parameter, 0, 2) === '--') {
					$name = substr($parameter, 2);
				} else {
					$value = $parameter;
				}
				if ($name !== NULL && $value !== NULL) {
					$this->$name = $value;
					$name = NULL;
					$value = NULL;
				}
			}
		} else {
			// named/keyed parameters with name=>value
			foreach ($parameters as $name => $value) {
				$this->$name = $value;
			}
		}
	}

	/**
	 * @return string
	 */
	public function getPackage() {
		return $this->package;
	}

	/**
	 * @param string $package
	 * @return void
	 */
	public function setPackage($package) {
		$this->package = $package;
	}

	/**
	 * @return string
	 */
	public function getCounter() {
		return $this->counter;
	}

	/**
	 * @param string $counter
	 * @return void
	 */
	public function setCounter($counter) {
		$this->counter = $counter;
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @param string $action
	 * @return void
	 */
	public function setAction($action) {
		$this->action = $action;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * @return NULL|string
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * @param NULL|string $from
	 * @return void
	 */
	public function setFrom($from) {
		$this->from = $from;
	}

	/**
	 * @return NULL|string
	 */
	public function getTo() {
		return $this->to;
	}

	/**
	 * @param NULL|string $to
	 * @return void
	 */
	public function setTo($to) {
		$this->to = $to;
	}

	/**
	 * @return int|NULL
	 */
	public function getCount() {
		return $this->count;
	}

	/**
	 * @param int|NULL $count
	 * @return void
	 */
	public function setCount($count) {
		$this->count = $count;
	}

	/**
	 * @return string
	 */
	public function toQueryString() {
		return 'package=' . $this->getPackage() .
			'&counter=' . $this->getCounter() .
			'&count=' . $this->getCount() .
			'&from=' . $this->getFrom() .
			'&to=' . $this->getTo() .
			'&value=' . $this->getValue()
		;
	}

}
