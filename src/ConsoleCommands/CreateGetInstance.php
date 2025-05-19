<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\EntityManager\Entities\Generator\{ClassNameNormalizer, FileNameFinder};
use Medas\RestRequestHandler\HandlerGenerator\{ClassGenerator, Templates};

#[Service]
readonly class CreateGetInstance extends BaseConsoleCommand
{
    public function __construct(
        private ClassGenerator          $classGenerator,
        private ClassNameNormalizer     $classNameNormalizer,
        private FileNameFinder          $fileNameFinder,
        private RestRequestHandlerGroup $group,
        private Templates               $templates,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'create-get-instance';
    }

    public function aliases(): array
    {
        return ['c.get-instance'];
    }

    public function description(): string
    {
        return 'Creates a GET instance method for a given entity class';
    }

    public function process(array $arguments): void
    {
        $entityClassName = $arguments[1];
        $entityClassName = $this->classNameNormalizer->normalize($entityClassName);
        $useUuid = ($arguments[2] ?? null) !== '--id';

        if (!preg_match('#^/(\w+)(/.+)?/(\w+)$#', $entityClassName, $match)) {
            exit($entityClassName . ' is not a valid entity class name');
        }

        $handlerClassName = '/'
            . $match[1]
            . '/RestControllers.'
            . ($match[2] ?? '')
            . '/Get'
            . $match[3];

        $code = $this->classGenerator->generate(
            $entityClassName,
            $handlerClassName,
            $useUuid ? $this->templates->getInstanceByUuid() : $this->templates->getInstanceByInteger()
        );

        $fileName = $this->fileNameFinder->find($entityClassName);

        $this->fileNameFinder->writeToFile($code, $fileName);
    }
}
