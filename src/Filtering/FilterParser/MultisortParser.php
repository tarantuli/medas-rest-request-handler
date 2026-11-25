<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Selector\{Operants\Operant, Sorting\SortBy, Sorting\SortDirection};
use Medas\RestRequestHandler\Exceptions\{CannotParseQueryValue, InvalidSortDirection};

#[Service]
readonly class MultisortParser
{
    public function __construct(
        private PropertyParser $propertyParser,
    )
    {
    }

    /**
     * @param string $name the query part's own name, which a refusal names
     */
    public function parse(Job $job, string $name, string $value): void
    {
        $parts = explode(',', $value);

        foreach ($parts as $part) {
            $direction = substr($part, 0, 1);

            if ($direction !== '<' && $direction !== '>') {
                throw new InvalidSortDirection($direction);
            }

            $operant = $this->operant($job, substr($part, 1));

            // Refused as a filter on the same name is, instead of reaching the database
            // as an unknown column
            if ($operant === null) {
                throw new CannotParseQueryValue($name, $part);
            }

            $job->elements[] = SortBy::c(
                $operant,
                $direction === '>' ? SortDirection::DESC : SortDirection::ASC
            );
        }
    }

    // A property of another entity, "<products.name", sorts by that entity's property,
    // checked and joined in the same way as a dotted filter (PropertyParser). A derived
    // value sorts by its expression.
    private function operant(Job $job, string $name): Operant|null
    {
        if (array_key_exists($name, $job->derivedValues)) {
            return $job->derivedValues[$name];
        }

        return $this->propertyParser->parse($job, $name);
    }
}
