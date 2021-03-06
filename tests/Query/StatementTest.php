<?php
namespace Query;

use PHPUnit\Framework\TestCase;
use Bego\Query;
use Bego\Condition;
use Aws\DynamoDb;

/**
 * @covers \Bego\Query
 */
class StatementTest extends TestCase
{
    protected $_query;

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

        $this->_query = new Query\Statement($db);
    }

    public function testMissingKeyConditionError()
    {
        $this->expectException(\Bego\Exception::class);

        $statement = $this->_query
            ->table('Test')
            ->compile();

        $statement = $query->compile();
    }

    public function testReverseFlagIsTrue()
    {
        $statement = $this->_query
            ->table('Test')
            ->partition('TestKey')
            ->key('12345')
            ->reverse(true)
            ->compile();

        $this->assertArraySubset(
            ['ScanIndexForward' => false], $statement
        );
    }

    public function testReverseFlagIsFalse()
    {
        $statement = $this->_query
            ->table('Test')
            ->partition('TestKey')
            ->key('12345')
            ->reverse(false)
            ->compile();

        $this->assertArraySubset(
            ['ScanIndexForward' => true], $statement
        );
    }

    public function testKeyCondidtionExpression()
    {
        $statement = $this->_query
            ->table('Test')
            ->partition('TestKey')
            ->key('12345')
            ->compile();

        $subset = [
            'KeyConditionExpression' => '#TestKey = :CmpTestKey', 
            'ExpressionAttributeValues' => [
                ':CmpTestKey' => ['S' => '12345'],
            ]
        ];

        $this->assertArraySubset(
            $subset, $statement
        );
    }

    public function testCondidtionExpression()
    {
        $statement = $this->_query
            ->table('Test')
            ->partition('TestKey')
            ->key('12345')
            ->condition(Condition::attribute('Artist')->eq('John'))
            ->compile();

        $subset = [
            'KeyConditionExpression' => '#TestKey = :CmpTestKey and #Artist = :CmpArtist', 
            'ExpressionAttributeValues' => [
                ':CmpTestKey' => ['S' => '12345'],
                ':CmpArtist' => ['S' => 'John'],
            ]
        ];

        $this->assertArraySubset(
            $subset, $statement
        );
    }

    public function testCondidtionExpressionWithAnd()
    {
        $statement = $this->_query
            ->table('Test')
            ->partition('TestKey')
            ->key('12345')
            ->condition(Condition::comperator('Artist', '=', 'John'))
            ->compile();

        $subset = [
            'KeyConditionExpression' => '#TestKey = :CmpTestKey and #Artist = :CmpArtist', 
            'ExpressionAttributeValues' => [
                ':CmpTestKey' => ['S' => '12345'],
                ':CmpArtist' => ['S' => 'John'],
            ]
        ];

        $this->assertArraySubset(
            $subset, $statement
        );
    }

    public function testFilterExpressionAttributeName()
    {
        $statement = $this->_query
            ->table('Test')
            ->partition('TestKey')
            ->key('12345')
            ->filter(Condition::comperator('Artist', '=', 'John'))
            ->compile();

        $subset = [
            'ExpressionAttributeNames' => [
                '#Artist'  => 'Artist',
                '#TestKey' => 'TestKey'
            ],
        ];

        $this->assertArraySubset(
            $subset, $statement
        );
    }

    public function testFilterEqualsOperator()
    {
        $statement = $this->_query
            ->table('Test')
            ->partition('TestKey')
            ->key('12345')
            ->filter(Condition::comperator('Artist', '=', 'John'))
            ->compile();

        $subset = [
            'FilterExpression' => '#Artist = :CmpArtist', 
            'ExpressionAttributeValues' => [
                ':CmpArtist'  => ['S' => 'John'],
                ':CmpTestKey' => ['S' => '12345'],
            ]
        ];

        $this->assertArraySubset(
            $subset, $statement
        );
    }

    public function testProjectionMatches()
    {
        $statement = $this->_query
            ->table('Test')
            ->partition('TestKey')
            ->key('12345')
            ->projection(['TestKey', 'Artist'])
            ->reverse(false)
            ->compile();

        $this->assertArraySubset(
            ['ProjectionExpression' => '#TestKey, #Artist'], $statement
        );
    }
}
