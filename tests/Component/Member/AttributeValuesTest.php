<?php
namespace Component\Member;

use PHPUnit\Framework\TestCase;
use Bego\Component;
use Aws\DynamoDb;
use Bego;

/**
 * @covers \Bego\Query
 */
class AttributeValuesTest extends TestCase
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

    public function testAllEmptyAction()
    {
        $expressions = [
            new Component\Member\FilterExpression([]),
            new Component\Member\ProjectionExpression([]),
        ];

        $object = new Component\Member\AttributeValues(
            $this->_db->marshaler(), $expressions
        );

        $this->assertFalse($object->isDefined());
    }

    public function testProjectionIsEmptyAction()
    {

        $projection = new Component\Member\ProjectionExpression([
            new Component\AttributeName('TestKey')
        ]);

        $object = new Component\Member\AttributeValues(
            $this->_db->marshaler(), [$projection]
        );

        $this->assertFalse($object->isDefined());
    }

    public function testNotEmptyAction()
    {
        $filter = new Component\Member\FilterExpression([
            new Component\Condition\Comperator(
                new Component\AttributeName('Artist'), '=', 'John'
            )
        ]);

        $object = new Component\Member\AttributeValues(
            $this->_db->marshaler(), [$filter]
        );

        $this->assertTrue($object->isDefined());
    }
}
