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
use Medas\RestRequestHandler\Responses\EntityResponse;
use Medas\Routing\{Methods\Get, Parameters\Uuid, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Get(new Uuid('id'))]
    public function handle(UuidType $id): EntityResponse
    {
        $entity = em()->get(\{{entityClassName}}::class, $id);
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

use Medas\RestRequestHandler\Responses\EntityResponse;
use Medas\Routing\{Methods\Get, Parameters\Uuid, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Get(new Integer('id'))]
    public function handle(int $id): EntityResponse
    {
        $entity = em()->get(\{{entityClassName}}::class, $id);
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
use Medas\EntityManager\{Hydration\ValueSetter, MetaDataManager};
use Medas\RestRequestHandler\{
    Responses\EntityResponse
};
use Medas\HttpRequestHandler\RequestDataManager;
use Medas\Routing\{Methods\Put, Parameters\Uuid, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private MetaDataManager    $metaDataManager,
        private RequestDataManager $requestDataManager,
        private ValueSetter        $valueSetter,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Put(new Uuid('id'))]
    public function handle(UuidType $id): EntityResponse
    {
        $entity = em()->get(\{{entityClassName}}::class, $id);
        $metaData = $this->metaDataManager->get(\{{entityClassName}}::class);
        $data = $this->requestDataManager->get()->bodyData->data();
        $data = $this->normalizer->unserializeAndDenormalize($data);

        $this->valueSetter->setValues($metaData, $entity, $data);

        em()->persist($entity);
        em()->flush();

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

use Medas\EntityManager\{Hydration\ValueSetter, MetaDataManager};
use Medas\RestRequestHandler\{
    Responses\EntityResponse
};
use Medas\HttpRequestHandler\RequestDataManager;
use Medas\Routing\{Methods\Put, Parameters\Integer, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private MetaDataManager    $metaDataManager,
        private RequestDataManager $requestDataManager,
        private ValueSetter        $valueSetter,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Put(new Integer('id'))]
    public function handle(int $id): EntityResponse
    {
        $entity = em()->get(\{{entityClassName}}::class, $id);
        $metaData = $this->metaDataManager->get(\{{entityClassName}}::class);

        $data = $this->requestDataManager->get()->bodyData->data();
        $data = $this->normalizer->unserializeAndDenormalize($data);

        $this->valueSetter->setValues($metaData, $entity, $data);

        em()->persist($entity);
        em()->flush();

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

use Medas\EntityManager\Exceptions\PropertyDoesNotExist;
use Medas\RestRequestHandler\{
    Exceptions\EntityDoesNotHaveProperty,
    Responses\EntityResponse
};
use Medas\HttpRequestHandler\RequestDataManager;
use Medas\Routing\{Methods\Post, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private RequestDataManager $requestDataManager,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Post]
    public function handle(): EntityResponse
    {
        $data = $this->requestDataManager->get()->bodyData->data();
        $data = $this->normalizer->unserializeAndDenormalize($data);

        try {
            $entity = em()->create(\{{entityClassName}}::class, $data);
        }
        catch (PropertyDoesNotExist $exception) {
            throw new EntityDoesNotHaveProperty(\{{entityClassName}}::class, $exception->propertyName);
        }

        em()->persist($entity);
        em()->flush();

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
use Medas\RestRequestHandler\Responses\SuccessResponse;
use Medas\Routing\{Methods\Delete, Parameters\Uuid, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    #[Delete(new Uuid('id'))]
    public function handle(UuidType $id): SuccessResponse
    {
        $entity = em()->get(\{{entityClassName}}::class, $id);

        em()->delete($entity);
        em()->flush();

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

use Medas\RestRequestHandler\Responses\SuccessResponse;
use Medas\Routing\{Methods\Delete, Parameters\Integer, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    #[Delete(new Integer('id'))]
    public function handle(int $id): SuccessResponse
    {
        $entity = em()->get(\{{entityClassName}}::class, $id);

        em()->delete($entity);
        em()->flush();

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
use Medas\RestRequestHandler\{
    Filtering\SelectorBuilder,
    Responses\CollectionResponse
};
use Medas\HttpRequestHandler\RequestDataManager;
use Medas\Routing\{Methods\Get, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private Repository         $repository,
        private RequestDataManager $requestDataManager,
        private SelectorBuilder    $selectorBuilder,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Get]
    public function handle(): CollectionResponse
    {
        if ($queryData = $this->requestDataManager->get()->uri->query) {
            $selector = $this->selectorBuilder->build(\{{entityClassName}}::class, $queryData);
            $entities = $this->repository->fetch($selector);
        }
        else {
            $entities = $this->repository->fetchAll(\{{entityClassName}}::class);
        }

        array_map(fn ($entity) => $this->normalizer->normalizeAndSerialize($entity), $entities);

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
use Medas\HttpRequestHandler\RequestDataManager;
use Medas\Routing\{Methods\Get, Parameters\Constant, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private Repository         $repository,
        private RequestDataManager $requestDataManager,
        private SelectorBuilder    $selectorBuilder,
    )
    {
    }

    #[Get(new Constant('count'))]
    public function handle(): ScalarResponse
    {
        if ($queryData = $this->requestDataManager->get()->uri->query) {
            $selector = $this->selectorBuilder->build(\{{entityClassName}}::class, $queryData);
            $count = $this->repository->fetchCount($selector);
        }
        else {
            $count = $this->repository->fetchCount(new AllEntities(\{{entityClassName}}::class));
        }

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

        array_walk($data, fn($value) => $this->serializer->serialize($value));

        return $data;
    }

    public function unserializeAndDenormalize(array $data): array
    {
        array_walk($data, fn($value) => $this->serializer->unserialize($value));

        return $data;
    }
}

PHP;
    }
}
