<?php

namespace Alschemy;

use Alschemy\Types\LongtextType;
use Alschemy\Types\TimestampType;
use Alschemy\Types\TinyintType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TypeMap
{
    public static $types = [
        TinyintType::class,
        TimestampType::class,
        LongtextType::class
    ];

    public $map = [];

    protected static $instance;

    protected function __construct(array $classes)
    {
        foreach ($classes as $class) {
            if (!Type::hasType($class::name())) {
                Type::addType($class::name(), $class);
            }

            $this->map[$class::name()] = $class;
        }
    }

    /**
     * @return static
     */
    public static function instance()
    {
        if (!static::$instance) {
            static::$instance = new static(static::$types);
        }

        return static::$instance;
    }

    /**
     * The main entry.
     *
     * @param $type
     * @return Type
     * @throws \Exception
     */
    public static function getType($type)
    {
        // register
        static::instance();

        if (!Type::hasType($type))
            throw new \Exception('Unknown type ' . $type . '. Please make a type that extends Alschemy\Types\BaseType');

        return Type::getType($type);
    }

    public function getClass($name)
    {
        if (!isset($this->map[$name]))
            throw new \Exception('Unknown type ' . $name . '. Please make a type that extends Alschemy\Types\BaseType');

        return $this->map[$name];
    }
}