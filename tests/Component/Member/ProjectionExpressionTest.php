<?php
namespace Component\Member;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class ProjectionExpressionTest extends TestCase
{
    protected $_db;

    public function testReservedKeywordNames()
    {
        $projection = new Component\Member\ProjectionExpression([
            new Component\AttributeName('Year')
        ]);

        $this->assertEquals(
            ['#Year' => 'Year'], $projection->names()
        );
    }

    public function testMultipleAttributeNames()
    {
        $projection = new Component\Member\ProjectionExpression([
            new Component\AttributeName('Year'),
            new Component\AttributeName('Artist'),
        ]);

        $this->assertEquals(
            ['#Year' => 'Year', '#Artist' => 'Artist'], $projection->names()
        );
    }

    public function testMultipleAttributesStatement()
    {
        $projection = new Component\Member\ProjectionExpression([
            new Component\AttributeName('Year'),
            new Component\AttributeName('Artist'),
        ]);

        $this->assertEquals(
            '#Year, #Artist', $projection->statement()
        );
    }

}
