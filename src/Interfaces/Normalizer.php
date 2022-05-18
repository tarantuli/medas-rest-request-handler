<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Interfaces;

interface Normalizer
{
    public function normalize(object $entity): array;
}
