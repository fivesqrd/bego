<?php
namespace Component\Condition;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class ContainsTest extends TestCase
{
    protected $_db;

    public function testStatement()
    {
        $object = new Component\Condition\Contains(
            new Component\AttributeName('Year'), '19'
        );

        $this->assertEquals(
            "contains(#Year, :CoYear)", $object->statement()
        );
    }

    public function testExpressiveStatement()
    {
        $object = \Bego\Condition::attribute('Year')->contains('19');

        $this->assertEquals(
            "contains(#Year, :CoYear)", $object->statement()
        );
    }

    public function testNames()
    {
        $object = new Component\Condition\Contains(
            new Component\AttributeName('Year'), '19'
        );

        $this->assertEquals(
            ['#Year' => 'Year'], $object->name()
        );
    }
}
