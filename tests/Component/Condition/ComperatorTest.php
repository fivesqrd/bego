<?php
namespace Component\Condition;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class ComperatorTest extends TestCase
{
    protected $_db;

    public function testEqualsStatement()
    {
        $comperator = new Component\Condition\Comperator(
            new Component\AttributeName('Year'), '=', '1967'
        );

        $placeholder = Component\Condition\Comperator::PREFIX . 'Year';

        $this->assertEquals(
            "#Year = :{$placeholder}", $comperator->statement()
        );
    }

    public function testEqualsExpressiveStatement()
    {
        $comperator = \Bego\Condition::attribute('Year')->eq('1967');

        $placeholder = Component\Condition\Comperator::PREFIX . 'Year';

        $this->assertEquals(
            "#Year = :{$placeholder}", $comperator->statement()
        );
    }

    public function testValues()
    {
        $comperator = new Component\Condition\Comperator(
            new Component\AttributeName('Year'), '=', '1967'
        );

        $placeholder = ':' . Component\Condition\Comperator::PREFIX . 'Year';

        $this->assertEquals(
            [$placeholder => 1967], $comperator->values()
        );
    }
}
