<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\RestRequestHandler\HandlerGenerator\{ClassGenerator, Templates};

#[Service]
readonly class CreateDeleteInstance extends BaseConsoleCommand
{
    public function __construct(
        private ClassGenerator          $classGenerator,
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
        return 'create-delete-instance';
    }

    public function aliases(): array
    {
        return ['c.delete-instance'];
    }

    public function description(): string
    {
        return 'Creates a DELETE instance method for a given entity class';
    }

    public function process(array $arguments): void
    {
        $entityClassName = $arguments[1];
        $useUuid = ($arguments[2] ?? null) !== '--id';

        $this->classGenerator->generate(
            $entityClassName,
            $useUuid
                ? $this->templates->deleteInstanceByUuid()
                : $this->templates->deleteInstanceByInteger(),
            'Delete',
            '',
        );
    }
}
