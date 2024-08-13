<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{
    Attributes\PreferredDefault,
    Attributes\Service,
    Interfaces\ArgumentProcessor,
    Interfaces\Serializer
};
use Medas\EntityManager\Types\{DateTime, Integer};

#[Service]
readonly class ArgumentDeserializer implements ArgumentProcessor
{
    public function __construct(
        #[PreferredDefault(JsonSerializer::class)]
        private Serializer $serializer,
    )
    {
    }

    public function priority(): int
    {
        return -50;
    }

    public function process(\ReflectionParameter|\ReflectionProperty $parameter, mixed $argument): mixed
    {
        if (is_object($argument)) {
            return $argument;
        }

        // Should be TypeFinder, but for parameters instead of just properties
        $type = null;
        $name = parameterTypes($parameter)[0]->getName();

        if ($name === \DateTime::class) {
            $type = new DateTime();
        }
        elseif ($name === 'int') {
            $type = new Integer();
        }

        return $type === null ? $argument : $this->serializer->unserialize($argument, $type);
    }
}
