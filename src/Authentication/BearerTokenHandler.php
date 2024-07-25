<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authentication;

use Medas\Core\{Attributes\Service, Interfaces\BearerTokenValidator};
use Medas\HttpRequestHandler\{AccessManagement\AuthenticationVote, Request\HeaderFinder};

#[Service]
readonly class BearerTokenHandler
{
    public function __construct(
        private BearerTokenValidator|null $validator,
        private HeaderFinder              $headerFinder,
    )
    {
    }

    public function validate(AuthenticationVote $vote): void
    {
        if ($this->validator === null) {
            return;
        }

        $header = $this->headerFinder->find($vote->request->serverData, 'Authorization');

        if ($header === null) {
            return;
        }

        if (!str_starts_with($header, 'Bearer ')) {
            return;
        }

        $token = substr($header, 7);
        $vote->user = $this->validator->user($token);
    }
}
