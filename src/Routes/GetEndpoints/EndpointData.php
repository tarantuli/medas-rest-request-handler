<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Routes\GetEndpoints;

readonly class EndpointData
{
    public function __construct(
        public string $type,
        public string $method,
        public string $href,
    )
    {
    }
}
