<?php
namespace Update;

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

    public function testExpressionNames()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->set('Artist', 'Bob Dylan');

        $statement = new Bego\Update\Statement(
            'Songs', new Bego\Update\Action($item)
        );

        $subset = [
            'ExpressionAttributeNames' => ['#Artist' => 'Artist'],
        ];

        $this->assertArraySubset(
            $subset, $statement->compile($this->_db->marshaler())
        );
    }

    public function testExpressionAttributeValues()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down',
            'Year'      => 1966
        ]);

        $item->set('Artist', 'Bob Dylan');
        $item->set('Year', 1967);

        $statement = new Bego\Update\Statement(
            'Songs', new Bego\Update\Action($item)
        );

        $subset = [
            'ExpressionAttributeValues' => [
                ':Artist'  => ['S' => 'Bob Dylan'],
                ':Year'    => ['N' => 1967]
            ]
        ];

        $this->assertArraySubset(
            $subset, $statement->compile($this->_db->marshaler())
        );
    }

    public function testEqualsUpdateExpression()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->set('Artist', 'Bob Dylan');

        $statement = new Bego\Update\Statement(
            'Songs', new Bego\Update\Action($item)
        );

        $subset = [
            'UpdateExpression' => 'SET #Artist = :Artist', 
        ];

        $this->assertArraySubset(
            $subset, $statement->compile($this->_db->marshaler())
        );
    }

    public function testConditionExpressionEquals()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->set('Artist', 'Bob Dylan');
        $item->set('Year', 1968);

        $statement = new Bego\Update\Statement(
            'Songs', new Bego\Update\Action($item)
        );

        $statement->conditions([
            new Component\Condition\Comperator(
                new Component\AttributeName('Year'), '=', 1967
            ),
        ]);

        $subset = [
            'ExpressionAttributeValues' => [
                ':Artist'  => ['S' => 'Bob Dylan'],
                ':Year'    => ['N' => '1968'],
                ':CmpYear' => ['N' => '1967'],
            ],
            'ConditionExpression' => '#Year = :CmpYear', 
        ];

        $this->assertArraySubset(
            $subset, $statement->compile($this->_db->marshaler())
        );
    }

    public function testKey()
    {
        $item = new Bego\Item([
            'Id'        => '1',
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->set('Artist', 'Bob Dylan');

        $statement = new Bego\Update\Statement(
            'Songs', new Bego\Update\Action($item)
        );
        $statement->key(['Id' => $this->_db->marshaler()->marshalValue('1')]);

        $subset = [
            'Key' => ['Id' => ['S' => '1']], 
        ];

        $this->assertArraySubset(
            $subset, $statement->compile($this->_db->marshaler())
        );
    }

    public function testTableName()
    {
        $item = new Bego\Item([
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ]);

        $item->set('Artist', 'Bob Dylan');

        $statement = new Bego\Update\Statement(
            'Songs', new Bego\Update\Action($item)
        );

        $subset = [
            'TableName' => 'Songs', 
        ];

        $this->assertArraySubset(
            $subset, $statement->compile($this->_db->marshaler())
        );
    }
}
