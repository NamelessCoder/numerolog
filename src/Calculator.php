<?php
namespace NamelessCoder\Numerolog;

/**
 * Class Calculator
 */
class Calculator {

	/**
	 * @param float $value
	 * @return boolean
	 */
	public function isSigned($value) {
		return in_array(substr((string) $value, 0, 1), array('+', '-'));
	}

	/**
	 * @param float $value
	 * @param string $modification
	 * @return float
	 */
	public function modify($value, $modification) {
		if ($this->isSigned($modification)) {
			return $value + $modification;
		}
		return $modification;
	}

	/**
	 * @param array $values
	 * @return array
	 */
	public function statistics(array $values) {
		$data = array(
			'average' => 0,
			'sum' => 0,
			'min' => NULL,
			'max' => 0
		);
		foreach ($values as $set) {
			$value = $set['value'];
			$data['sum'] += $value;
			if ($data['min'] === NULL || $value < $data['min']) {
				$data['min'] = $value;
			}
			if ($value > $data['max']) {
				$data['max'] = $value;
			}
		}
		$results = count($values);
		$data['average'] = $data['sum'] / $results;
		$data['count'] = $results;
		return $data;
	}

}
