<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\Attributes\ConfigValue;
use Medas\EntityManager\Selector\Pagination;
use Medas\RestRequestHandler\ConfigOptions\{DefaultPageSize, MultisortQueryName, PageQueryName, PerPageQueryName};
use Medas\ServiceManager\Service;

#[Service]
class SelectorBuilder
{
    private int|null $page = null;
    private int|null $pageSize = null;

    public function __construct(
        #[ConfigValue(MultisortQueryName::class)]
        private readonly string|null      $multisortQueryName,
        #[ConfigValue(DefaultPageSize::class)]
        private readonly int              $defaultPageSize,
        #[ConfigValue(PageQueryName::class)]
        private readonly string|null      $pageQueryName,
        #[ConfigValue(PerPageQueryName::class)]
        private readonly string|null      $perPageQueryName,
        private readonly MultisortParser  $multisortParser,
        private readonly ComparisonParser $comparisonParser,
    )
    {
    }

    public function build(string $entity, array $filters): QuerySelector
    {
        $selector = new QuerySelector($entity);

        foreach ($filters as $name => $value) {
            $this->processFilter($selector, $name, $value);
        }

        $this->processPagination($selector);

        return $selector;
    }

    private function processFilter(QuerySelector $selector, string $name, mixed $value): void
    {
        if ($name === $this->multisortQueryName) {
            $this->multisortParser->parse($selector, $value);
        }
        elseif ($name === $this->pageQueryName) {
            $this->page = (int) $value;
        }
        elseif ($name === $this->perPageQueryName) {
            $this->pageSize = (int) $value;
        }
        else {
            $this->comparisonParser->parse($selector, $name, $value);
        }
    }

    private function processPagination(QuerySelector $selector): void
    {
        if ($this->page === null) {
            return;
        }

        $selector->definition()->add(
            new Pagination($this->page, $this->pageSize ?? $this->defaultPageSize)
        );
    }
}
