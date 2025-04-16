<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\Selector\{Element, Pagination};
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
        private string|null      $multisortQueryName,

        #[ConfigValue(DefaultPageSize::class)]
        private int              $defaultPageSize,

        #[ConfigValue(PageQueryName::class)]
        private string|null      $pageQueryName,

        #[ConfigValue(PerPageQueryName::class)]
        private string|null      $perPageQueryName,
        private ComparisonParser $comparisonParser,
        private MultisortParser  $multisortParser,
        private StringProtector  $stringProtector,
    )
    {
    }

    /** @return Element[] */
    public function parse(array $filters, \Closure $typeFinder = null): array
    {
        $job = new FilterParser\Job();

        foreach ($filters as $name => $value) {
            $this->processFilter($job, $name, $value, $typeFinder);
        }

        $this->processPagination($job);

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
                $job->elements[] = $this->comparisonParser->parse(
                    $name,
                    $this->stringProtector->decode($value),
                    $typeFinder
                );
            }
            catch (\Exception) {
                throw new CannotParseQueryValue($name, $value);
            }
        }
    }

    private function processPagination(FilterParser\Job $job): void
    {
        $job->elements[] = new Pagination($job->page, $job->pageSize ?? $this->defaultPageSize);
    }
}
