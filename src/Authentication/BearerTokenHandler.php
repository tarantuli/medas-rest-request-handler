<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authentication;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\EventListener,
    Attributes\Service,
    Events\DebugInformation,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\BearerTokenValidator,
    Interfaces\UuidProvider,
    Types\Uuid as UuidType
};
use Medas\EntityManager\{EntityManager, MetaDataManager};
use Medas\HttpRequestHandler\{Authentication\AuthenticationVote, Request\HeaderFinder};
use Medas\RestRequestHandler\ConfigOptions\UsersClass;

#[Service]
readonly class BearerTokenHandler
{
    public function __construct(
        private BearerTokenValidator|null $validator,
        private EntityManager             $entityManager,
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
            dispatch(new DebugInformation('[bearer-token] Authorization header not found'));

            return;
        }

        if (!str_starts_with($header, 'Bearer ')) {
            dispatch(new DebugInformation('[bearer-token] Authorization header does not start with "Bearer"'));

            return;
        }

        $token = substr($header, 7);
        $userId = $this->validator->userId($token);
        $type = $this->metaDataManager->get($this->usersClass)->idProperty->type;

        if ($userId !== null && $type instanceof UuidType) {
            if ($this->uuidProvider === null) {
                throw new UuidProviderIsNotAvailable();
            }

            $userId = $this->uuidProvider->fromString($userId);
        }

        if ($userId) {
            dispatch(new DebugInformation('[bearer-token] found user %s', $userId));

            $vote->user = $this->entityManager->get($this->usersClass, $userId);
        }
        else {
            dispatch(new DebugInformation('[bearer-token] no user found with id "%s"', $userId));
        }
    }
}
