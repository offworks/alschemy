<?php

namespace Alschemy\Schema;

class Schema extends \Doctrine\DBAL\Schema\Schema
{
    /**
     * @param string $tableName
     *
     * @return Table|\Doctrine\DBAL\Schema\Table
     */
    public function getTable($tableName)
    {
        $tableName = $this->getFullQualifiedAssetName($tableName);

        if (!isset($this->_tables[$tableName])) {
            $this->createTable($tableName);
        }

        return $this->_tables[$tableName];
    }

    /**
     * Creates a new table.
     *
     * @param string $tableName
     *
     * @return Table
     */
    public function createTable($tableName)
    {
        $table = new Table($tableName);
        $this->_addTable($table);

        foreach ($this->_schemaConfig->getDefaultTableOptions() as $name => $value) {
            $table->addOption($name, $value);
        }

        return $table;
    }

    protected function getUnquotedAssetName($assetName)
    {
        if ($this->isIdentifierQuoted($assetName)) {
            return $this->trimQuotes($assetName);
        }

        return $assetName;
    }

    protected function getFullQualifiedAssetName($name)
    {
        $name = $this->getUnquotedAssetName($name);

        if (strpos($name, '.') === false) {
            $name = $this->getName() . '.' . $name;
        }

        return strtolower($name);
    }
}