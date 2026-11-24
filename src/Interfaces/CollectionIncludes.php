<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Interfaces;

/**
 * Values a GetCollection route adds to its rows only when the request asks for them
 * by name (?include=a,b): figures that cost a query, such as a count of related
 * records, which a client only needs while it shows them.
 *
 * values() gets the whole page at once, so an implementation can fetch what it needs
 * for all rows in one query rather than one per row.
 */
interface CollectionIncludes
{
    /**
     * The names a request may ask for.
     *
     * @return list<string>
     */
    public function names(): array;

    /**
     * Per row, keyed by the row's id as a string, the value of each name asked for.
     * A row or name left out is sent as null.
     *
     * @param object[]     $entities the page, as the client receives it
     * @param list<string> $names    a subset of names()
     *
     * @return array<string, array<string, mixed>>
     */
    public function values(array $entities, array $names): array;
}
