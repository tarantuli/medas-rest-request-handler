<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\RestRequestHandler\Responses\{CollectionResponse, EntityResponse};
use Medas\Routing\Methods\{Delete, Get, Post, Put};
use Medas\Routing\Parameters\Guid;

abstract class BaseGuidRoutes extends BaseController
{
    /**
     * GET /entities
     */
    #[Get(isCollectionEndpoint: true)]
    public function get(): CollectionResponse
    {
        return new CollectionResponse(
            $this->repository->fetchAll($this->entityClass()),
            $this
        );
    }

    /**
     * POST /entities
     */
    #[Post]
    public function createEntity(): EntityResponse
    {
        $entity = $this->entityManager->create($this->entityClass(), $this->requestData());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->getEntity($entity->id());
    }

    /**
     * GET /entities/:id
     */
    #[Get(new Guid('id'), isEntityEndpoint: true)]
    public function getEntity(string $id): EntityResponse
    {
        return new EntityResponse(
            $this->entityManager->get($this->entityClass(), $id),
            $this
        );
    }

    /**
     * PUT /entities/:id
     */
    #[Put(new Guid('id'))]
    public function putEntity(string $id): EntityResponse
    {
        $entity = em()->get($this->entityClass(), $id);
        $metaData = $this->metaDataManager->get($this->entityClass());
        $this->valueSetter->setValues($metaData, $entity, $this->requestData());

        em()->persist($entity);
        em()->flush();

        return $this->getEntity($id);
    }

    /**
     * DELETE /entities/:id
     */
    #[Delete(new Guid('id'))]
    public function removeEntity(string $id): bool
    {
        $entity = em()->get($this->entityClass(), $id);
        $this->entityManager->delete($entity);

        return true;
    }
}
