<?php
namespace Query;

use PHPUnit\Framework\TestCase;
use Bego\Scan;
use Aws\DynamoDb;

/**
 * @covers \Bego\Query
 */
class ConduitTest extends TestCase
{
    protected $_db;

    protected function setUp()
    {
        $config = [
            'version' => 'latest',
            'region'  => 'eu-west-1',
            'credentials' => [
                'key'    => 'test',
                'secret' => 'test',
            ],
            'endpoint' => 'http://localhost:8000'
        ];

        $this->_db = new \Bego\Database(
            new DynamoDb\DynamoDbClient($config), new DynamoDb\Marshaler()
        );
    }

    public function testMissingKeyConditionError()
    {
        $object = new Scan\Conduit($this->_db, ['Limit' => 100]);

        $this->assertEquals(
            100, $object->option('Limit')
        );
    }
}
