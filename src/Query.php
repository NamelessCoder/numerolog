<?php
namespace NamelessCoder\Numerolog;

/**
 * Class Query
 */
class Query {

	const ACTION_SAVE = 'save';
	const ACTION_GET = 'get';
	const ACTION_POLL = 'poll';
	const ACTION_COMPARE = 'compare';
	const ACTION_COUNTERS = 'counters';

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
	 * @var string|NULL
	 */
	protected $poll;

	/**
	 * @var string|NULL
	 */
	protected $token;

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
	 * @return integer|NULL
	 */
	public function getFrom() {
		return $this->from !== NULL ? strtotime($this->from) : NULL;
	}

	/**
	 * @param NULL|string $from
	 * @return void
	 */
	public function setFrom($from) {
		$this->from = $from;
	}

	/**
	 * @return integer|NULL
	 */
	public function getTo() {
		return $this->to !== NULL ? strtotime($this->to) : NULL;
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
	 * @return string|NULL
	 */
	public function getPoll() {
		return $this->poll;
	}

	/**
	 * @param string|NULL $poll
	 * @return void
	 */
	public function setPoll($poll) {
		$this->poll = $poll;
	}

	/**
	 * @return string
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * @param string $token
	 * @return void
	 */
	public function setToken($token) {
		$this->token = $token;
	}

	/**
	 * @return string
	 */
	public function toQueryString() {
		return 'package=' . $this->getPackage() .
			'&counter=' . $this->getCounter() .
			'&action=' . $this->getAction() .
			'&count=' . $this->getCount() .
			'&from=' . $this->getFrom() .
			'&to=' . $this->getTo() .
			'&poll=' . $this->getPoll() .
			'&value=' . urlencode($this->getValue()) .
			'&token=' . $this->getToken()
		;
	}

}
