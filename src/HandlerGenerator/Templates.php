<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\HandlerGenerator;

use Medas\Core\Attributes\Service;

#[Service]
readonly class Templates
{
    public function getInstanceByUuid(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\Core\Interfaces\Uuid as UuidType;
use Medas\EntityManager\EntityManager;
use Medas\HttpRequestHandler\Exceptions\RequestNotAuthorized;
use Medas\RestRequestHandler\Responses\EntityResponse;
use Medas\Routing\{Methods\Get, Parameters\Uuid, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityManager $entityManager,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Get(new Uuid('id'))]
    public function handle(UuidType $id): EntityResponse
    {
        $entity = $this->entityManager->get(\{{entityClassName}}::class, $id);

        allowElseThrow(
            $vote = new \{{readVoteClassName}}($entity),
            new RequestNotAuthorized($vote->allowedAccess)
        );

        $data = $this->normalizer->normalizeAndSerialize($entity);

        return new EntityResponse($data);
    }
}

PHP;
    }

    public function getInstanceByInteger(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\EntityManager\EntityManager;
use Medas\HttpRequestHandler\Exceptions\RequestNotAuthorized;
use Medas\RestRequestHandler\Responses\EntityResponse;
use Medas\Routing\{Methods\Get, Parameters\Integer, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityManager $entityManager,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Get(new Integer('id'))]
    public function handle(int $id): EntityResponse
    {
        $entity = $this->entityManager->get(\{{entityClassName}}::class, $id);

        allowElseThrow(
            $vote = new \{{readVoteClassName}}($entity),
            new RequestNotAuthorized($vote->allowedAccess)
        );

        $data = $this->normalizer->normalizeAndSerialize($entity);

        return new EntityResponse($data);
    }
}

PHP;
    }

    public function putInstanceByUuid(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\Core\Interfaces\Uuid as UuidType;
use Medas\EntityManager\{EntityManager, Hydration\ValueSetter, MetaDataManager};
use Medas\HttpRequestHandler\{Exceptions\RequestNotAuthorized, RequestFactory};
use Medas\RestRequestHandler\Responses\EntityResponse;
use Medas\Routing\{Methods\Put, Parameters\Uuid, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityManager $entityManager,
        private MetaDataManager $metaDataManager,
        private RequestFactory $requestFactory,
        private ValueSetter $valueSetter,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Put(new Uuid('id'))]
    public function handle(UuidType $id): EntityResponse
    {
        $entity = $this->entityManager->get(\{{entityClassName}}::class, $id);

        allowElseThrow(
            $vote = new \{{readVoteClassName}}($entity),
            new RequestNotAuthorized($vote->allowedAccess)
        );

        $metaData = $this->metaDataManager->get(\{{entityClassName}}::class);
        $data = $this->requestFactory->get()->bodyData->data();
        $data = $this->normalizer->unserializeAndDenormalize($data);

        allowElseThrow(
            $vote = new \{{updateVoteClassName}}($entity, $data),
            new RequestNotAuthorized($vote->allowedAccess)
        );

        $this->valueSetter->setValues($metaData, $entity, $data);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $data = $this->normalizer->normalizeAndSerialize($entity);

        return new EntityResponse($data);
    }
}

PHP;
    }

    public function putInstanceByInteger(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\EntityManager\{EntityManager, Hydration\ValueSetter, MetaDataManager};
use Medas\HttpRequestHandler\{Exceptions\RequestNotAuthorized, RequestFactory};
use Medas\RestRequestHandler\Responses\EntityResponse;
use Medas\Routing\{Methods\Put, Parameters\Integer, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityManager $entityManager,
        private MetaDataManager $metaDataManager,
        private RequestFactory $requestFactory,
        private ValueSetter $valueSetter,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Put(new Integer('id'))]
    public function handle(int $id): EntityResponse
    {
        $entity = $this->entityManager->get(\{{entityClassName}}::class, $id);

        allowElseThrow(
            $vote = new \{{readVoteClassName}}($entity),
            new RequestNotAuthorized($vote->allowedAccess)
        );

        $metaData = $this->metaDataManager->get(\{{entityClassName}}::class);

        $data = $this->requestFactory->get()->bodyData->data();
        $data = $this->normalizer->unserializeAndDenormalize($data);

        allowElseThrow(
            $vote = new \{{updateVoteClassName}}($entity, $data),
            new RequestNotAuthorized($vote->allowedAccess)
        );

        $this->valueSetter->setValues($metaData, $entity, $data);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $data = $this->normalizer->normalizeAndSerialize($entity);

        return new EntityResponse($data);
    }
}

PHP;
    }

    public function createInstance(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\EntityManager\{EntityManager, Exceptions\PropertyDoesNotExist};
use Medas\HttpRequestHandler\{Exceptions\RequestNotAuthorized, RequestFactory};
use Medas\RestRequestHandler\{
    Exceptions\EntityDoesNotHaveProperty,
    Responses\EntityResponse
};
use Medas\Routing\{Methods\Post, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityManager$entityManager,
        private RequestFactory $requestFactory,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Post]
    public function handle(): EntityResponse
    {
        $data = $this->requestFactory->get()->bodyData->data();
        $data = $this->normalizer->unserializeAndDenormalize($data);

        allowElseThrow(
            $vote = new \{{createVoteClassName}}($data),
            new RequestNotAuthorized($vote->allowedAccess)
        );

        try {
            $entity = $this->entityManager->create(\{{entityClassName}}::class, $data);
        }
        catch (PropertyDoesNotExist $exception) {
            throw new EntityDoesNotHaveProperty(\{{entityClassName}}::class, $exception->propertyName);
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $data = $this->normalizer->normalizeAndSerialize($entity);

        return new EntityResponse($data);
    }
}

PHP;
    }

    public function deleteInstanceByUuid(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\Core\Interfaces\Uuid as UuidType;
use Medas\EntityManager\EntityManager;
use Medas\HttpRequestHandler\Exceptions\RequestNotAuthorized;
use Medas\RestRequestHandler\Responses\SuccessResponse;
use Medas\Routing\{Methods\Delete, Parameters\Uuid, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityManager $entityManager,
    )
    {
    }

    #[Delete(new Uuid('id'))]
    public function handle(UuidType $id): SuccessResponse
    {
        $entity = $this->entityManager->get(\{{entityClassName}}::class, $id);

        allowElseThrow(
            $vote = new \{{deleteVoteClassName}}($entity),
            new RequestNotAuthorized($vote->allowedAccess)
        );

        $this->entityManager->delete($entity);
        $this->entityManager->flush();

        return new SuccessResponse(true);
    }
}

