<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Handlers;

use Medas\Core\{Attributes\ConfigValue, Attributes\Service, Events\AllowedAccess};
use Medas\EntityManager\{Repository, Selector\Selector};
use Medas\HttpRequestHandler\RequestFactory;
use Medas\RestRequestHandler\{
    ConfigOptions\SearchQueryName,
    Filtering\SelectorBuilder,
    Interfaces\EntityNormalizer,
    Responses\CollectionResponse
};

/**
 * Shared body for a GetCollection route handler: build a selector (the
 * given QuerySelector if the request has a ?query= and one was passed in,
 * otherwise the generic SelectorBuilder), fetch and count, filter by
 * per-entity read authorization, normalize, respond.
 *
 * $entityClass and $readVoteClass are passed as class-strings, since they're
 * used differently: $entityClass is just a plain reference SelectorBuilder
 * needs, and $readVoteClass is instantiated fresh per entity
 * (new $readVoteClass($entity)) - it can never be a shared instance, since
 * each one wraps a different entity. $querySelector and $normalizer, by
 * contrast, are genuine stateless per-entity services - passed in as
 * already-constructed instances (typed against the common Selector/
 * EntityNormalizer interfaces every concrete one implements) so the calling
 * GetCollection route handler's own DI-constructed instances are used
 * directly, rather than this handler resolving them itself via a service
 * locator.
 */
#[Service]
readonly class GetCollectionHandler
{
    public function __construct(
        private Repository      $repository,
        private RequestFactory  $requestFactory,
        private SelectorBuilder $selectorBuilder,

        #[ConfigValue(SearchQueryName::class)]
        private string          $searchQueryName,
    )
    {
    }

    /**
     * @param class-string $entityClass
     * @param class-string $readVoteClass
     */
    public function handle(
        string           $entityClass,
        string           $readVoteClass,
        EntityNormalizer $normalizer,
        Selector|null    $querySelector = null,
    ): CollectionResponse
    {
        $filters = $this->requestFactory->get()->uri->query;

        if ($querySelector !== null && array_key_exists($this->searchQueryName, $filters)) {
            $selector = $querySelector;

            // Remaps the configurable external parameter name to the fixed
            // internal 'query' key every QuerySelector's own Parameter::c()
            // binding expects - so SearchQueryName only affects what the
            // HTTP query parameter is called, without requiring every
            // entity's QuerySelector to also know about the configured name.
            $arguments = ['query' => $filters[$this->searchQueryName]];
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
