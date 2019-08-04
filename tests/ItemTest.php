<?php

use PHPUnit\Framework\TestCase;

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

    public function testAttributeConstructor()
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

    public function testAttributeUpdate()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->set('Artist', 'Bob Dylan');

        $this->assertEquals(
            'Bob Dylan', $item->attribute('Artist')
        );
    }

    public function testMagicAttributeUpdate()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->Artist = 'Bob Dylan';

        $this->assertEquals(
            'Bob Dylan', $item->attribute('Artist')
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

    public function testMagicIsSet()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $this->assertTrue(isset($item->Artist));
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

    public function testPingIsNotSet()
    {
        $this->expectException(\Bego\Exception::class);

        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->ping('Year');
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

    public function testMagicUnset()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        unset($item->SongTitle);

        $this->assertEquals(
            ['Id' => 1, 'Artist' => 'John Lennon'], $item->attributes()
        );
    }

}
