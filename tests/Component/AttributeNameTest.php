<?php
namespace Component;

use PHPUnit\Framework\TestCase;
use Bego;

/**
 * @covers \Bego\Query
 */
class AttributeNameTest extends TestCase
{
    protected $_db;

    public function testPlaceholder()
    {
        $attribute = new Bego\Component\AttributeName('Year');

        $this->assertEquals(
            ':Year', $attribute->placeholder()
        );
    }

    public function testPlaceholderWithPrefix()
    {
        $attribute = new Bego\Component\AttributeName('Year');

        $this->assertEquals(
            ':PreYear', $attribute->placeholder('Pre')
        );
    }

    public function testKey()
    {
        $attribute = new Bego\Component\AttributeName('Year');

        $this->assertEquals(
            '#Year', $attribute->key()
        );
    }
}
