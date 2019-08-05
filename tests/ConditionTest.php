<?php

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class ConditionTest extends TestCase
{
    protected $_db;

    public function testExpressiveConditionObject()
    {
        $object = \Bego\Condition::attribute('Year');

        $this->assertInstanceOf(
            Component\Condition::class, $object
        );
    }
}
