<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\EntityManager\Entities\Generator\FileNameFinder;
use Medas\RestRequestHandler\HandlerGenerator\{ClassGenerator, Templates};

#[Service]
readonly class CreateGetInstance extends BaseConsoleCommand
{
    public function __construct(
        private RestRequestHandlerGroup $group,
        private FileNameFinder          $fileNameFinder,
        private ClassGenerator          $classGenerator,
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
