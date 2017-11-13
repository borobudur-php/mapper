<?php
/**
 * This file is part of the Borobudur package.
 *
 * (c) 2017 Borobudur <http://borobudur.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Borobudur\Component\Mapper;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class ObjectMapper implements MapperInterface
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var array
     */
    private $excludes;

    /**
     * @var array
     */
    private $only;

    /**
     * @var ReflectionClass
     */
    private $class;

    /**
     * {@inheritdoc}
     */
    public function excludes(array $fields): MapperInterface
    {
        $this->excludes = $fields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function only(array $fields): MapperInterface
    {
        $this->only = $fields;

        return $this;
    }

    /**
     * Fill object from object
     *
     * @param object $data
     *
     * @return object
     */
    public function fill($data)
    {
        if (!$this->object) {
            throw new InvalidArgumentException(
                'There are no object to be mapped'
            );
        }

        if (!is_object($data)) {
            throw new InvalidArgumentException('Data should be an object');
        }

        $data = $this->extractData($data);
        $allowed = $this->extractAllowedAttributes($this->class);

        foreach ($this->class->getProperties() as $property) {
            $name = $property->getName();
            if (!in_array($name, $allowed, true)
                || !array_key_exists(
                    $name,
                    $data
                )
            ) {
                continue;
            }

            $property->setAccessible(true);
            $property->setValue($this->object, $data[$name]);
        }

        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function map($object): MapperInterface
    {
        if (!$object && !is_object($object)) {
            throw new InvalidArgumentException('There is object to be mapped');
        }

        $this->object = $object;
        $this->class = new ReflectionClass(get_class($object));

        return $this;
    }

    /**
     * Extract allowed attributes.
     *
     * @param ReflectionClass $reflection
     *
     * @return array
     */
    private function extractAllowedAttributes(ReflectionClass $reflection): array
    {
        $allowed = [];
        foreach (
            $reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method
        ) {
            if (!$this->isGetMethod($method)) {
                continue;
            }

            $name = $this->getAttributeName($method);
            if ($this->isExclude($name)) {
                continue;
            }

            $allowed[] = $name;
        }

        return $allowed;
    }

    /**
     * Check whether current key is excluded.
     *
     * @param string $key
     *
     * @return bool
     */
    private function isExclude(string $key): bool
    {
        if (!empty($this->excludes) && in_array($key, $this->excludes, true)) {
            return true;
        }

        if (!empty($this->only) && !in_array($key, $this->only, true)) {
            return true;
        }

        return false;
    }

    /**
     * Extract the object data to array.
     *
     * @param object $data
     *
     * @return array
     */
    private function extractData(object $data): array
    {
        if ($data instanceof \stdClass) {
            return (array) $data;
        }

        $extracted = [];
        $reflection = new \ReflectionObject($data);
        foreach (
            $reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method
        ) {
            if (!$this->isGetMethod($method)) {
                continue;
            }

            $extracted[$this->getAttributeName($method)] = $method->invoke(
                $data
            );
        }

        return $extracted;
    }

    /**
     * Check the method is getter.
     *
     * @param ReflectionMethod $method
     *
     * @return bool
     */
    private function isGetMethod(ReflectionMethod $method): bool
    {
        $methodLength = strlen($method->name);

        return
            !$method->isStatic()
            && (
                ((0 === strpos($method->name, 'get') && 3 < $methodLength)
                    || (0 === strpos($method->name, 'is')
                        && 2 < $methodLength))
                && 0 === $method->getNumberOfRequiredParameters()
            );
    }

    /**
     * Get the attribute name from method.
     *
     * @param ReflectionMethod $method
     *
     * @return string
     */
    private function getAttributeName(ReflectionMethod $method): string
    {
        return lcfirst(
            substr($method->name, 0 === strpos($method->name, 'is') ? 2 : 3)
        );
    }
}
