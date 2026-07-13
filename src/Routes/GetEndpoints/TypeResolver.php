<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Routes\GetEndpoints;

use Medas\Core\Attributes\Service;
use Medas\RestRequestHandler\Exceptions\CannotDetermineEndpointType;
use Medas\Routing\{
    Methods\Delete,
    Methods\Get,
    Methods\Patch,
    Methods\Post,
    Methods\Put,
    Parameters\Constant,
    RouteHandler
};

#[Service]
readonly class TypeResolver
{
    /**
     * Falls back to inference when a Route doesn't set endpointType explicitly. Get routes prefer
     * the isCollectionEndpoint()/isEntityEndpoint() flags, since those already handle nested
     * routes correctly (e.g., GET /invoices/:id/lines as a collection endpoint); everything else,
     * including Get routes that set neither flag, falls back to a shape check: does the route's
     * fully compiled parameter list end in a non-constant (i.e., an id) parameter.
     */
    public function resolve(RouteHandler $handler, string $entityClass): string
    {
        $method = $handler->method();
        $hasTrailingId = $this->hasTrailingIdParameter($handler);

        if ($method instanceof Get) {
            return match (true) {
                $method->isEntityEndpoint(), $hasTrailingId => EndpointType::GetEntity,
                default => EndpointType::GetCollection,
            };
        }

        if ($method instanceof Post && !$hasTrailingId) {
            return EndpointType::CreateEntity;
        }

        if (($method instanceof Put || $method instanceof Patch) && $hasTrailingId) {
            return EndpointType::UpdateEntity;
        }

        if ($method instanceof Delete && $hasTrailingId) {
            return EndpointType::DeleteEntity;
        }

        throw new CannotDetermineEndpointType(
            $handler->handlerMethod()->class,
            $entityClass,
            $method->name()
        );
    }

    private function hasTrailingIdParameter(RouteHandler $handler): bool
    {
        $parameters = $handler->parameters();
        $last = end($parameters);

        return $last !== false && !$last instanceof Constant;
    }
}
