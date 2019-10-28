<?php

namespace Alschemy\Providers\Laravel;

use Alschemy\Contracts\VersionReader;
use Alschemy\TypeMap;
use Alschemy\Schema\Schema;
use Alschemy\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use PhpParser\Error;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\ParserFactory;

function pp($r) {
    if (is_object($r))
        $r = $r->jsonSerialize();

    echo '<pre>';
    print_r($r);
}

class MigrationReader implements VersionReader
{
    /**
     * @var
     */
    private $dir;

    protected $handlers = [];

    protected $modifiers = [];

    public function __construct($dir)
    {
        $this->dir = $dir;

        $this->prepareHandlers();
    }

    protected function prepareHandlers()
    {
        $this->handlers = [
            'bigIncrements' => function(Table $table, array $args) {
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
            'increments' => function(Table $table, array $args) {
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
            'rememberToken' => function(Table $table) {

            },
            'set' => '',
            'smallIncrements' => 'SMALLINT',
            'smallInteger' => 'SMALLINT',
            'softDeletes' => function(Table $table) {
                $table->setColumn(new Column('deleted_at', TypeMap::getType('timestamp')));
            },
            'softDeletesTz' => '',
            'string' => Type::STRING,
            'text' => 'TEXT',
            'time' => 'TIME',
            'timeTz' => 'TIME',
            'timestamp' => 'TIMESTAMP',
            'timestampTz' => 'TIMESTAMP',
            'timestamps' => function(Table $table, $precision = 0) {
                $table->setColumn(new Column('created_at', TypeMap::getType('timestamp'), compact('precision')));
                $table->setColumn(new Column('updated_at', TypeMap::getType('timestamp'), compact('precision')));
            },
            'timestampsTz' => '',
            'tinyIncrements' => function(Table $table, array $args) {
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
            'unique' => function(Table $table, array $args) {
                $table->addUniqueIndex($args[0], isset($args[1]) ? $args[1] : null);
            },
            'index' => function(Table $table, array $args) {
                $table->addIndex($args[0], isset($args[1]) ? $args[1] : null);
            }
        ];

        $this->modifiers = [
            'unique' => function(Table $table, Column $column, array $args) {
                $table->addUniqueIndex([$column->getName()]);
            },
            'index' => function(Table $table, Column $column, array $args) {
                $table->addIndex([$column->getName()], isset($args[0]) ? $args[0] : null);
            },
            'autoIncrement' => function(Table $table, Column $column) {
                $column->setAutoincrement(true);
            },
            'charset' => function(Table $table, Column $column, array $args) {
                $column->setOptions(['charset' => $args[0]]);
            },
            'collation' => function(Table $table, Column $column, array $args) {
                $column->setOptions(['collation' => $args[0]]);
            },
            'default' => function(Table $table, Column $column, array $args) {
                $column->setDefault($args[0]);
            },
            'nullable' => function(Table $table, Column $column, array $args) {
                $column->setNotnull(isset($args[0]) ? !$args[0] : false);
            },
            'unsigned' => function(Table $table, Column $column, array $args) {
                $column->setUnsigned(true);
            },
            'useCurrent' => function(Table $table, Column $column, array $args) {
                $column->setDefault('CURRENT_TIMESTAMP');
            }
        ];
    }

    /**
     * @param string $name
     * @param string|\Closure $handler
     */
    public function setHandler($name, $handler)
    {
        $this->handlers[$name] = $handler;
    }

    public function setModifier($name, \Closure $modifier)
    {
        $this->modifiers[$name] = $modifier;
    }

    /**
     * Output the schema of whatever version you had
     * @return Schema|null
     */
    public function read()
    {
        $dir = new \DirectoryIterator($this->dir);

        $schema = new Schema();

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        foreach ($dir as $fileInfo) {
            if ($fileInfo->isDot())
                continue;

            try {
                $ast = $parser->parse(file_get_contents($fileInfo->getPathname()));
            } catch (Error $error) {
                echo 'Parse error : ' . $error->getMessage();
                return null;
            }

            $this->applyChangesFromAST($ast, $schema);
        }

        return $schema;
    }

    protected function applyChangesFromAST($ast, Schema $schema)
    {
        foreach ($this->getClosureExpressions($this->getUpStatement($ast)) as $table => $expressions) {
            foreach ($expressions as $expression) {
                $this->resolve($this->getCallChains($expression->expr), $schema->getTable($table));
            }
        }
    }

    protected function resolve(array $expr, Table $table)
    {
        $main = $expr['main'];
        $method = $main['name'];

        $modifiers = $expr['modifiers'];

        if (!isset($this->handlers[$method]))
            throw new \Exception('Unknown method [' . $method . ']');

        $handler = $this->handlers[$method];

        if (is_string($handler)) {
            if ($handler === '')
                throw new \Exception('Can\'t handle [' . $method . '] type');

            $type = strtolower($handler);

            $type = TypeMap::getType($type);

            if (!isset($main['args'][0]))
                throw new \Exception('Argument is required for ' .$method . ' of table ' . $table->getName() . ' migration');

            $table->setColumn(new Column($main['args'][0], $type));
        } else if (is_object($handler) && $handler instanceof \Closure) {
            $handler($table, $main['args']);
        }

        foreach ($modifiers as $modifier) {
            if (!isset($this->modifiers[$modifier['name']]))
                throw new \Exception('Unknown modifier [' . $modifier['name'] . ']');

            if (!isset($main['args'][0]))
                throw new \Exception('Cant apply modifier if column is unknown.');

            $this->modifiers[$modifier['name']]($table, $table->getColumn($main['args'][0]), $modifier['args']);
        }
    }

    protected function getCallChains(Expr $expr, $nodes = [])
    {
        if ($expr->var && $expr->var instanceof MethodCall) {

            $nodes[] = [
                'name' => $expr->name->name,
                'args' => $this->prepareArgs($expr->var->args)
            ];

            return $this->getCallChains($expr->var, $nodes);
        } else if ($expr->var && $expr->var instanceof Variable) {
            return [
                'main' => [
                    'name' => $expr->name->name,
                    'args' => $this->prepareArgs($expr->args)
                ],
                'modifiers' => array_reverse($nodes)
            ];
        }

        throw new \Exception('Huh');
    }

    protected function prepareArgs(array $args)
    {
        $r = [];

        /** @var Arg $arg */
        foreach ($args as $arg) {
            if ($arg->value instanceof Expr\Array_) {
                $vs = [];

                foreach ($arg->value->items as $item) {
                    $vs[] = $item->value->value;
                }

                $r[] = $vs;
            } else {
                $r[] = $arg->value->value;
            }
        }

        return $r;
    }

    protected function getClosureExpressions(ClassMethod $up)
    {
        $expressions = [];

        /** @var Expression $stmt */
        foreach ($up->stmts as $stmt) {
            if ($stmt->expr->class->parts[0] == 'Schema')
                $expressions[$stmt->expr->args[0]->value->value] = $stmt->expr->args[1]->value->stmts;
        }

        return $expressions;
    }

    /**
     * @param $ast
     * @return ClassMethod
     */
    protected function getUpStatement($ast)
    {
        foreach ($ast as $statement) {
            if (!($statement instanceof Class_))
                continue;

            foreach ($statement->stmts as $classStatement) {
                if (!($classStatement instanceof ClassMethod))
                    continue;

                if ($classStatement->name != 'up')
                    continue;

                return $classStatement;
            }
        }

        return null;
    }
}