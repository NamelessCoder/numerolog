<?php
namespace NamelessCoder\Numerolog\Tests\Unit;
use NamelessCoder\Numerolog\Query;

/**
 * Class QueryTest
 */
class QueryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @param string $propertyName
	 * @param mixed $value
	 * @param mixed $expected
	 * @dataProvider getGetterAndSetterTestValues
	 */
	public function testStandardGetter($propertyName, $value, $expected = NULL) {
		if (!$expected) {
			$expected = $value;
		}
		$query = new Query();
		$property = new \ReflectionProperty($query, $propertyName);
		$property->setAccessible(TRUE);
		$property->setValue($query, $value);
		$getter = 'get' . ucfirst($propertyName);
		$this->assertEquals($expected, $query->$getter());
	}

	/**
	 * @param string $propertyName
	 * @param mixed $value
	 * @param mixed $expected
	 * @dataProvider getGetterAndSetterTestValues
	 */
	public function testStandardSetter($propertyName, $value, $expected = NULL) {
		$query = new Query();
		$setter = 'set' . ucfirst($propertyName);
		$query->$setter($value);
		$this->assertAttributeEquals($value, $propertyName, $query);
	}

	/**
	 * @return array
	 */
	public function getGetterAndSetterTestValues() {
		return array(
			'package' => array('package', 'foobar-package'),
			'counter' => array('counter', 'foobar-counter'),
			'value' => array('value', 123),
			'action' => array('action', 'foobar-action'),
			'from' => array('from', '2015-01-01', 1420066800),
			'to' => array('to', '2015-01-02', 1420153200),
			'count' => array('count', 654),
			'token' => array('token', 'foobar-token')
		);
	}

	/**
	 * @param array $parameters
	 * @param array $expected
	 * @dataProvider getConstrutorParameterTestValues
	 */
	public function testConstructorParameters(array $parameters, array $expected) {
		$query = new Query($parameters);
		foreach ($expected as $name => $value) {
			$this->assertAttributeEquals($value, $name, $query);
		}
	}

	/**
	 * @return array
	 */
	public function getConstrutorParameterTestValues() {
		return array(
			'associative' => array(
				array(
					'package' => 'foobar-package',
					'counter' => 'foobar-counter',
					'value' => 123,
					'action' => 'foobar-action',
					'from' => '2015-01-01',
					'to' => '2015-01-02',
					'count' => 321,
					'token' => 'foobar-token'
				),
				array(
					'package' => 'foobar-package',
					'counter' => 'foobar-counter',
					'value' => 123,
					'action' => 'foobar-action',
					'from' => '2015-01-01',
					'to' => '2015-01-02',
					'count' => 321,
					'token' => 'foobar-token'
				)
			),
			'named' => array(
				array(
					'--package', 'foobar-package',
					'--counter', 'foobar-counter',
					'--value', 123,
					'--action', 'foobar-action',
					'--from', '2015-01-01',
					'--to', '2015-01-02',
					'--count', 321,
					'--token', 'foobar-token'
				),
				array(
					'package' => 'foobar-package',
					'counter' => 'foobar-counter',
					'value' => 123,
					'action' => 'foobar-action',
					'from' => '2015-01-01',
					'to' => '2015-01-02',
					'count' => 321,
					'token' => 'foobar-token'
				)
			)
		);
	}

	/**
	 * @test
	 */
	public function testToQueryString() {
		$query = new Query(
			array(
				'package' => 'foobar-package',
				'counter' => 'foobar-counter',
				'value' => 123,
				'action' => 'foobar-action',
				'from' => '2015-01-01',
				'to' => '2015-01-02',
				'count' => 321,
				'token' => 'foobar-token'
			)
		);
		$this->assertEquals(
			'package=foobar-package&counter=foobar-counter&action=foobar-action&count=321&from=1420066800&to=1420153200' .
			'&value=123&token=foobar-token',
			$query->toQueryString()
		);
	}

}
