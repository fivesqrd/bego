<?php
namespace Component\Condition;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class NotContainsTest extends TestCase
{
    protected $_db;

    public function testStatement()
    {
        $object = new Component\Condition\NotContains(
            new Component\AttributeName('Year'), '19'
        );

        $this->assertEquals(
            "not(contains(#Year, :NcoYear))", $object->statement()
        );
    }

    public function testExpressiveStatement()
    {
        $object = \Bego\Condition::attribute('Year')->notContains('19');

        $this->assertEquals(
            "not(contains(#Year, :NcoYear))", $object->statement()
        );
    }

    public function testNames()
    {
        $object = new Component\Condition\NotContains(
            new Component\AttributeName('Year'), '19'
        );

        $this->assertEquals(
            ['#Year' => 'Year'], $object->name()
        );
    }
}
