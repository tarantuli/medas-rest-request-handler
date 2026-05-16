<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authentication;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\EventListener,
    Attributes\Service,
    Events\DebugInformation,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\AuthenticationTokenController,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    Types\Uuid as UuidType
};
use Medas\EntityManager\{EntityManager, MetaDataManager};
use Medas\HttpRequestHandler\Authentication\AuthenticationVote;
use Medas\RestRequestHandler\ConfigOptions\UsersClass;

#[Service]
readonly class BearerTokenAuthenticator
{
    public function __construct(
        private AuthenticationTokenController|null $tokenController,
        private BearerTokenHandler                 $tokenHandler,
        private EntityManager                      $entityManager,
        private MetaDataManager                    $metaDataManager,
        private UuidProvider|null                  $uuidProvider,

        #[ConfigValue(UsersClass::class)]
        private string|null                        $usersClass,
    )
    {
    }

    #[EventListener]
    public function validate(AuthenticationVote $vote): void
    {
        if ($this->tokenController === null || $this->usersClass === null) {
            return;
        }

        if (null === $authData = $this->tokenHandler->data($vote->request->serverData)) {
            return;
        }

        $userId = $authData->getUserId();
        $type = $this->metaDataManager->get($this->usersClass)->idProperty->type;

        if ($userId !== null && $type instanceof UuidType && !($userId instanceof Uuid)) {
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
