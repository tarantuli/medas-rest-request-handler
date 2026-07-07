<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\Core\Interfaces\HasTotalCount;
use Medas\HttpRequestHandler\ResponseTypes\JsonResponse;

readonly class CollectionResponse implements JsonResponse, HasTotalCount
{
    public function __construct(
        private array    $entitiesData,
        private int|null $totalCount = null,
    )
    {
    }

    public function getJsonResponse(): array
    {
        return $this->entitiesData;
    }

    /**
     * Falls back to the number of entities actually returned when no explicit
     * count was given - correct as long as the collection isn't paginated;
     * pass the real count explicitly (e.g., via Repository::fetchCount())
     * once pagination is in play, since the returned page is then smaller
     * than the true total.
     */
    public function totalCount(): int
    {
        return $this->totalCount ?? count($this->entitiesData);
    }
}
