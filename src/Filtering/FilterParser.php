<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\{EntityClassFinder, Selector\Element, Selector\Pagination};
use Medas\Json\StringProtector;
use Medas\RestRequestHandler\{
    ConfigOptions\DefaultPageSize,
    ConfigOptions\MultisortQueryName,
    ConfigOptions\PageQueryName,
    ConfigOptions\PerPageQueryName,
    Exceptions\CannotParseQueryValue
};

#[Service]
readonly class FilterParser
{
    public function __construct(
        #[ConfigValue(MultisortQueryName::class)]
        private string|null                            $multisortQueryName,

        #[ConfigValue(DefaultPageSize::class)]
        private int                                    $defaultPageSize,

        #[ConfigValue(PageQueryName::class)]
        private string|null                            $pageQueryName,

        #[ConfigValue(PerPageQueryName::class)]
        private string|null                            $perPageQueryName,
        private EntityClassFinder                      $entityClassFinder,
        private FilterParser\ComparisonExtractor       $comparisonExtractor,
        private FilterParser\ComparisonParser          $comparisonParser,
        private FilterParser\MultisortParser           $multisortParser,
        private FilterParser\ReferencedEntitiesHandler $referencedEntitiesHandler,
        private StringProtector                        $stringProtector,
    )
    {
    }

    /** @return Element[] */
    public function parse(string $entity, array $filters, \Closure $typeFinder = null): array
    {
        $job = new FilterParser\Job($entity);

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
        \Closure         $typeFinder = null
    ): void
    {
        if ($name === $this->multisortQueryName) {
            $this->multisortParser->parse($job, $value);
        }
        elseif ($name === $this->pageQueryName) {
            $job->page = (int) $value;
        }
        elseif ($name === $this->perPageQueryName) {
            $job->pageSize = (int) $value;
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

    private function parseComparison(FilterParser\Job $job, string $name, mixed $value, ?\Closure $typeFinder): void
    {
        $comparisonType = $this->comparisonExtractor->extract($name);

        if (str_contains($name, '.')) {
            [$subStore, $name] = explode('.', $name);
            $metaData = $this->entityClassFinder->getByStore($subStore);
            $entity = $metaData->className;
            $job->referencedEntities[$entity] = true;
        }
        else {
            $entity = $job->entity;
        }

        $type = $typeFinder ? $typeFinder($name, $entity) : null;

        $job->elements[] = $this->comparisonParser->parse(
            $entity,
            $name,
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
