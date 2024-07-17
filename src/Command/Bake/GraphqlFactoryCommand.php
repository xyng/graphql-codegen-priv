<?php

namespace Interweber\GraphQL\Command\Bake;

use Bake\Command\SimpleBakeCommand;
use Cake\Console\Arguments;
use Cake\Core\Configure;
use Cake\Database\Type\EnumType;
use Cake\Database\TypeFactory;
use Cake\ORM\Association;
use Cake\ORM\Association\BelongsTo;
use Cake\Utility\Inflector;

class GraphqlFactoryCommand extends SimpleBakeCommand {
	protected string $pathFragment = 'GraphQL/Factory/';

	public function name(): string {
		return 'graphql_factory';
	}

	public function fileName(string $name): string {
		return sprintf("%sFactory.php", $this->_entityName($name));
	}

	public function template(): string {
		return 'graphqlFactory.php';
	}

	public function templateData(Arguments $arguments): array {
		['namespace' => $namespace] = parent::templateData($arguments);

 		$name = $this->_getName($arguments->getArgumentAt(0));
		$name = Inflector::camelize($name);

		$currentModelName = $name;
		$plugin = $this->plugin;
		if ($plugin) {
			$plugin .= '.';
		}

		if ($this->getTableLocator()->exists($plugin . $currentModelName)) {
			$modelObj = $this->getTableLocator()->get($plugin . $currentModelName);
		} else {
			$modelObj = $this->getTableLocator()->get($plugin . $currentModelName, [
				'connectionName' => $this->connection,
			]);
		}

		$modelName = $this->_modelNameFromKey($name);
		$tableName = $modelName . 'Table';
		$entityName = $this->_entityName($name);
		$singularHumanName = $this->_singularHumanName($name);
		$pluralHumanName = $this->_pluralHumanName($name);
		$singularVariable = $this->_singularName($name);

		$entityObj = $modelObj->newEmptyEntity();
		$accessible = $entityObj->getAccessible();
		$pk = $modelObj->getPrimaryKey();
		$schema = $modelObj->getSchema();

		$assocs = collection($modelObj->associations());

		$assocKeys = $assocs
//			->map(fn (Association $assoc) => [
//				'type' => $assoc::class,
//				'b' => $assoc->getBindingKey(),
//				'f' => $assoc->getForeignKey(),
//			])
			->indexBy(fn (Association $assoc) => $assoc->getName())
			->map(fn (Association $assoc) => $assoc instanceof Association\HasMany || $assoc instanceof Association\BelongsToMany ? $assoc->getBindingKey() : $assoc->getForeignKey())
			->filter(fn ($k) => $k !== 'id')
			->toArray();

		$properties = [];
        foreach ($schema->columns() as $column) {
			if ($column === $pk || $column === 'uuid') {
				continue;
			}

			if (!($accessible[$column] ?? false)) {
				continue;
			}

            $columnSchema = $schema->getColumn($column);

			$type = $columnSchema['type'];

			$assoc = array_search($column, $assocKeys) ?: null;

            $properties[$column] = [
                'kind' => $assoc ? 'assoc' : 'column',
                'type' => $assoc ? 'ID' : $type,
                'null' => $columnSchema['null'],
				'assoc' => $assoc,
            ];
        }

		return compact(
			'namespace',
			'properties',
			'modelName',
			'tableName',
			'entityName',
			'singularHumanName',
			'pluralHumanName',
			'singularVariable',
		);
	}

	public function getEntityPropertySchema(Table $model): array
    {
        $properties = [];

        $schema = $model->getSchema();
        foreach ($schema->columns() as $column) {
            $columnSchema = $schema->getColumn($column);

            $properties[$column] = [
                'kind' => 'column',
                'type' => $columnSchema['type'],
                'null' => $columnSchema['null'],
            ];
        }

        return $properties;
    }
}
