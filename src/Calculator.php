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
		$set = array();
		foreach ($values as $unit) {
			$value = (float) $unit['value'];
			$set[] = $value;
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
		$data['deviation'] = $this->calculateStandardDeviationOfSet($set, $data['average']);
		return $data;
	}

	/**
	 * @param array $values
	 * @param float $value
	 * @return float
	 */
	public function variance(array $values, $value) {
		$statistics = $this->statistics($values);
		return ($value - $statistics['mean']);
	}

	/**
	 * @param array $values
	 * @param float $mean
	 * @return float
	 */
	protected function calculateStandardDeviationOfSet(array $values, $mean) {
		$deviation = 0;
		// $deviations initalized -1 for Bessel's correction; sample standard deviation
		$deviations = -1;
		foreach ($values as $value) {
			$valueDeviation = ($value - $mean);
			if ($valueDeviation > 0) {
				$deviation =+ sqrt($valueDeviation);
			}
			++ $deviations;
		}
		return sqrt($deviation / $deviations);
	}

}
