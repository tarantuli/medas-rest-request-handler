<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Requests;

use Medas\Core\{Attributes\PreferredDefault, Attributes\Service, Interfaces\Serializer};
use Medas\EntityManager\MetaData;
use Medas\RestRequestHandler\{Exceptions\PropertyCannotBeChanged, Serializers\RestSerializer};

/**
 * Refuses an update that tries to change a property that cannot be changed: one
 * that is writable on create only, or not writable at all. An update normally just
 * skips such fields, which leaves the client believing its change was saved.
 *
 * Run it on the raw request body, before anything is applied, so a refused request
 * changes nothing. A field that holds the value already stored is accepted: a client
 * may send back what it read. Not checked are the id and the timestamps, which a
 * client echoes from a copy that may be older than the row, and body keys that are
 * not properties of the entity, such as computed fields.
 *
 * It first refuses a field the entity does not know at all (UnknownFieldsGuard), so an
 * update gets both checks from this one call.
 */
#[Service]
readonly class FixedPropertiesGuard
{
    public function __construct(
        #[PreferredDefault(RestSerializer::class)]
        private Serializer         $serializer,
        private UnknownFieldsGuard $unknownFieldsGuard,
    )
    {
    }

    /**
     * @param array $body The request body as sent, keyed by field name.
     * @param string[] $handledByController Field names the controller applies
     *     itself, outside the generic writable fields.
     * @param string[] $computedFields Field names the controller's normalizer adds to
     *     its output, which a client may send back. Not compared with the stored value:
     *     the normalizer shows something other than what is stored, also when the name
     *     is a property's.
     */
    public function check(
        MetaData $metaData,
        object   $entity,
        array    $body,
        array    $handledByController = [],
        array    $computedFields = [],
    ): void
    {
        $this->unknownFieldsGuard->check(
            $metaData->className,
            $body,
            [...$handledByController, ...$computedFields]
        );

        foreach ($this->fixedFields($metaData) as $name => $source) {
            $isNotForThisGuard = in_array($name, $handledByController, true)
                || in_array($name, $computedFields, true);

            if (!array_key_exists($name, $body) || $isNotForThisGuard) {
                continue;
            }

            $property = $metaData->property($source, ignoreUnknownProperties: true);

            if ($property === null
                    || $property->isId
                    || $property->isCreationTimestamp
                    || $property->isModificationTimestamp) {
                continue;
            }

            if (!$this->isStoredValue($property, $entity, $body[$name])) {
                throw new PropertyCannotBeChanged($metaData->className, $name);
            }
        }
    }

    /**
     * The fields a client can see or send that an update may not change, as field
     * name => property name. Fields backed by a method are left out: there is no
     * stored value to compare with.
     *
     * @return array<string, string>
     */
    private function fixedFields(MetaData $metaData): array
    {
        $fields = [];
        $updatable = [];

        foreach ($metaData->writableFields as $field) {
            if ($field->onUpdate) {
                $updatable[$field->name] = true;
            }
            elseif (!$field->isMethod) {
                $fields[$field->name] = $field->source;
            }
        }

        foreach ($metaData->readableFields as $field) {
            if (!$field->isMethod) {
                $fields[$field->name] ??= $field->source;
            }
        }

        return array_diff_key($fields, $updatable);
    }

    // Compared as the API shows them: the sent value is read with the property's
    // type and serialized again, the stored value is serialized. Comparing the PHP
    // values would tell two equal dates or collections apart, as they are different
    // objects.
    private function isStoredValue(MetaData\Property $property, object $entity, mixed $sent): bool
    {
        $stored = $property->reflection->isInitialized($entity)
            ? $property->reflection->getValue($entity)
            : null;

        $sent = $this->serializer->unserialize($sent, $property->type);

        return $this->serializer->serialize($sent) === $this->serializer->serialize($stored);
    }
}
