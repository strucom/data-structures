<?php

declare(strict_types=1);

namespace Strucom\Tests\DataStructures;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Strucom\DataStructures\ArrayContainer;
use Strucom\Exception\NotFoundException;

final class ArrayContainerTest extends TestCase
{
    private ArrayContainer $container;

    protected function setUp(): void
    {
        $this->container = new ArrayContainer();
    }

    public function testConstructorWithValidElements(): void
    {
        $elements = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $container = new ArrayContainer($elements);

        $this->assertSame($elements, $container->dump());
    }

    public function testConstructorWithKeysContainingWhitespace(): void
    {
        $elements = [
            '  key1  ' => 'value1',
            "\tkey2\t" => 'value2',
            "\nkey3\n" => 'value3',
            ' key4 ' => 'value4',
            'key5' => 'value5',
        ];

        $expected = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4',
            'key5' => 'value5',
        ];

        $container = new ArrayContainer($elements);

        $this->assertSame($expected, $container->dump());
    }

    public function testConstructorWithInvalidKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All keys must be non-empty strings.');

        new ArrayContainer([
            '' => 'value1',
            '  ' => 'value2',
        ]);
    }

    public function testConstructorWithDuplicateKeysAfterTrimming(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Keys must be unique after trimming.');

        new ArrayContainer([
            ' key1 ' => 'value1',
            'key1' => 'value2',
        ]);
    }

    public function testSetValidKey(): void
    {
        $this->container->set('key1', 'value1');
        $this->assertSame('value1', $this->container->get('key1'));
    }

    public function testSetTrimsKey(): void
    {
        $this->container->set(' key1 ', 'value1');
        $this->assertSame('value1', $this->container->get('key1'));
    }

    public function testSetWithEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID must be a non-empty string.');

        $this->container->set('   ', 'value1');
    }

    public function testAddValidKey(): void
    {
        $this->container->add('key1', 'value1');
        $this->assertSame('value1', $this->container->get('key1'));
    }

    public function testAddThrowsExceptionIfKeyExists(): void
    {
        $this->container->add('key1', 'value1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ID "key1" already exists in the container.');

        $this->container->add('key1', 'value2');
    }

    public function testUnsetRemovesKey(): void
    {
        $this->container->set('key1', 'value1');
        $this->container->unset('key1');

        $this->assertFalse($this->container->has('key1'));
    }

    public function testUnsetDoesNotThrowIfKeyDoesNotExist(): void
    {
        $this->container->unset('nonexistent_key');
        $this->assertFalse($this->container->has('nonexistent_key'));
    }

    public function testDumpReturnsAllElements(): void
    {
        $elements = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $this->container = new ArrayContainer($elements);

        $this->assertSame($elements, $this->container->dump());
    }

    public function testGetThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('nonexistent_key not found.');

        $this->container->get('nonexistent_key');
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        $this->container->set('key1', 'value1');
        $this->assertTrue($this->container->has('key1'));
    }

    public function testHasReturnsFalseForNonexistentKey(): void
    {
        $this->assertFalse($this->container->has('nonexistent_key'));
    }
}
