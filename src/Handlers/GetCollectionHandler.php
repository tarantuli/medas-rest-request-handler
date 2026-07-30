<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Handlers;

use Medas\Core\{Attributes\ConfigValue, Attributes\Service, Events\AllowedAccess};
use Medas\EntityManager\{Repository, Selector\Selector};
use Medas\HttpRequestHandler\RequestFactory;
use Medas\RestRequestHandler\{
    ConfigOptions\SearchQueryName,
    Exceptions\ExtraSelectorHasNoRequiredParameters,
    Filtering\SelectorBuilder,
    Interfaces\EntityNormalizer,
    Responses\CollectionResponse
};

/**
 * Shared body for a GetCollection route handler. Rather than choosing a
 * single filtering source, it composes one selector from all applicable
 * sources: the generic field filters (field comparisons plus page/sort
 * parsed from the residual query), the search QuerySelector (when the
 * request carries the configured search parameter), and any extra selectors
 * (each applied when its own required parameters are present). Their
 * conditions AND together, their parameters and sorts accumulate, and
 * pagination comes from the residual query - so e.g. ?group=X&query=Y&
 * username=Z filters by all three at once instead of silently keeping one.
 *
 * Extra selectors are matched by the parameter names declared in their own
 * Definition, so a caller never repeats a name the selector already owns.
 * The search QuerySelector stays a separate argument because it alone remaps
 * a configurable external parameter name onto its fixed internal 'query'
 * binding; every other selector's external and internal names are identical.
 *
 * $entityClass and $readVoteClass are passed as class-strings, since they're
 * used differently: $entityClass is just a plain reference SelectorBuilder
 * needs, and $readVoteClass is instantiated fresh per entity
 * (new $readVoteClass($entity)) - it can never be a shared instance, since
 * each one wraps a different entity. The selectors and $normalizer, by
 * contrast, are genuine stateless per-entity services - passed in as
 * already-constructed instances (typed against the common Selector/
 * EntityNormalizer interfaces every concrete one implements), so the calling
 * GetCollection route handler's own DI-constructed instances are used
 * directly, rather than this handler resolving them itself via a service
 * locator.
 */
#[Service]
readonly class GetCollectionHandler
{
    public function __construct(
        private Repository                 $repository,
        private RequestFactory             $requestFactory,
        private SelectorBuilder            $selectorBuilder,
        private SelectorRequiredParameters $selectorRequiredParameters,

        #[ConfigValue(SearchQueryName::class)]
        private string                     $searchQueryName,
    )
    {
    }

    /**
     * @param class-string $entityClass
     * @param class-string $readVoteClass
     * @param Selector[]   $extraSelectors
     */
    public function handle(
        string           $entityClass,
        string           $readVoteClass,
        EntityNormalizer $normalizer,
        Selector|null    $querySelector = null,
        array            $extraSelectors = [],
    ): CollectionResponse
    {
        $filters = $this->requestFactory->get()->uri->query;

        // Sources are composed, not chosen: gather every selector that
        // applies, the request-argument bindings they need, and the filter
        // names they consume. Whatever names remain build the generic base;
        // the applicable selectors are then folded into it.
        $applicableSelectors = [];
        $arguments = [];
        $consumedNames = [];

        if ($querySelector !== null && array_key_exists($this->searchQueryName, $filters)) {
            $applicableSelectors[] = $querySelector;
            $consumedNames[] = $this->searchQueryName;

            // Remap the configurable external name onto the fixed internal
            // 'query' key every search QuerySelector's Parameter::c('query')
            // binding expects.
            $arguments['query'] = $filters[$this->searchQueryName];
        }

        foreach ($extraSelectors as $extraSelector) {
            // A selector's required (non-default) parameters are its gate: it
            // applies only when the request supplies all of them. Without at
            // least one required parameter, it would match every request and
            // shadow both the other sources and the generic path, so that is
            // a misconfiguration rather than a valid "always on" selector.
            $requiredParameterNames = $this->selectorRequiredParameters->find($extraSelector);

            if ($requiredParameterNames === []) {
                throw new ExtraSelectorHasNoRequiredParameters($extraSelector);
            }

            if (array_diff($requiredParameterNames, array_keys($filters)) !== []) {
                continue;
            }

            $applicableSelectors[] = $extraSelector;

            // Bind every declared parameter the request supplies; the
            // parameter processor fills defaults for any optional ones left
            // out.
            foreach ($extraSelector->definition()->parameters as $parameter) {
                if (array_key_exists($parameter->name, $filters)) {
                    $consumedNames[] = $parameter->name;
                    $arguments[$parameter->name] = $filters[$parameter->name];
                }
            }
        }

        // The consumed names are removed before the generic builder sees the
        // query; the remaining filters still carry page/perPage/multisort and
        // any plain field comparisons, so the base selector already has
        // pagination and client sorting. (A consumed name like 'group' is not
        // a scalar property and would otherwise make the generic parser throw.)
        $residualFilters = array_diff_key($filters, array_flip($consumedNames));
        $selector = $this->selectorBuilder->build($entityClass, $residualFilters);

        // Fold each applicable selector's conditions, parameters and sorts
        // into the single base Definition. Conditions AND together; sorts
        // append after any client multisort, so a selector's baked-in sort
        // acts as the default ordering when the request specifies none. The
        // slice is left untouched - pagination comes from the residual query
        // alone, so a selector cannot override it.
        foreach ($applicableSelectors as $applicableSelector) {
            $source = $applicableSelector->definition();

            $selector->definition()->add(
                ...$source->conditions,
                ...$source->parameters,
                ...$source->sorts,
            );
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
