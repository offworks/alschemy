<?php

namespace Alschemy\Schema;

use Doctrine\DBAL\Schema\Column;

class Table extends \Doctrine\DBAL\Schema\Table
{
    public function setColumn(Column $column)
    {
        $columnName = $column->getName();

        $columnName = $this->trimQuotes(strtolower($columnName));

        $this->_columns[$columnName] = $column;

        return $column;
    }
}