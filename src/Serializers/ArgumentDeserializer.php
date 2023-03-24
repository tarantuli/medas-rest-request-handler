<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\EntityManager\Types\TypeFinder;
use Medas\ServiceManager\Attributes\PreferredDefault;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Interfaces\Serializer;
use Medas\ServiceManager\ParameterResolving\ArgumentProcessor;

#[Service]
class ArgumentDeserializer implements ArgumentProcessor
{
    public function __construct(
        #[PreferredDefault(JsonSerializer::class)]
        private readonly Serializer $serializer,

        private readonly TypeFinder $typeFinder,
    )
    {
    }

    public function priority(): int
    {
        return -50;
    }

    public function process(\ReflectionParameter|\ReflectionProperty $parameter, mixed $argument): mixed
    {
        return $this->serializer->unserialize($argument, $this->typeFinder->find($parameter));
    }
}
