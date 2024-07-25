<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authorization;

use Medas\Core\Attributes\{EventListener, Service};
use Medas\HttpRequestHandler\AccessManagement\AuthorizationVote;
use Medas\Routing\RouteHandler;

/**
 * This authorization vote handlers checks if the route handler method or its declaring class are tagged with PublicResource.
 * If so, then access is allowed. In all other cases, it expresses no opinion.
 */
#[Service]
readonly class PublicResourceHandler
{
    #[EventListener]
    public function handleAuthorizationVote(AuthorizationVote $vote): void
    {
        if (!$vote->requestHandler instanceof RouteHandler) {
            return;
        }

        $methodReflector = $vote->requestHandler->handlerMethod();

        if (attribute(PublicResource::class, $methodReflector)
                || attribute(PublicResource::class, $methodReflector->getDeclaringClass())) {
            $vote->allowedAccess = true;
        }
    }
}
