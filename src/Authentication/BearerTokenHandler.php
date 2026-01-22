<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authentication;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\EventListener,
    Attributes\Service,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\BearerTokenValidator,
    Interfaces\UuidProvider,
    Types\Uuid as UuidType
};
use Medas\EntityManager\MetaDataManager;
use Medas\HttpRequestHandler\{Authentication\AuthenticationVote, Request\HeaderFinder};
use Medas\RestRequestHandler\ConfigOptions\UsersClass;

#[Service]
readonly class BearerTokenHandler
{
    public function __construct(
        private BearerTokenValidator|null $validator,
        private HeaderFinder              $headerFinder,
        private MetaDataManager           $metaDataManager,
        private UuidProvider|null         $uuidProvider,

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
        $type = $this->metaDataManager->get($this->usersClass)->idProperty->type;

        if ($type instanceof UuidType) {
            if ($this->uuidProvider === null) {
                throw new UuidProviderIsNotAvailable();
            }

            $userId = $this->uuidProvider->fromString($userId);
        }

        if ($userId) {
            $vote->user = em()->get($this->usersClass, $userId);
        }
    }
}
