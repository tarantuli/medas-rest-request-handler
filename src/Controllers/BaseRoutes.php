<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\EntityManager\Exceptions\PropertyDoesNotExist;
use Medas\RestRequestHandler\{
    Exceptions\EntityDoesNotHaveProperty,
    Filtering\SelectorBuilder,
    Responses\CollectionResponse,
    Responses\EntityResponse,
    Responses\ScalarResponse,
    Responses\SuccessResponse
};
use Medas\Routing\{
    Methods\Delete,
    Methods\Get,
    Methods\Post,
    Methods\Put,
    Parameters\Constant,
    Parameters\Integer
};

abstract class BaseRoutes extends BaseController
{
    /**
     * POST /entities
     */
    #[Post]
    public function createEntity(): EntityResponse
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

        return $this->getEntity($entity->id());
    }

    /**
     * GET /entities/:id
     */
    #[Get(new Integer('id'), isEntityEndpoint: true)]
    public function getEntity(int $id): EntityResponse
    {
        return $this->entityResponseBuilder->build(em()->get($this->entityClass, $id), $this);
    }

    /**
     * GET /entities
     */
    #[Get(isCollectionEndpoint: true)]
    public function get(): CollectionResponse
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

    /**
     * GET /entities/count
     */
    #[Get(new Constant('count'))]
    public function getCount(): ScalarResponse
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

    /**
     * PUT /entities/:id
     */
    #[Put(new Integer('id'))]
    public function putEntity(int $id): EntityResponse
    {
        $entity = em()->get($this->entityClass, $id);
        $metaData = $this->metaDataManager->get($this->entityClass);

        $this->valueSetter->setValues($metaData, $entity, $this->bodyData());

        em()->persist($entity);
        em()->flush();

        return $this->getEntity($id);
    }

    /**
     * DELETE /entities/:id
     */
    #[Delete(new Integer('id'))]
    public function removeEntity(int $id): SuccessResponse
    {
        $entity = em()->get($this->entityClass, $id);

        em()->delete($entity);
        em()->flush();

        return new SuccessResponse(true);
    }
}
