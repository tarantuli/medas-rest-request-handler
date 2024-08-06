<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authentication;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\EventListener,
    Attributes\Service,
    Interfaces\BearerTokenValidator
};
use Medas\HttpRequestHandler\{AccessManagement\AuthenticationVote, Request\HeaderFinder};
use Medas\RestRequestHandler\ConfigOptions\UsersClass;

#[Service]
readonly class BearerTokenHandler
{
    public function __construct(
        private BearerTokenValidator|null $validator,
        private HeaderFinder              $headerFinder,

        #[ConfigValue(UsersClass::class)]
        private string|null               $usersClass,
    )
    {
    }

    #[EventListener]
    public function validate(AuthenticationVote $vote): void
    {
        if ($this->validator === null || $this->usersClass === null) {
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
        $userId = $this->validator->userId($token);

        if ($userId) {
            $vote->user = em()->get($this->usersClass, $userId);
        }
    }
}
