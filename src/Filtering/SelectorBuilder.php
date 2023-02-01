<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\RestRequestHandler\ConfigOptions\MultisortQueryName;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\ConfigValue;

#[Service]
class SelectorBuilder
{
    public function __construct(
        #[ConfigValue(MultisortQueryName::class)]
        private readonly string|null     $multisortQueryName,
        private readonly MultisortParser $multisortParser,
    )
    {
    }

    public function build(string $entity, array $filters): QuerySelector
    {
        $selector = new QuerySelector($entity);

        foreach ($filters as $name => $value) {
            $this->processFilter($selector, $name, $value);
        }

        return $selector;
    }

    private function processFilter(QuerySelector $selector, string $name, mixed $value): void
    {
        if ($name === $this->multisortQueryName) {
            $this->multisortParser->parse($selector, $value);
        }
        else {
            // Add filtering
        }
    }
}
