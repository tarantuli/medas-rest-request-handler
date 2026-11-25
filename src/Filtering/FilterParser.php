<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\Selector\{DerivedValues\DerivedValue, Element, Pagination};
use Medas\Json\StringProtector;
use Medas\RestRequestHandler\{
    ConfigOptions\DefaultPageSize,
    ConfigOptions\MultisortQueryName,
    ConfigOptions\PageQueryName,
    ConfigOptions\PerPageQueryName,
    ConfigOptions\PredefinedQueryName,
    Exceptions\CannotParseQueryValue
};

#[Service]
readonly class FilterParser
{
    public function __construct(
        private FilterParser\ComparisonExtractor         $comparisonExtractor,
        private FilterParser\ComparisonParser            $comparisonParser,
        private FilterParser\MultisortParser             $multisortParser,
        private FilterParser\PropertyParser              $propertyParser,
        private FilterParser\ReferencedEntitiesHandler   $referencedEntitiesHandler,
        private PredefinedQueries\PredefinedQueryHandler $predefinedQueryHandler,
        private StringProtector                          $stringProtector,

        #[ConfigValue(DefaultPageSize::class)]
        private int                                      $defaultPageSize,

        #[ConfigValue(MultisortQueryName::class)]
        private string|null                              $multisortQueryName,

        #[ConfigValue(PageQueryName::class)]
        private string|null                              $pageQueryName,

        #[ConfigValue(PerPageQueryName::class)]
        private string|null                              $perPageQueryName,

        #[ConfigValue(PredefinedQueryName::class)]
        private string|null                              $predefinedQueryName,
    )
    {
    }

    /**
     * @param array<string, DerivedValue> $derivedValues names a filter or sort may name
     *                                                  beside the entity's properties
     *
     * @return Element[]
     */
    public function parse(
        string        $entity,
        array         $filters,
        \Closure|null $typeFinder = null,
        array         $derivedValues = [],
    ): array
    {
        $job = new FilterParser\Job($entity, $derivedValues);

        foreach ($filters as $name => $value) {
            $this->processFilter($job, $name, $value, $typeFinder);
        }

        $this->processPagination($job);
        $this->referencedEntitiesHandler->handle($job);

        return $job->elements;
    }

    private function processFilter(
        FilterParser\Job $job,
        string           $name,
        mixed            $value,
        \Closure|null    $typeFinder = null
    ): void
    {
        if ($name === $this->multisortQueryName) {
            $this->multisortParser->parse($job, $name, $value);
        }
        elseif ($name === $this->pageQueryName) {
            if (!is_numeric($value) || (int) $value < 1) {
                throw new CannotParseQueryValue($name, $value);
            }

            $job->page = (int) $value;
        }
        elseif ($name === $this->perPageQueryName) {
            if (!is_numeric($value) || (int) $value < 1 || (int) $value > 1000) {
                throw new CannotParseQueryValue($name, $value);
            }

            $job->pageSize = (int) $value;
        }
        elseif ($name === $this->predefinedQueryName) {
            $this->predefinedQueryHandler->handle($job, $value);
        }
        else {
            try {
                $this->parseComparison($job, $name, $value, $typeFinder);
            }
            catch (\Exception) {
                throw new CannotParseQueryValue($name, $value);
            }
        }
    }

    private function parseComparison(
        FilterParser\Job $job,
        string           $name,
        mixed            $value,
        \Closure|null    $typeFinder
    ): void
    {
        $comparisonType = $this->comparisonExtractor->extract($name);

        if (array_key_exists($name, $job->derivedValues)) {
            $job->elements[] = $this->comparisonParser->parseDerived(
                $job->derivedValues[$name],
                $comparisonType,
                $this->stringProtector->decode($value)
            );

            return;
        }

        $property = $this->propertyParser->parse($job, $name);

        if ($property === null) {
            throw new CannotParseQueryValue($name, $value);
        }

        $type = $typeFinder ? $typeFinder($property->name, $property->entity) : null;

        $job->elements[] = $this->comparisonParser->parse(
            $property->entity,
            $property->name,
            $comparisonType,
            $this->stringProtector->decode($value),
            $type
        );
    }

    private function processPagination(FilterParser\Job $job): void
    {
        $job->elements[] = new Pagination($job->page, $job->pageSize ?? $this->defaultPageSize);
    }
}
