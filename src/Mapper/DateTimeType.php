<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Mapper;

use Cake\I18n\Date;
use Cake\I18n\DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class DateTimeType extends ScalarType {
	/**
	 * @var string
	 */
	public string $name = 'CustomDateTime';

	public function serialize(mixed $value): string {
		if (!$value instanceof DateTimeImmutable) {
			throw new InvariantViolation('DateTime is not an instance of DateTimeImmutable: ' . Utils::printSafe($value));
		}

		return $value->format(DateTimeInterface::ATOM);
	}

	/**
	 * @var string|null
	 */
	public string|null $description = 'The `CustomDateTime` scalar type represents time data, represented as an ISO-8601 encoded UTC date string.';

	public function parseValue($value): ?DateTime {
		if ($value === null) {
			return null;
		}

		if ($value instanceof DateTime) {
			return $value;
		}

		return new DateTime($value);
	}

	public function parseLiteral($valueNode, array|null $variables = null): string {
		if ($valueNode instanceof StringValueNode) {
			return $valueNode->value;
		}

		// Intentionally without message, as all information already in wrapped Exception
		throw new GraphQLRuntimeException();
	}
}
