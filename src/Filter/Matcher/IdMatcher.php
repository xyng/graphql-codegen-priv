<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

class IdMatcher extends BaseMatcher {
	/**
	 * @param ID|null $eq
	 * @param ID|null $neq
	 * @param ID[]|null $in
	 * @param ID[]|null $nin
	 * @return IdMatcher
	 */
	#[Factory]
	public static function factory(
		?ID $eq,
		?ID $neq,
		?array $in,
		?array $nin,
	): IdMatcher {
		return new self($eq, $neq, $in, $nin, null);
	}

	public function build(Query $query, ExpressionInterface|string $field): QueryExpression {
		return $this->buildBasic($query, $field);
	}

	public function buildRelation(string $relation, string $field): \Closure {
		return function (Query $query, QueryExpression $exp) use ($relation, $field) {
			$pk = $query->getRepository()->aliasField($query->getRepository()->getPrimaryKey());

			return $query
				->where($exp->in(
					$pk,
					$query->getRepository()->find()
						->select($pk)
						->leftJoinWith($relation)
						->where($this->build(
							$query,
							$query->getRepository()->getAssociation($relation)->aliasField($field)
						))
				));
		};
	}
}
