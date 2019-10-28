<?php

namespace Alschemy\Contracts;

use Doctrine\DBAL\Schema\Schema;

interface VersionReader
{
    /**
     * Output the schema of whatever version you had
     * @return Schema
     */
    public function read();
}