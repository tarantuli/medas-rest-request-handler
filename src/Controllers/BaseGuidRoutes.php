<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\Core\Interfaces\Guid as GuidType;
use Medas\EntityManager\Exceptions\PropertyDoesNotExist;
use Medas\RestRequestHandler\Exceptions\EntityDoesNotHaveProperty;
use Medas\RestRequestHandler\Filtering\SelectorBuilder;
use Medas\RestRequestHandler\Responses\{CollectionResponse, EntityResponse, SuccessResponse};
use Medas\Routing\Methods\{Delete, Get, Post, Put};
use Medas\Routing\Parameters\Guid;

abstract class BaseGuidRoutes extends BaseController
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
    #[Get(new Guid('id'), isEntityEndpoint: true)]
    public function getEntity(GuidType $id): EntityResponse
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
     * PUT /entities/:id
     */
    #[Put(new Guid('id'))]
    public function putEntity(GuidType $id): EntityResponse
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
    #[Delete(new Guid('id'))]
    public function removeEntity(GuidType $id): SuccessResponse
    {
        $entity = em()->get($this->entityClass, $id);
        em()->delete($entity);
        em()->flush();

        return new SuccessResponse(true);
    }
}
