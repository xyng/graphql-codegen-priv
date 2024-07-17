<?php

namespace Interweber\GraphQL\Command\Bake;

use Bake\Command\SimpleBakeCommand;
use Cake\Console\Arguments;
use Cake\Database\Type\EnumType;
use Cake\Database\TypeFactory;
use Cake\ORM\Association;
use Cake\Utility\Inflector;

class GraphqlControllerCommand extends SimpleBakeCommand {
	protected string $pathFragment = 'GraphQL/Controller/';

	public function name(): string {
		return 'graphql_controller';
	}

	public function fileName(string $name): string {
		return sprintf("%sController.php", $name);
	}

	public function template(): string {
		return 'graphqlController.php';
	}

	public function templateData(Arguments $arguments): array {
		['namespace' => $namespace] = parent::templateData($arguments);

 		$name = $this->_getName($arguments->getArgumentAt(0));
		$name = Inflector::camelize($name);

		$tableName = $this->_modelNameFromKey($name) . 'Table';
		$entityName = $this->_entityName($name);
		$singularHumanName = $this->_singularHumanName($name);
		$pluralHumanName = $this->_pluralHumanName($name);
		$singularVariable = $this->_singularName($name);

		$identityVariable = $singularVariable === 'identity' ? 'authIdentity' : 'identity';

		return compact(
			'namespace',
			'tableName',
			'entityName',
			'singularHumanName',
			'pluralHumanName',
			'singularVariable',
			'identityVariable',
		);
	}
}
