<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\Selector\Conditions\{
    WhereContains,
    WhereEndsWith,
    WhereIn,
    WhereIs,
    WhereIsAtLeast,
    WhereIsAtMost,
    WhereIsLessThan,
    WhereIsMoreThan,
    WhereIsNot,
    WhereNotIn,
    WhereStartsWith
};
use Medas\RestRequestHandler\ConfigOptions\ComparisonOperators\{
    ContainsOperator,
    EndsWithOperator,
    IsAtLeastOperator,
    IsAtMostOperator,
    IsInOperator,
    IsLessThanOperator,
    IsMoreThanOperator,
    IsNotInOperator,
    IsNotOperator,
    StartsWithOperator
};

#[Service]
readonly class ComparisonExtractor
{
    /** @var array<class-string, string> Condition classes mapped to operator strings, sorted longest-first */
    private array $operators;

    public function __construct(
        #[ConfigValue(IsLessThanOperator::class)]
        string|null $isLessThanOperator,

        #[ConfigValue(IsAtMostOperator::class)]
        string|null $isAtMostOperator,

        #[ConfigValue(IsMoreThanOperator::class)]
        string|null $isMoreThanOperator,

        #[ConfigValue(IsAtLeastOperator::class)]
        string|null $isAtLeastOperator,

        #[ConfigValue(StartsWithOperator::class)]
        string|null $startsWithOperator,

        #[ConfigValue(EndsWithOperator::class)]
        string|null $endsWithOperator,

        #[ConfigValue(ContainsOperator::class)]
        string|null $containsOperator,

        #[ConfigValue(IsNotOperator::class)]
        string|null $isNotOperator,

        #[ConfigValue(IsInOperator::class)]
        string|null $isInOperator,

        #[ConfigValue(IsNotInOperator::class)]
        string|null $isNotInOperator,
    )
    {
        $candidates = [
            WhereIsLessThan::class => $isLessThanOperator,
            WhereIsAtMost::class => $isAtMostOperator,
            WhereIsMoreThan::class => $isMoreThanOperator,
            WhereIsAtLeast::class => $isAtLeastOperator,
            WhereStartsWith::class => $startsWithOperator,
            WhereEndsWith::class => $endsWithOperator,
            WhereContains::class => $containsOperator,
            WhereIsNot::class => $isNotOperator,
            WhereIn::class => $isInOperator,
            WhereNotIn::class => $isNotInOperator,
        ];

        // Remove disabled operators (null values)
        $operators = array_filter($candidates);

        // Sort the longest operator first so e.g. "<<" is matched before "<"
        uasort($operators, static fn(string $a, string $b): int => strlen($b) - strlen($a));

        $this->operators = $operators;
    }

    public function extract(string &$name): string
    {
        foreach ($this->operators as $conditionClass => $operator) {
            if (str_ends_with($name, $operator)) {
                $name = substr($name, 0, -strlen($operator));

                return $conditionClass;
            }
        }

        return WhereIs::class;
    }
}
