<?php

use PHPUnit\Framework\TestCase;
use Bego;
use Bego\Component;
use Aws\DynamoDb;

/**
 * @covers \Bego\Query
 */
class ItemTest extends TestCase
{
    protected $_db;

    public function testEmpty()
    {
        $item = new Bego\Item([]);

        $this->assertTrue($item->isEmpty());
    }

    public function testNotEmpty()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $this->assertFalse($item->isEmpty());
    }

    public function testAttribute()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $this->assertEquals(
            'John Lennon', $item->attribute('Artist')
        );
    }

    public function testIsSet()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $this->assertTrue($item->isSet('Artist'));
    }

    public function testIsNotSet()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $this->assertFalse($item->isSet('Year'));
    }

    public function testRemove()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->remove('SongTitle');

        $this->assertEquals(
            ['Id' => 1, 'Artist' => 'John Lennon'], $item->attributes()
        );
    }

}
