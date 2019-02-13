<?php
namespace Update;

use PHPUnit\Framework\TestCase;
use Bego;
use Bego\Component;
use Aws\DynamoDb;

/**
 * @covers \Bego\Query
 */
class ActionTest extends TestCase
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

        $action = new Bego\Update\Action($item);

        $this->assertEquals(
            'SET #Artist = :Artist', $action->expression()
        );

        $this->assertEquals(
            ['#Artist' => 'Artist'], $action->attributeNames()
        );

        $this->assertEquals(
            [':Artist' => 'Bob Dylan'], $action->attributeValues()
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

        $action = new Bego\Update\Action($item);

        $this->assertEquals(
            'SET #Artist = :Artist, #SongTitle = :SongTitle', $action->expression()
        );

        $this->assertEquals(
            ['#Artist' => 'Artist', '#SongTitle' => 'SongTitle'], $action->attributeNames()
        );

        $this->assertEquals(
            [':Artist' => 'Bob Dylan', ':SongTitle' => 'The Hurricane'], $action->attributeValues()
        );
    }
}
