<?php
namespace Component\Condition;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class InTest extends TestCase
{
    protected $_db;

    public function testStatement()
    {
        $comperator = new Component\Condition\In(
            new Component\AttributeName('Year'), ['1967', '1968']
        );

        $placeholder = Component\Condition\In::PREFIX . 'Year';

        $this->assertEquals(
            "#Year in (:{$placeholder}0, :{$placeholder}1)", $comperator->statement()
        );
    }

    public function testValues()
    {
        $comperator = new Component\Condition\In(
            new Component\AttributeName('Year'), ['1967', '1968']
        );

        $placeholder = ':' . Component\Condition\In::PREFIX . 'Year';

        $this->assertEquals(
            [$placeholder . '0' => 1967, $placeholder . '1' => 1968], $comperator->values()
        );
    }
}
