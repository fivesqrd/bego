<?php
namespace Component\Condition;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class BeginsWithTest extends TestCase
{
    protected $_db;

    public function testStatement()
    {
        $object = new Component\Condition\BeginsWith(
            new Component\AttributeName('Year'), '19'
        );

        $this->assertEquals(
            "begins_with(#Year, :BwYear)", $object->statement()
        );
    }

    public function testExpressiveStatement()
    {
        $object = \Bego\Condition::attribute('Year')->beginsWith('19');

        $this->assertEquals(
            "begins_with(#Year, :BwYear)", $object->statement()
        );
    }

    public function testNames()
    {
        $object = new Component\Condition\BeginsWith(
            new Component\AttributeName('Year'), '19'
        );

        $this->assertEquals(
            ['#Year' => 'Year'], $object->name()
        );
    }
}
