<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Entities;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Repository;
use Medas\RestRequestHandler\Exceptions\EntityNotFound;

/**
 * Loads the entity a request addresses by id, or fails with a 404.
 *
 * Controllers use this instead of EntityManager::get(), which never checks that
 * the row exists: for an unknown id it returns an entity holding only that id. A
 * read then answers 200 with a nearly empty body, an update is accepted and
 * writes nothing, and anything touching a property fails with a 500.
 */
#[Service]
readonly class EntityFinder
{
    public function __construct(
        private Repository $repository,
    )
    {
    }

    /**
     * Soft-deleted entities are still found, as with get(): whether one may be
     * read is for the caller's authorization to decide.
     *
     * @template T of object
     * @param class-string<T> $className
     *
     * @return T
     */
    public function find(string $className, mixed $id): object
    {
        return $this->repository->fetchById(
            $className,
            $id
        ) ?? throw new EntityNotFound($className, $id);
    }
}
