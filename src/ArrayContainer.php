<?php

namespace Strucom\DataStructures;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Strucom\Exception\NotFoundException;
/**
 * A simple container implementation based on an internal array. It adheres to the PSR-11 ContainerInterface.
 *
 * Keys are strings. Note: Keys are trimmed automatically in the methods construct, set and add.
 *
 * @since PHP 7.4
 * @author af
 */
class ArrayContainer implements ContainerInterface
{
    /**
     * Constructs the ConfigContainer with an optional array of elements.
     *
     * Validates that all keys in the provided array are non-empty strings,
     * trims them, and ensures they are unique.
     *
     * @param array $elements An optional array of elements to initialize the container.
     *
     * @throws InvalidArgumentException If keys are not non-empty strings or are not unique after trimming.
     *
     * @since PHP 7.4
     */
    public function __construct(protected array $elements = [])
    {
        // Validate keys
        $keys = array_keys($this->elements);

        // Ensure all keys are non-empty strings
        if (!array_all($keys, fn($key) => is_string($key) && trim($key) !== '')) {
            throw new InvalidArgumentException('All keys must be non-empty strings.');
        }

        // Trim keys and ensure uniqueness
        $trimmedKeys = array_map('trim', $keys);
        if (count($trimmedKeys) !== count(array_unique($trimmedKeys))) {
            throw new InvalidArgumentException('Keys must be unique after trimming.');
        }

        // Rebuild the $elements array with trimmed keys
        $this->elements = array_combine($trimmedKeys, array_values($this->elements));
    }

    /**
     * Set a value for a given ID in the container.
     *
     * Validates that the ID is a non-empty string, trims it, and sets the value.
     *
     * @param string $id    The unique identifier for the value.
     * @param mixed  $value The value to store in the container.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the ID is empty or contains only whitespace.
     *
     * @since PHP 7.4
     */
    public function set(string $id, mixed $value): void
    {
        $id = trim($id);

        if ($id === '') {
            throw new InvalidArgumentException('ID must be a non-empty string. Note: IDs are trimmed automatically.');
        }

        $this->elements[$id] = $value;
    }

    /**
     * Add a new value for a given ID in the container.
     *
     * Throws an exception if the ID already exists in the container.
     *
     * @param string $id    The unique identifier for the value.
     * @param mixed  $value The value to store in the container.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the ID is empty or contains only whitespace.
     * @throws RuntimeException If the ID already exists in the container.
     *
     * @since PHP 7.4
     */
    public function add(string $id, mixed $value): void
    {
        $id = trim($id);
        if ($this->has($id)) {
            throw new RuntimeException(sprintf('ID "%s" already exists in the container. Note: IDs are trimmed automatically.', $id));
        }
        $this->set($id, $value);
    }

    /**
     * Unset a value for a given ID in the container.
     *
     * This method removes the value associated with the specified ID from the container.
     *
     * @param string $id The unique identifier for the value to remove.
     *
     * @return void
     *
     * @since PHP 7.4
     */
    public function unset(string $id): void
    {
        unset($this->elements[$id]);
    }

    /**
     * Retrieve all elements in the container.
     *
     * @return array The array of elements in the container.
     *
     * @since PHP 7.0
     */
    public function dump(): array
    {
        return $this->elements;
    }
    
    /**
     * Retrieve all keys in the container.
     *
     * @return array The array of elements in the container.
     *
     * @since PHP 7.0
     */
    public function keys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): mixed
    {
        if (isset($this->elements[$id])) {
            return $this->elements[$id];
        } else {
            throw new NotFoundException($id . ' not found.');
        }
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->elements[$id]);
    }
}
