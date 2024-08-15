<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\Selector\Pagination;
use Medas\RestRequestHandler\{
    ConfigOptions\DefaultPageSize,
    ConfigOptions\MultisortQueryName,
    ConfigOptions\PageQueryName,
    ConfigOptions\PerPageQueryName,
    Exceptions\CannotParseQueryValue
};

#[Service]
readonly class SelectorBuilder
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
        private MultisortParser  $multisortParser,
        private ComparisonParser $comparisonParser,
    )
    {
    }

    public function build(string $entity, array $filters): QuerySelector
    {
        $selector = new QuerySelector($entity);
        $job = new SelectorBuilder\Job();

        foreach ($filters as $name => $value) {
            $this->processFilter($selector, $job, $name, $value);
        }

        $this->processPagination($selector, $job);

        return $selector;
    }

    private function processFilter(
        QuerySelector       $selector,
        SelectorBuilder\Job $job,
        string              $name,
        mixed               $value
    ): void
    {
        if ($name === $this->multisortQueryName) {
            $this->multisortParser->parse($selector, $value);
        }
        elseif ($name === $this->pageQueryName) {
            $job->page = (int) $value;
        }
        elseif ($name === $this->perPageQueryName) {
            $job->pageSize = (int) $value;
        }
        else {
            try {
                $this->comparisonParser->parse($selector, $name, $value);
            }
            catch (\Exception) {
                throw new CannotParseQueryValue($name, $value);
            }
        }
    }

    private function processPagination(QuerySelector $selector, SelectorBuilder\Job $job): void
    {
        $selector->definition()->add(new Pagination($job->page, $job->pageSize ?? $this->defaultPageSize));
    }
}