PHP;
    }

    public function deleteInstanceByInteger(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\EntityManager\EntityManager;
use Medas\HttpRequestHandler\Exceptions\RequestNotAuthorized;
use Medas\RestRequestHandler\Responses\SuccessResponse;
use Medas\Routing\{Methods\Delete, Parameters\Integer, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityManager $entityManager,
    )
    {
    }

    #[Delete(new Integer('id'))]
    public function handle(int $id): SuccessResponse
    {
        $entity = $this->entityManager->get(\{{entityClassName}}::class, $id);

        allowElseThrow(
            $vote = new \{{deleteVoteClassName}}($entity),
            new RequestNotAuthorized($vote->allowedAccess)
        );

        $this->entityManager->delete($entity);
        $this->entityManager->flush();

        return new SuccessResponse(true);
    }
}

PHP;
    }

    public function getCollection(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\EntityManager\Repository;
use Medas\HttpRequestHandler\{Exceptions\RequestNotAuthorized, RequestFactory};
use Medas\RestRequestHandler\{
    Filtering\SelectorBuilder,
    Responses\CollectionResponse
};
use Medas\Routing\{Methods\Get, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private Repository $repository,
        private RequestFactory $requestFactory,
        private SelectorBuilder $selectorBuilder,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Get]
    public function handle(): CollectionResponse
    {
        $selector = $this->selectorBuilder->build(
            \{{entityClassName}}::class, 
            $this->requestFactory->get()->uri->query
        );
        
        /** @var \{{entityClassName}}[] $entities */
        $entities = $this->repository->fetch($selector);

        foreach ($entities as $entity) {
            allowElseThrow(
                $vote = new \{{readVoteClassName}}($entity),
                new RequestNotAuthorized($vote->allowedAccess)
            );
        }

        $entities = array_map(
            fn($entity) => $this->normalizer->normalizeAndSerialize($entity),
            $entities
        );
        return new CollectionResponse($entities);
    }
}

PHP;
    }

    public function getCollectionCount(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\EntityManager\Repository;
use Medas\EntityManager\Selector\Selectors\AllEntities;
use Medas\RestRequestHandler\{
    Filtering\SelectorBuilder,
    Responses\ScalarResponse
};
use Medas\HttpRequestHandler\RequestFactory;
use Medas\Routing\{Methods\Get, Parameters\Constant, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private Repository $repository,
        private RequestFactory $requestFactory,
        private SelectorBuilder $selectorBuilder,
    )
    {
    }

    #[Get(new Constant('count'))]
    public function handle(): ScalarResponse
    {
        $selector = $this->selectorBuilder->build(\{{entityClassName}}::class, $this->requestFactory->get()->uri->query);
        $count = $this->repository->fetchCount($selector);

        return new ScalarResponse($count);
    }
}

PHP;
    }

    public function entityNormalizer(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\Core\{Attributes\PreferredDefault, Attributes\Service, Interfaces\Serializer};
use Medas\RestRequestHandler\{
    Interfaces\EntityNormalizer,
    Serializers\RestSerializer
};

#[Service]
readonly class {{shortClassName}} implements EntityNormalizer
{
    public function __construct(
        #[PreferredDefault(RestSerializer::class)]
        private Serializer $serializer,
    )
    {
    }

    public function normalizeAndSerialize(object $entity): array
    {
        /** @var \{{entityClassName}} $entity */
        $data = get_object_vars($entity);

        array_walk($data, function (&$value) {
            $value = $this->serializer->serialize($value);
        });

        return $data;
    }

    public function unserializeAndDenormalize(array $data): array
    {
        array_walk($data, function (&$value) {
            $value = $this->serializer->unserialize($value);
        });

        return $data;
    }
}

PHP;
    }

    public function readDeleteVote(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\Core\Events\BasicVote;

class {{shortClassName}} extends BasicVote
{
    public function __construct(
        public \{{entityClassName}} {{instanceVariable}},
    )
    {
    }
}
PHP;
    }

    public function createVote(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\Core\Events\BasicVote;

class {{shortClassName}} extends BasicVote
{
    public function __construct(
        public array $data,
    )
    {
    }
}
PHP;
    }

    public function updateVote(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\Core\Events\BasicVote;

class {{shortClassName}} extends BasicVote
{
    public function __construct(
        public \{{entityClassName}} {{instanceVariable}},
        public array $data,
    )
    {
    }
}
PHP;
    }

    public function crudVoteHandler(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\Core\Attributes\{EventListener, Service};

#[Service]
readonly class {{shortClassName}}
{
    #[EventListener]
    public function handle(\{{voteClassName}} $vote): void
    {
    }
}
PHP;
    }
}
