<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\EntityManager\{
    Exceptions\PropertyDoesNotExist,
    Hydration\ValueSetter,
    MetaDataManager,
    Repository
};
use Medas\HttpRequestHandler\Request\RequestDataManager;
use Medas\RestRequestHandler\{
    Exceptions\EntityDoesNotHaveProperty,
    Exceptions\RouteDoesNotSpecifyEntity,
    Filtering\SelectorBuilder,
    Requests\RequestDataHandler,
    Responses\CollectionResponse,
    Responses\CollectionResponseBuilder,
    Responses\EntityResponse,
    Responses\EntityResponseBuilder,
    Responses\ScalarResponse,
    Responses\SuccessResponse
};
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

    protected function _createEntity(): EntityResponse
    {
        return $this->createEntityFromData($this->bodyData());
    }

    protected function createEntityFromData(array $data): EntityResponse
    {
        try {
            $entity = em()->create($this->entityClass, $data);
        }
        catch (PropertyDoesNotExist $exception) {
            throw new EntityDoesNotHaveProperty($this->entityClass, $exception->propertyName);
        }

        em()->persist($entity);
        em()->flush();

        return $this->_getEntity($entity->id());
    }

    protected function _getEntity(mixed $id): EntityResponse
    {
        return $this->entityResponseBuilder->build(em()->get($this->entityClass, $id), $this);
    }

    protected function bodyData(): array
    {
        if (!isset($this->bodyData)) {
            $this->bodyData = $this->requestDataHandler->deserialize(
                $this->requestDataManager->get()->bodyData->data(),
                $this
            );
        }

        return $this->bodyData;
    }

    protected function _get(): CollectionResponse
    {
        if ($queryData = $this->queryData()) {
            $selectorBuilder = service(SelectorBuilder::class);
            $selector = $selectorBuilder->build($this->entityClass, $queryData);
            $entities = $this->repository->fetch($selector);
        }
        else {
            $entities = $this->repository->fetchAll($this->entityClass);
        }

        return $this->collectionResponseBuilder->build($entities, $this);
    }

    protected function queryData(): array
    {
        return $this->requestDataManager->get()->uri->query;
    }

    protected function _getCount(): ScalarResponse
    {
        if ($queryData = $this->queryData()) {
            $selectorBuilder = service(SelectorBuilder::class);
            $selector = $selectorBuilder->build($this->entityClass, $queryData);
            $count = $this->repository->fetchCount($selector);
        }
        else {
            $count = $this->repository->fetchCount($this->entityClass);
        }

        return new ScalarResponse($count);
    }

    protected function _putEntity(mixed $id): EntityResponse
    {
        $entity = em()->get($this->entityClass, $id);
        $metaData = $this->metaDataManager->get($this->entityClass);

        $this->valueSetter->setValues($metaData, $entity, $this->bodyData());

        em()->persist($entity);
        em()->flush();

        return $this->_getEntity($id);
    }

    protected function _removeEntity(mixed $id): SuccessResponse
    {
        $entity = em()->get($this->entityClass, $id);

        em()->delete($entity);
        em()->flush();

        return new SuccessResponse(true);
    }
}
