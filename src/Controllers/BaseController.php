<?php

namespace Alschemy\Controllers;

use Alschemy\Contracts\VersionReader;
use Alschemy\Contracts\VersionWriter;

abstract class BaseController
{
    /**
     * @var VersionReader
     */
    private $reader;

    /**
     * @var VersionWriter
     */
    private $writer;

    public final function __construct(VersionReader $reader, VersionWriter $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }
}