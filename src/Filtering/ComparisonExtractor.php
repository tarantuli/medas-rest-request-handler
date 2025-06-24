<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

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
use Medas\RestRequestHandler\ConfigOptions\ComparisonOperators\IsLessThanOperator;

#[Service]
readonly class ComparisonExtractor
{
    public function __construct(
        #[ConfigValue(IsLessThanOperator::class)]
        private string|null $isLessThanOperator,
    )
    {
    }

    public function extract(&$name): string
    {
        if ($this->isLessThanOperator !== null && str_ends_with($name, $this->isLessThanOperator)) {
            $name = substr($name, 0, -strlen($this->isLessThanOperator));

            return WhereIsLessThan::class;
        }

        if (str_ends_with($name, '<')) {
            $name = substr($name, 0, -1);

            return WhereIsAtMost::class;
        }

        if (str_ends_with($name, '>>')) {
            $name = substr($name, 0, -2);

            return WhereIsMoreThan::class;
        }

        if (str_ends_with($name, '>')) {
            $name = substr($name, 0, -1);

            return WhereIsAtLeast::class;
        }

        if (str_ends_with($name, '^')) {
            $name = substr($name, 0, -1);

            return WhereStartsWith::class;
        }

        if (str_ends_with($name, '$')) {
            $name = substr($name, 0, -1);

            return WhereEndsWith::class;
        }

        if (str_ends_with($name, '*')) {
            $name = substr($name, 0, -1);

            return WhereContains::class;
        }

        if (str_ends_with($name, '!')) {
            $name = substr($name, 0, -1);

            return WhereIsNot::class;
        }

        if (str_ends_with($name, '∈')) {
            $name = substr($name, 0, -strlen('∈'));

            return WhereIn::class;
        }

        if (str_ends_with($name, '∉')) {
            $name = substr($name, 0, -strlen('∉'));

            return WhereNotIn::class;
        }

        return WhereIs::class;
    }
}
