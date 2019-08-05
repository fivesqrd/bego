<?php
namespace Component\Condition;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class AttributeExistsTest extends TestCase
{
    protected $_db;

    public function testStatement()
    {
        $object = new Component\Condition\AttributeExists(
            new Component\AttributeName('Year')
        );

        $this->assertEquals(
            "attribute_exists(#Year)", $object->statement()
        );
    }

    public function testExpressiveStatement()
    {
        $object = \Bego\Condition::attribute('Year')->exists();

        $this->assertEquals(
            "attribute_exists(#Year)", $object->statement()
        );
    }

    public function testNames()
    {
        $object = new Component\Condition\AttributeExists(
            new Component\AttributeName('Year')
        );

        $this->assertEquals(
            ['#Year' => 'Year'], $object->name()
        );
    }
}
