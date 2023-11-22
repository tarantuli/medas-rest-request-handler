<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\RestRequestHandler\Responses\{CollectionResponse, EntityResponse};
use Medas\Routing\{Methods\Delete, Methods\Get, Methods\Post, Methods\Put, Parameters\Integer};

abstract class BaseRoutes extends BaseController
{
    /**
     * GET /entities
     */
    #[Get(isCollectionEndpoint: true)]
    public function get(): CollectionResponse
    {
        return $this->collectionResponseBuilder->build($this->repository->fetchAll($this->entityClass), $this);
    }

    /**
     * POST /entities
     */
    #[Post]
    public function createEntity(): EntityResponse
    {
        $entity = em()->create($this->entityClass, $this->bodyData());

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
    public function removeEntity(int $id): bool
    {
        $entity = em()->get($this->entityClass, $id);

        em()->delete($entity);
        em()->flush();

        return true;
    }
}
