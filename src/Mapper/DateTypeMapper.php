<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Mapper;

use Cake\I18n\Date;
use Cake\I18n\DateTime;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;

class DateTypeMapper implements RootTypeMapperInterface {
	private static DateTimeType|null $dateTimeType = null;
	private static DateType|null $dateType = null;

	/**
	 * @param RootTypeMapperInterface $next
	 */
	public function __construct(
		private RootTypeMapperInterface $next
	) {
	}

	public function toGraphQLOutputType(
		Type $type,
		?OutputType $subType,
		$reflector,
		DocBlock $docBlockObj
	): OutputType&GraphQLType {
		$mapped = $this->mapBaseType($type);

		if ($mapped !== null) {
			return $mapped;
		}

		return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
	}

	public function toGraphQLInputType(
		Type $type,
		?InputType $subType,
		string $argumentName,
		ReflectionMethod|ReflectionProperty $reflector,
		DocBlock $docBlockObj
	): InputType&GraphQLType {
		$mapped = $this->mapBaseType($type);

		if ($mapped !== null) {
			return $mapped;
		}

		return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
	}

	private function mapBaseType(Type $type): DateTimeType|DateType|null {
		if (!$type instanceof Object_) {
			return null;
		}

		$fqcn = (string) $type->getFqsen();
		if ($fqcn === '\\' . DateTime::class) {
			return self::getDateTimeType();
		}

		if ($fqcn === '\\' . Date::class) {
			return self::getDateType();
		}

		 return null;
	}

	private static function getDateType(): DateType {
		if (self::$dateType === null) {
			self::$dateType = new DateType();
		}

		return self::$dateType;
	}

	private static function getDateTimeType(): DateTimeType {
		if (self::$dateTimeType === null) {
			self::$dateTimeType = new DateTimeType();
		}

		return self::$dateTimeType;
	}

	public function mapNameToType(string $typeName): NamedType&GraphQLType {
		if ($typeName === 'DateTime') {
			return self::getDateTimeType();
		}

		if ($typeName === 'CustomDateTime') {
			return self::getDateTimeType();
		}

		if ($typeName === 'Date') {
			return self::getDateType();
		}

		return $this->next->mapNameToType($typeName);
	}
}
