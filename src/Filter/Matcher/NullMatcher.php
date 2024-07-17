<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

class NullMatcher extends BaseMatcher {

	#[Factory]
	public static function factory(): NullMatcher {
		return new self(null, null, null, null, null);
	}

	public function build(Query $query, ExpressionInterface|string $field): QueryExpression {
		return $this->buildBasic($query, $field);
	}
}
