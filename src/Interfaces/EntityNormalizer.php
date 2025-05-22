<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Interfaces;

interface EntityNormalizer
{
    public function normalizeAndSerialize(object $entity): array;

    public function unserializeAndDenormalize(array $data): array;
}
