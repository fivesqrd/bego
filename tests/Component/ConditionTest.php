<?php
namespace Component;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class ConditionTest extends TestCase
{
    protected $_condition;

    public function setUp()
    {
        $this->_condition = new Component\Condition(
            new Component\AttributeName('Year')
        );
    }

    public function testComperatorObject()
    {
        $object = $this->_condition->eq('1967');

        $this->assertInstanceOf(
            Component\Condition\Comperator::class, $object
        );
    }

    public function testAttributeExists()
    {
        $object = $this->_condition->exists();

        $this->assertInstanceOf(
            Component\Condition\AttributeExists::class, $object
        );
    }

    public function testAttributeNotExists()
    {
        $object = $this->_condition->exists(false);

        $this->assertInstanceOf(
            Component\Condition\AttributeNotExists::class, $object
        );
    }

    public function testContains()
    {
        $object = $this->_condition->contains('123');

        $this->assertInstanceOf(
            Component\Condition\Contains::class, $object
        );
    }

    public function testNotContains()
    {
        $object = $this->_condition->notContains('123');

        $this->assertInstanceOf(
            Component\Condition\NotContains::class, $object
        );
    }

    public function testIn()
    {
        $object = $this->_condition->in(['123', '456']);

        $this->assertInstanceOf(
            Component\Condition\In::class, $object
        );
    }
}
