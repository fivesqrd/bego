<?php
namespace Component\Member;

use PHPUnit\Framework\TestCase;
use Bego\Component;

/**
 * @covers \Bego\Query
 */
class KeyConditionExpressionTest extends TestCase
{
    protected $_db;

    public function testReservedKeywordNames()
    {
        $filter = new Component\Member\KeyConditionExpression([
            new Component\Condition\Comperator(
                new Component\AttributeName('Year'), '=', '1966'
            )
        ]);

        $this->assertEquals(
            ['#Year' => 'Year'], $filter->names()
        );
    }

    public function testMultipleConditionNames()
    {
        $filter = new Component\Member\KeyConditionExpression([
            new Component\Condition\Comperator(new Component\AttributeName('Year'), '>', '1966'),
            new Component\Condition\Comperator(new Component\AttributeName('Artist'), '=', 'John Lennon'),
        ]);

        $this->assertEquals(
            ['#Year' => 'Year', '#Artist' => 'Artist'], $filter->names()
        );
    }

    public function testMultipleConditionValues()
    {
        $filter = new Component\Member\KeyConditionExpression([
            new Component\Condition\Comperator(new Component\AttributeName('Year'), '>', '1966'),
            new Component\Condition\Comperator(new Component\AttributeName('Artist'), '=', 'John Lennon'),
        ]);

        $this->assertEquals(
            [':CmpYear' => '1966', ':CmpArtist' => 'John Lennon'], $filter->values()
        );
    }

    public function testMultipleConditionStatement()
    {
        $filter = new Component\Member\KeyConditionExpression([
            new Component\Condition\Comperator(new Component\AttributeName('Year'), '>', '1966'),
            new Component\Condition\Comperator(new Component\AttributeName('Artist'), '=', 'John Lennon'),
        ]);

        $this->assertEquals(
            '#Year > :CmpYear and #Artist = :CmpArtist', $filter->statement()
        );
    }
}
