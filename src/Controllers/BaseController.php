<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\EntityManager\Hydration\ValueSetter;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Repository;
use Medas\HttpRequestHandler\Request\RequestDataManager;
use Medas\RestRequestHandler\Exceptions\RouteDoesNotSpecifyEntity;
use Medas\RestRequestHandler\Requests\RequestDataHandler;
use Medas\RestRequestHandler\Responses\{CollectionResponseBuilder, EntityResponseBuilder};
use Medas\Routing\Route;

abstract class BaseController
{
    private array $bodyData;
    protected string $entityClass;

    public function __construct(
        protected CollectionResponseBuilder $collectionResponseBuilder,
        protected EntityResponseBuilder     $entityResponseBuilder,
        protected MetaDataManager           $metaDataManager,
        protected Repository                $repository,
        protected RequestDataManager        $requestDataManager,
        protected ValueSetter               $valueSetter,
        protected RequestDataHandler        $requestDataHandler,
    )
    {
        $entity = attribute(Route::class, new \ReflectionClass($this))->endpointForEntity();

        if ($entity === null) {
            throw new RouteDoesNotSpecifyEntity($this);
        }

        $this->entityClass = $entity;
    }

    protected function bodyData(): array
    {
        if (!isset($this->bodyData)) {
            $data = $this->requestDataManager->get();
            $this->bodyData = $this->requestDataHandler->deserialize(
                $data->bodyData->data(),
                $this
            );
        }

        return $this->bodyData;
    }

    protected function queryData(): array
    {
        return $this->requestDataManager->get()->uri->query;
    }
}
