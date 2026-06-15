<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{
    Exceptions\ServiceNotFoundByType,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\ArgumentProcessor,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    Types\DateTime,
    Types\Integer
};

readonly class ArgumentDeserializer implements ArgumentProcessor
{
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

        if (is_string($argument) && is_a($name, Uuid::class, true)) {
            try {
                $uuidProvider = service(UuidProvider::class);
            }
            catch (ServiceNotFoundByType) {
                throw new UuidProviderIsNotAvailable();
            }

            $argument = $uuidProvider->fromString($argument);
        }

        if ($type === null) {
            return $argument;
        }

        dispatch($request = new DeserializeRequest($argument, $type));

        return $request->result;
    }
}
