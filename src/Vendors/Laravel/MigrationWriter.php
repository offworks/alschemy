<?php

namespace Alschemy\Vendors\Laravel;

use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class MigrationWriter
{
    /**
     * @var
     */
    protected $dir;

    /**
     * Type handlers
     * @var array
     */
    protected $handlers = [];

    public function __construct($dir)
    {
        $this->dir = $dir;

        $this->prepareHandlers();
    }

    protected function loadHandlersFromDBALTypes()
    {
        foreach (Type::getTypesMap() as $type => $class) {
            $this->handlers[$type] = $type;
        }
    }

    protected function prepareHandlers()
    {
        $this->loadHandlersFromDBALTypes();

        /*$this->handlers = [
            'bigIncrements' => function (Table $table, array $args) {
                $table->setColumn(new Column($args[0], Type::getType(Type::BIGINT)));
                $table->setPrimaryKey([$args[0]]);
            },
            'bigInteger' => Type::BIGINT,
            'binary' => 'BLOG',
            'boolean' => 'BOOLEAN',
            'char' => 'CHAR',
            'date' => 'DATE',
            'dateTime' => 'DATETIME',
            'dateTimeTz' => 'DATETIME',
            'decimal' => 'DECIMAL',
            'double' => 'DOUBLE',
            'enum' => 'ENUM',
            'float' => 'FLOAT',
            'geometry' => 'GEOMETRY',
            'geometryCollection' => 'GEOMETRYCOLLECTION',
            'increments' => function (Table $table, array $args) {
                $table->setColumn(new Column($args[0], Type::getType(Type::INTEGER)));
                $table->setPrimaryKey([$args[0]]);
            },
            'integer' => 'INTEGER',
            'ipAddress' => 'IP',
            'json' => 'JSON',
            'jsonb' => 'JSONB',
            'lineString' => 'LINESTRING',
            'longText' => 'LONGTEXT',
            'macAddress' => 'MAC',
            'mediumIncrements' => 'MEDIUMINT',
            'mediumText' => 'MEDIUMTEXT',
            'morphs' => '',
            'uuidMorphs' => '',
            'multiLineString' => 'MULTILINESTRING',
            'multiPoint' => 'MULTIPOINT',
            'multiPolygon' => 'MULTIPOLYGON',
            'nullableMorphs' => '',
            'nullableUuidMorphs' => '',
            'nullableTimestamps' => '',
            'point' => 'POINT',
            'polygon' => 'POLYGON',
            'rememberToken' => function (Table $table) {

            },
            'set' => '',
            'smallIncrements' => 'SMALLINT',
            'smallInteger' => 'SMALLINT',
            'softDeletes' => function (Table $table) {
                $table->setColumn(new Column('deleted_at', TypeMap::getType('timestamp')));
            },
            'softDeletesTz' => '',
            'string' => Type::STRING,
            'text' => 'TEXT',
            'time' => 'TIME',
            'timeTz' => 'TIME',
            'timestamp' => 'TIMESTAMP',
            'timestampTz' => 'TIMESTAMP',
            'timestamps' => function (Table $table, $precision = 0) {
                $table->setColumn(new Column('created_at', TypeMap::getType('timestamp'), compact('precision')));
                $table->setColumn(new Column('updated_at', TypeMap::getType('timestamp'), compact('precision')));
            },
            'timestampsTz' => '',
            'tinyIncrements' => function (Table $table, array $args) {
                $table->setColumn(new Column($args[0], TypeMap::getType('tinyint')));
            },
            'tinyInteger' => 'TINYINT',
            'unsignedBigInteger' => 'BIGINT',
            'unsignedDecimal' => 'DECIMAL',
            'unsignedInteger' => 'INTEGER',
            'unsignedMediumInteger' => 'MEDIUMINT',
            'unsignedSmallInteger' => 'SMALLINT',
            'unsignedTinyInteger' => 'TINYINT',
            'uuid' => 'UUID',
            'year' => 'YEAR',
            'unique' => function (Table $table, array $args) {
                $table->addUniqueIndex([$args[0]], isset($args[1]) ? $args[1] : null);
            },
            'index' => function (Table $table, array $args) {
                $table->addIndex([$args[0]], isset($args[1]) ? $args[1] : null);
            }
        ];*/
    }

    public function write(SchemaDiff $diff)
    {
        $newStub = file_get_contents(__DIR__ . '/stubs/new-table-migration.stub');
        $updateStub = file_get_contents(__DIR__ . '/stubs/update-table-migration.stub');

        foreach ($diff->newTables as $table) {
            $name = str_replace($table->getNamespaceName() . '.', '', $table->getName());
            $tableCased = str_replace(' ', '', ucfirst(str_replace(['-', '_'], ' ', $name)));

            $stub = str_replace('{{table}}', $name, $newStub);
            $stub = str_replace('{{tableCased}}', $tableCased, $stub);

            $codes = $this->prepareCreateCodes($table);

            $stub = str_replace('{{migrationcodes}}', $codes, $stub);

            var_dump($stub);
        }


        foreach ($diff->changedTables as $table) {
        }
    }

    public function prepareCreateCodes(Table $table)
    {
        $lines = [];

        foreach ($table->getColumns() as $column) {
            $type = $column->getType()->getName();
            $name = $column->getName();

            $method = strtolower($type);

            $line = '$table->' . $method . '(\'' . $name . '\')';

            $lines[] = str_repeat(' ', 16) . $line . ';';
        }

        return implode("\n", $lines);
    }
}