<?php

namespace Alschemy\Vendors\Laravel;

use Alschemy\Contracts\VersionController;
use Alschemy\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;

class MigrationController implements VersionController
{
    /**
     * @var MigrationReader
     */
    private $reader;

    /**
     * @var MigrationWriter
     */
    private $writer;

    public function __construct(MigrationReader $reader, MigrationWriter $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    public static function create($dir)
    {
        return new static(new MigrationReader($dir), new MigrationWriter($dir));
    }

    public function read() : Schema
    {
        return $this->reader->read();
    }

    public function write(SchemaDiff $diff)
    {
        $this->writer->write($diff);
    }
}