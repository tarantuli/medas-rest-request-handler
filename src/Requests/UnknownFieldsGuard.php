<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Requests;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaDataManager;
use Medas\RestRequestHandler\Exceptions\UnknownField;

/**
 * Refuses a create or update whose body holds a field the entity does not know. Such a
 * field used to be skipped, which left the client believing it was stored.
 *
 * Run it on the raw request body, before anything is applied. Known are the entity's
 * readable and writable fields, computed ones included, so a client may send back a copy
 * it read; whether a change to one of them is allowed is FixedPropertiesGuard's question.
 * Known as well are the fields given in $alsoAccepted: those a controller applies itself,
 * and those a normalizer adds to its output.
 */
#[Service]
readonly class UnknownFieldsGuard
{
    public function __construct(
        private MetaDataManager $metaDataManager,
    )
    {
    }

    /**
     * @param class-string $className
     * @param array $body The request body as sent, keyed by field name.
     * @param string[] $alsoAccepted Field names that are not the entity's but are expected.
     */
    public function check(string $className, array $body, array $alsoAccepted = []): void
    {
        $metaData = $this->metaDataManager->get($className);
        $known = array_fill_keys($alsoAccepted, true);

        foreach ([...$metaData->readableFields, ...$metaData->writableFields] as $field) {
            $known[$field->name] = true;
        }

        foreach (array_keys($body) as $name) {
            if (!isset($known[$name])) {
                throw new UnknownField($className, (string) $name);
            }
        }
    }
}
