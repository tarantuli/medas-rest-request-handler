<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authorization;

use Medas\Core\Attributes\{EventListener, Service};
use Medas\HttpRequestHandler\AccessManagement\AuthorizationVote;
use Medas\Routing\RouteHandler;

#[Service]
/**
 * This auth vote handlers checks if the route handler method or its declaring class are tagged with PublicResource.
 * If so, then access is allowed. In all other cases, it expresses no opinion.
 */
readonly class PublicResourceHandler
{
    #[EventListener]
    public function handleAuthorizationVote(AuthorizationVote $authVote): void
    {
        if (!$authVote->requestHandler instanceof RouteHandler) {
            return;
        }

        $methodReflector = $authVote->requestHandler->handlerMethod();

        if (attribute(PublicResource::class, $methodReflector)
                || attribute(PublicResource::class, $methodReflector->getDeclaringClass())) {
            $authVote->allowedAccess = true;
        }
    }
}
