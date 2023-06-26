<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Interfaces;

interface Denormalizer
{
    public function denormalize(array $data): array;
}
