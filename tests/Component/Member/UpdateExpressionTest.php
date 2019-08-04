<?php
namespace Component\Member;

use PHPUnit\Framework\TestCase;
use Bego;
use Bego\Component\Member\UpdateExpression;
use Aws\DynamoDb;

/**
 * @covers \Bego\Query
 */
class UpdateExpressionTest extends TestCase
{
    protected $_db;

    public function testOneSetAction()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->set('Artist', 'Bob Dylan');

        $action = new UpdateExpression($item);

        $this->assertEquals(
            'SET #Artist = :Artist', $action->statement()
        );

        $this->assertEquals(
            ['#Artist' => 'Artist'], $action->names()
        );

        $this->assertEquals(
            [':Artist' => 'Bob Dylan'], $action->values()
        );
    }

    public function testMultipleSetActions()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->set('Artist', 'Bob Dylan');
        $item->set('SongTitle', 'The Hurricane');

        $action = new UpdateExpression($item);

        $this->assertEquals(
            'SET #Artist = :Artist, #SongTitle = :SongTitle', $action->statement()
        );

        $this->assertEquals(
            ['#Artist' => 'Artist', '#SongTitle' => 'SongTitle'], $action->names()
        );

        $this->assertEquals(
            [':Artist' => 'Bob Dylan', ':SongTitle' => 'The Hurricane'], $action->values()
        );
    }
}
