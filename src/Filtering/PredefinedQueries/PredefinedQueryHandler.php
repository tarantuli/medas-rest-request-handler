<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\PredefinedQueries;

use Medas\Core\Attributes\Service;
use Medas\RestRequestHandler\Exceptions\NoPredefinedQueryFoundByName;
use Medas\RestRequestHandler\Filtering\FilterParser\Job;

#[Service]
readonly class PredefinedQueryHandler
{
    public function __construct(
        private PredefinedQueryManager $queryManager,
    )
    {
    }

    public function handle(Job $job, string $name): void
    {
        if (!$query = $this->queryManager->getByName($name)) {
            throw new NoPredefinedQueryFoundByName($name);
        }

        $job->elements = array_merge($job->elements, $query->elements());
    }
}
