<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authentication;

use Medas\Core\{
    Attributes\Service,
    Events\DebugInformation,
    Interfaces\AuthenticationData,
    Interfaces\AuthenticationTokenController
};
use Medas\HttpRequestHandler\Request\{HeaderFinder, ServerData};

#[Service]
readonly class BearerTokenHandler
{
    public function __construct(
        private AuthenticationTokenController|null $tokenController,
        private HeaderFinder                       $headerFinder,
    )
    {
    }

    public function data(ServerData $serverData): AuthenticationData|null
    {
        if ($this->tokenController === null) {
            return null;
        }

        $header = $this->headerFinder->find($serverData, 'Authorization');

        if ($header === null) {
            dispatch(new DebugInformation('[bearer-token] Authorization header not found'));

            return null;
        }

        if (!str_starts_with($header, 'Bearer ')) {
            dispatch(new DebugInformation('[bearer-token] Authorization header does not start with "Bearer"'));

            return null;
        }

        $token = substr($header, 7);

        return $this->tokenController->data($token);
    }
}
