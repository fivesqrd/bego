<?php
namespace Put;

use PHPUnit\Framework\TestCase;
use Bego;
use Bego\Component;
use Aws\DynamoDb;

/**
 * @covers \Bego\Query
 */
class StatementTest extends TestCase
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

        $this->_db = new Bego\Database(
            new DynamoDb\DynamoDbClient($config), new DynamoDb\Marshaler()
        );

    }

    public function testItemAttributes()
    {
        $attributes = [
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ];

        $statement = new Bego\Put\Statement($this->_db, $attributes);

        $subset = [
            'Item' => [
                'Id'        => ['N' => 1],
                'Artist'    => ['S' => 'John Lennon'],
                'SongTitle' => ['S' => 'How many roads must a man walk down']
            ]
        ];

        $this->assertArraySubset($subset, $statement->compile());
    }

    public function testExpressionAttributeNames()
    {
        $attributes = [
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ];

        $statement = new Bego\Put\Statement($this->_db, $attributes);

        $statement->conditions([
            new Component\Condition\Comperator(
                new Component\AttributeName('Year'), '=', 1967
            ),
        ]);

        $subset = [
            'ExpressionAttributeNames' => ['#Year' => 'Year'],
        ];

        $this->assertArraySubset($subset, $statement->compile());
    }

    public function testExpressionAttributeValues()
    {
        $attributes = [
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ];

        $statement = new Bego\Put\Statement($this->_db, $attributes);

        $statement->conditions([
            new Component\Condition\Comperator(
                new Component\AttributeName('Year'), '=', 1967
            ),
        ]);

        $subset = [
            'ExpressionAttributeValues' => [
                ':CmpYear' => ['N' => 1967]
            ]
        ];

        $this->assertArraySubset($subset, $statement->compile());
    }

    public function testConditionExpressionEquals()
    {
        $attributes = [
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down',
            'Year'      => 1966
        ];

        $statement = new Bego\Put\Statement($this->_db, $attributes);

        $statement->conditions([
            new Component\Condition\Comperator(
                new Component\AttributeName('Year'), '=', 1967
            ),
        ]);

        $this->assertArraySubset(
            ['ConditionExpression' => '#Year = :CmpYear'], $statement->compile()
        );
    }

    public function testTableName()
    {
        $attributes = [
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ];

        $statement = new Bego\Put\Statement($this->_db, $attributes);
        $statement->table('Songs');

        $this->assertArraySubset(
            ['TableName' => 'Songs'], $statement->compile()
        ); 
    }
}
