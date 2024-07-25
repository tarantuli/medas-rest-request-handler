<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authorization;

use Medas\Core\Attributes\{EventListener, Service};
use Medas\HttpRequestHandler\AccessManagement\AuthorizationVote;
use Medas\Routing\RouteHandler;

/**
 * This authorization vote handlers checks if the route handler method or its declaring class are tagged with AnyUser.
 * If so, then access is allowed if any user is authenticated. If not, access is disallowed.
 */
#[Service]
readonly class AnyUserHandler
{
    #[EventListener]
    public function handleAuthorizationVote(AuthorizationVote $authorizationVote): void
    {
        if (!$authorizationVote->requestHandler instanceof RouteHandler) {
            return;
        }

        $methodReflector = $authorizationVote->requestHandler->handlerMethod();

        if (attribute(AnyUser::class, $methodReflector)
                || attribute(AnyUser::class, $methodReflector->getDeclaringClass())) {
            $authorizationVote->allowedAccess = $authorizationVote->request->authentication->user !== null;
        }
    }
}
