<?php
namespace Scan;

use PHPUnit\Framework\TestCase;
use Bego\Scan;
use Bego\Condition;
use Aws\DynamoDb;

/**
 * @covers \Bego\Query
 */
class StatementTest extends TestCase
{
    protected $_scan;

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

        $db = new \Bego\Database(
            new DynamoDb\DynamoDbClient($config), new DynamoDb\Marshaler()
        );

        $this->_scan = new Scan\Statement($db);
    }

    public function estFilterExpressionAttributeName()
    {
        $statement = $this->_scan
            ->table('Test')
            ->partition('TestKey')
            ->filter(Condition::comperator('Artist', '=', 'John'))
            ->compile();

        $subset = [
            'ExpressionAttributeNames' => [
                '#Artist'  => 'Artist',
            ],
        ];

        $this->assertArraySubset($subset, $statement);
    }

    public function testFilterEqualsOperator()
    {
        $statement = $this->_scan
            ->table('Test')
            ->filter(Condition::comperator('Artist', '=', 'John'))
            ->filter(Condition::comperator('TestKey', '=', '12345'))
            ->compile();

        $subset = [
            'FilterExpression' => '#Artist = :CmpArtist and #TestKey = :CmpTestKey', 
            'ExpressionAttributeValues' => [
                ':CmpArtist'  => ['S' => 'John'],
                ':CmpTestKey' => ['S' => '12345'],
            ]
        ];

        $this->assertArraySubset($subset, $statement);
    }
}
