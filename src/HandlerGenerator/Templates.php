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
use Medas\RestRequestHandler\Responses\{EntityResponse, EntityResponseBuilder};
use Medas\Routing\{Methods\Get, Parameters\Uuid, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityResponseBuilder $entityResponseBuilder,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Get(new Uuid('id'))]
    public function handle(UuidType $id): EntityResponse
    {
        return $this->entityResponseBuilder->build(em()->get(\{{entityClassName}}::class, $id), $this->normalizer);
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

use Medas\RestRequestHandler\Responses\{EntityResponse, EntityResponseBuilder};
use Medas\Routing\{Methods\Get,Parameters\Integer, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityResponseBuilder $entityResponseBuilder,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Get(new Integer('id'))]
    public function handle(int $id): EntityResponse
    {
        return $this->entityResponseBuilder->build(em()->get(\{{entityClassName}}::class, $id), $this->normalizer);
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
use Medas\HttpRequestHandler\RequestDataManager;
use Medas\RestRequestHandler\{
    Requests\RequestDataHandler,
    Responses\EntityResponse,
    Responses\EntityResponseBuilder
};
use Medas\Routing\{Methods\Put, Parameters\Uuid, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityResponseBuilder $entityResponseBuilder,
        private MetaDataManager       $metaDataManager,
        private RequestDataHandler    $requestDataHandler,
        private RequestDataManager    $requestDataManager,
        private ValueSetter           $valueSetter,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Put(new Uuid('id'))]
    public function handle(UuidType $id): EntityResponse
    {
        $entity = em()->get(\{{entityClassName}}::class, $id);
        $metaData = $this->metaDataManager->get(\{{entityClassName}}::class);

        $bodyData = $this->requestDataHandler->deserialize(
            $this->requestDataManager->get()->bodyData->data(),
            $this
        );

        $this->valueSetter->setValues($metaData, $entity, $bodyData);

        em()->persist($entity);
        em()->flush();

        return $this->entityResponseBuilder->build($entity, $this->normalizer);
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
use Medas\HttpRequestHandler\RequestDataManager;
use Medas\RestRequestHandler\{
    Requests\RequestDataHandler,
    Responses\EntityResponse,
    Responses\EntityResponseBuilder
};
use Medas\Routing\{Methods\Put, Parameters\Integer, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityResponseBuilder $entityResponseBuilder,
        private MetaDataManager       $metaDataManager,
        private RequestDataHandler    $requestDataHandler,
        private ValueSetter           $valueSetter,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Put(new Integer('id'))]
    public function handle(int $id): EntityResponse
    {
        $entity = em()->get(\{{entityClassName}}::class, $id);
        $metaData = $this->metaDataManager->get(\{{entityClassName}}::class);

        $data = $this->requestDataHandler->getBodyData($this);

        $this->valueSetter->setValues($metaData, $entity, $data);

        em()->persist($entity);
        em()->flush();

        return $this->entityResponseBuilder->build($entity, $this->normalizer);
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
    Requests\RequestDataHandler,
    Responses\EntityResponse,
    Responses\EntityResponseBuilder
};
use Medas\Routing\{Methods\Post, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private EntityResponseBuilder $entityResponseBuilder,
        private RequestDataHandler    $requestDataHandler,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Post]
    public function handle(): EntityResponse
    {
        $data = $this->requestDataHandler->getBodyData($this);

        try {
            $entity = em()->create(\{{entityClassName}}::class, $data);
        }
        catch (PropertyDoesNotExist $exception) {
            throw new EntityDoesNotHaveProperty(\{{entityClassName}}::class, $exception->propertyName);
        }

        em()->persist($entity);
        em()->flush();

        return $this->entityResponseBuilder->build($entity, $this->normalizer);
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
    Requests\RequestDataHandler,
    Responses\CollectionResponse,
    Responses\CollectionResponseBuilder
};
use Medas\Routing\{Methods\Get, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private CollectionResponseBuilder $collectionResponseBuilder,
        private Repository                $repository,
        private RequestDataHandler        $requestDataHandler,
        private SelectorBuilder           $selectorBuilder,
        private \{{normalizerClassName}} $normalizer,
    )
    {
    }

    #[Get]
    public function handle(): CollectionResponse
    {
        if ($queryData = $this->requestDataHandler->getQueryData()) {
            $selector = $this->selectorBuilder->build(\{{entityClassName}}::class, $queryData);
            $entities = $this->repository->fetch($selector);
        }
        else {
            $entities = $this->repository->fetchAll(\{{entityClassName}}::class);
        }

        return $this->collectionResponseBuilder->build($entities, $this->normalizer);
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
    Requests\RequestDataHandler,
    Responses\ScalarResponse
};
use Medas\Routing\{Methods\Get, Parameters\Constant, Route};

#[Route('{{routePath}}')]
readonly class {{shortClassName}}
{
    public function __construct(
        private Repository                $repository,
        private RequestDataHandler        $requestDataHandler,
        private SelectorBuilder           $selectorBuilder,
    )
    {
    }

    #[Get(new Constant('count'))]
    public function handle(): ScalarResponse
    {
        if ($queryData = $this->requestDataHandler->getQueryData()) {
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

use Medas\Core\{Attributes\Service, Interfaces\HasId, Interfaces\Uuid};
use Medas\RestRequestHandler\Interfaces\Normalizer;

#[Service]
readonly class {{shortClassName}} implements Normalizer
{
    public function normalize(object $entity): array
    {
        /** @var \{{entityClassName}} $entity */
        $properties = get_object_vars($entity);

        foreach ($properties as &$value) {
            if ($value instanceof HasId) {
                $value = $value->id();
            }

            if ($value instanceof Uuid) {
                $value = (string) $value;
            }
        }

        return $properties;
    }
}

PHP;
    }
}
