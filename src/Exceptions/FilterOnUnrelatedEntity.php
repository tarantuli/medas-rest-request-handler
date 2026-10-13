<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

class FilterOnUnrelatedEntity extends BadRequest
{
    public function __construct(string $filteredEntity, string $referencedEntity, int $referenceCount)
    {
        parent::__construct($referencedEntity, $filteredEntity, $referenceCount);
    }

    public function pattern(): string
    {
        return 'cannot filter on %s: %s needs exactly one property referring to it to join it, and has %d';
    }
}
