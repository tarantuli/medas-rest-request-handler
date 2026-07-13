<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Routes;

use Medas\HttpRequestHandler\ResponseTypes\{JsonResponseWrapper, Response};
use Medas\Routing\{Methods\Get, Route};

#[Route('endpoints')]
readonly class GetEndpoints
{
    public function __construct(
        private GetEndpoints\EndpointsParser $endpointsParser,
    )
    {
    }

    #[Get]
    public function handle(): Response
    {
        $entities = $this->endpointsParser->parse();

        return new JsonResponseWrapper($entities);
    }
}
