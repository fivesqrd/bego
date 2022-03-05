<?php
namespace Batch;

use PHPUnit\Framework\TestCase;
use Bego;
use Bego\Component;
use Aws\DynamoDb;

/**
 * @covers \Bego\Query
 */
class WriteStatementTest extends TestCase
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

    public function testPutRequest()
    {
        $attributes = [
            'Id'        => 1,
            'Artist'    => 'John Lennon',
            'SongTitle' => 'How many roads must a man walk down'
        ];

        $statement = new Bego\Batch\WriteStatement($this->_db);
        $statement->put('Music', $attributes);
        $statement->put('Music', $attributes);

        $subset = [
            'RequestItems' => [
                'Music' => [
                    [
                        'PutRequest' => [
                            'Item' => [
                                'Id'        => ['N' => 1],
                                'Artist'    => ['S' => 'John Lennon'],
                                'SongTitle' => ['S' => 'How many roads must a man walk down']
                            ],
                        ],
                    ],
                    [
                        'PutRequest' => [
                            'Item' => [
                                'Id'        => ['N' => 1],
                                'Artist'    => ['S' => 'John Lennon'],
                                'SongTitle' => ['S' => 'How many roads must a man walk down']
                            ],
                        ]
                    ]
                ]
            ],
            'ReturnConsumedCapacity' => 'NONE',
            'ReturnItemCollectionMetrics' => 'SIZE'
        ];

        $this->assertEquals($subset, $statement->compile());
    }

    public function testDeleteRequest()
    {
        $key = [
            'Artist'    => ['S' => 'John Lennon'],
            'SongTitle' => ['S' => 'How many roads must a man walk down']
        ];

        $statement = new Bego\Batch\WriteStatement($this->_db);
        $statement->delete('Music', $key);

        $subset = [
            'RequestItems' => [
                'Music' => [
                    [
                        'DeleteRequest' => [
                            'Key' => [
                                'Artist'    => ['S' => 'John Lennon'],
                                'SongTitle' => ['S' => 'How many roads must a man walk down']
                            ],
                        ],
                    ],
                ]
            ],
            'ReturnConsumedCapacity' => 'NONE',
            'ReturnItemCollectionMetrics' => 'SIZE'
        ];

        $this->assertEquals($subset, $statement->compile());
    }
}
