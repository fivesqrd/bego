<?php

use PHPUnit\Framework\TestCase;
use Aws\DynamoDb;

class Music extends Bego\Model
{
    /**
     * Table name
     */
    protected $_name = 'Music';

    /**
     * Table's partition key attribute
     */
    protected $_partition = 'Artist';

    protected $_sort = 'SongTitle';

    /**
     * List of indexes available for this table
     */
    protected $_indexes = [
        'My-Global-Index' => ['key' => 'Timestamp'],
        'My-Local-Index' => []
    ];
}

/**
 * @covers \Bego\Query
 */
class TableTest extends TestCase
{
    protected $_db;

    protected function setUp()
    {
        $config = [
            'version' => '2012-08-10',
            'region'  => 'eu-west-1',
            'credentials' => [
                'key'    => 'test',
                'secret' => 'test',
            ],
        ];

        $this->_db = new Bego\Database(
            new DynamoDb\DynamoDbClient($config), new DynamoDb\Marshaler()
        );

    }

    public function testQueryFactory()
    {
        $query = $this->_db->table(new Music())->query();

        $this->assertInstanceOf(
            Bego\Query\Statement::class, $query
        );
    }

    public function testScanFactory()
    {
        $query = $this->_db->table(new Music())->scan();

        $this->assertInstanceOf(
            Bego\Scan\Statement::class, $query
        );
    }
}
