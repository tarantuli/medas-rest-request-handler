<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\EntityManager\Hydration\ValueSetter;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Repository;
use Medas\HttpRequestHandler\Request\RequestDataManager;
use Medas\RestRequestHandler\Exceptions\RouteDoesNotSpecifyEntity;
use Medas\RestRequestHandler\Responses\{CollectionResponseBuilder, EntityResponseBuilder};
use Medas\Routing\Route;

abstract class BaseController
{
    private array $requestData;
    protected string $entityClass;

    public function __construct(
        protected CollectionResponseBuilder $collectionResponseBuilder,
        protected EntityResponseBuilder     $entityResponseBuilder,
        protected MetaDataManager           $metaDataManager,
        protected Repository                $repository,
        protected RequestDataManager        $requestDataManager,
        protected ValueSetter               $valueSetter,
    )
    {
        $entity = attribute(Route::class, new \ReflectionClass($this))->endpointForEntity();

        if ($entity === null) {
            throw new RouteDoesNotSpecifyEntity($this);
        }

        $this->entityClass = $entity;
    }

    protected function requestData(): array
    {
        if (!isset($this->requestData)) {
            $data = $this->requestDataManager->get();
            $this->requestData = array_merge(
                $data->postData->data(),
                $data->bodyData->data(),
                $data->fileData->data(),
            );
        }

        return $this->requestData;
    }

    protected function queryData(): array
    {
        return $this->requestDataManager->get()->uri->query;
    }
}
