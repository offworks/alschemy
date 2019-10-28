<?php

namespace Alschemy\Types;

use Doctrine\DBAL\Types\Type;

abstract class BaseType extends Type
{
    public abstract static function name();

    public function getName()
    {
        return static::name();
    }
}