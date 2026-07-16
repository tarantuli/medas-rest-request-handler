<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Handlers;

use Medas\Core\{Attributes\Service, Events\AllowedAccess};
use Medas\EntityManager\Repository;
use Medas\HttpRequestHandler\RequestFactory;
use Medas\RestRequestHandler\{
    Filtering\SelectorBuilder,
    Interfaces\EntityNormalizer,
    Responses\CollectionResponse
};

/**
 * Shared body for a GetCollection route handler: build a selector (a
 * QuerySelector if the request has a ?query= and the entity has one,
 * otherwise the generic SelectorBuilder), fetch + count, filter by
 * per-entity read authorization, normalize, respond.
 *
 * The three things that genuinely differ per entity - the entity class, its
 * optional QuerySelector, and its ReadVote class - are passed in as
 * class-strings. The normalizer is passed as an already-constructed
 * instance rather than a class-string: the calling GetCollection route
 * handler already injects its own Normalizer correctly via DI, so there's
 * no need for this handler to re-resolve it itself.
 */
#[Service]
readonly class GetCollectionHandler
{
    public function __construct(
        private Repository      $repository,
        private RequestFactory  $requestFactory,
        private SelectorBuilder $selectorBuilder,
    )
    {
    }

    /**
     * @param class-string $entityClass
     * @param class-string|null $querySelectorClass null when this entity has no ?query= search support
     * @param class-string $readVoteClass
     */
    public function handle(
        string           $entityClass,
        string|null      $querySelectorClass,
        string           $readVoteClass,
        EntityNormalizer $normalizer,
    ): CollectionResponse
    {
        $filters = $this->requestFactory->get()->uri->query;

        if ($querySelectorClass !== null && array_key_exists('query', $filters)) {
            $selector = service($querySelectorClass);
            $arguments = $filters;
        }
        else {
            $selector = $this->selectorBuilder->build($entityClass, $filters);
            $arguments = [];
        }

        $entities = $this->repository->fetch($selector, $arguments);
        $totalCount = $this->repository->fetchCount($selector, $arguments) ?? count($entities);

        foreach ($entities as $i => $entity) {
            $vote = dispatch(new $readVoteClass($entity));

            if ($vote->allowedAccess !== AllowedAccess::Allowed) {
                unset($entities[$i]);
            }
        }

        $entities = array_values($entities);

        $entities = array_map(
            fn($entity) => $normalizer->normalizeAndSerialize($entity),
            $entities
        );

        return new CollectionResponse($entities, $totalCount);
    }
}
