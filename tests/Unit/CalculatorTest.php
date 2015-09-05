<?php
namespace NamelessCoder\Numerolog\Tests\Unit;
use NamelessCoder\Numerolog\Calculator;

/**
 * Class CalculatorTest
 */
class CalculatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @param float $value
	 * @param boolean $expected
	 * @dataProvider getIsSignedTestValues
	 */
	public function testIsSigned($value, $expected) {
		$calculator = new Calculator();
		$this->assertEquals($expected, $calculator->isSigned($value));
	}

	/**
	 * @return array
	 */
	public function getIsSignedTestValues() {
		return array(
			array(1, FALSE),
			array(-1, TRUE),
			array(0.514, FALSE),
			array(-0.132, TRUE)
		);
	}

	/**
	 * @param float $value
	 * @param mixed $modification
	 * @param float $expected
	 * @dataProvider getModifyTestValues
	 */
	public function testModify($value, $modification, $expected) {
		$calculator = new Calculator();
		$this->assertEquals($expected, $calculator->modify($value, $modification));
	}

	/**
	 * @return array
	 */
	public function getModifyTestValues() {
		return array(
			array(1, 2, 2),
			array(1, '+1', 2),
			array(2, -1, 1)
		);
	}

	/**
	 * @param array $values
	 * @param array $expected
	 * @dataProvider getStatisticsTestValues
	 */
	public function testStatistics(array $values, array $expected) {
		$calculator = new Calculator();
		$this->assertEquals($expected, $calculator->statistics($values), 'Failed asserting expected statistics match');
	}

	/**
	 * @return array
	 */
	public function getStatisticsTestValues() {
		return array(
			array(
				array(
					array('value' => 1),
					array('value' => 2),
					array('value' => 3),
					array('value' => 4),
					array('value' => 5)
				),
				array(
					'min' => 1,
					'max' => 5,
					'sum' => 15,
					'average' => 3,
					'count' => 5,
					'deviation' => 0.59460355750136051
				)
			),
			array(
				array(
					array('value' => 0.4),
					array('value' => 1.2),
					array('value' => 2.4),
					array('value' => 3.8),
					array('value' => 0.5)
				),
				array(
					'min' => 0.4,
					'max' => 3.8,
					'sum' => 8.3,
					'average' => 1.6600000000000001,
					'count' => 5,
					'deviation' => '0.60474661715316347'
				)
			),

		);
	}

}
