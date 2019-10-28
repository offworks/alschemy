<?php

namespace Alschemy\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class LongtextType extends BaseType
{
    public static function name()
    {
        return 'longtext';
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param mixed[] $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // TODO: Implement getSQLDeclaration() method.
    }
}