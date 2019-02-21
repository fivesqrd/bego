<?php
namespace Component\Condition;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class AttributeNotExistsTest extends TestCase
{
    protected $_db;

    public function testStatement()
    {
        $object = new Component\Condition\AttributeNotExists(
            new Component\AttributeName('Year')
        );

        $this->assertEquals(
            "attribute_not_exists(#Year)", $object->statement()
        );
    }

    public function testNames()
    {
        $object = new Component\Condition\AttributeNotExists(
            new Component\AttributeName('Year')
        );

        $this->assertEquals(
            ['#Year' => 'Year'], $object->name()
        );
    }
}
