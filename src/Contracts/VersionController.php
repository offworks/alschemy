<?php

namespace Alschemy\Contracts;

use Alschemy\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;

interface VersionController
{
    public function read() : Schema;

    public function write(SchemaDiff $diff);
}