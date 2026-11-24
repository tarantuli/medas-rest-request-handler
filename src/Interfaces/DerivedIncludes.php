<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Interfaces;

use Medas\EntityManager\Selector\DerivedValues\DerivedValue;

/**
 * Includes that are worked out in the list's own query, so a request can also sort and
 * filter on them: ?multisort=>activeSubscriptionCount, activeSubscriptionCount.gte=1.
 *
 * names() stays the one gate: a derived value whose name it does not offer is neither
 * sent nor sortable nor filterable, as for an administrator-only value asked by a
 * partner. values() is asked only for the offered names that are not derived values.
 * A request may sort or filter on a derived value without asking for it.
 */
interface DerivedIncludes extends CollectionIncludes
{
    /**
     * Each named as the include it is, and built by the value's point of authority.
     *
     * @return list<DerivedValue>
     */
    public function derivedValues(): array;
}
