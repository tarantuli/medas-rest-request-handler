<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Routes\GetEndpoints;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\EventListener,
    Attributes\Service,
    Events\AllowedAccess
};
use Medas\RestRequestHandler\{ConfigOptions\EnableEndpoints, Routes\GetEndpoints};
use Medas\Routing\AllowRoute;

#[Service]
readonly class AllowRouteListener
{
    public function __construct(
        #[ConfigValue(EnableEndpoints::class)]
        private bool $endpointsEnabled = false,
    )
    {
    }

    #[EventListener]
    public function handle(AllowRoute $event): void
    {
        if ($event->handler->handlerMethod()->class !== GetEndpoints::class) {
            // Not the /endpoints route - leave the vote untouched (Pending), so it's allowed by
            // default and other listeners get a chance to weigh in.
            return;
        }

        if (!$this->endpointsEnabled) {
            $event->allowedAccess = AllowedAccess::Denied;
        }
    }
}
