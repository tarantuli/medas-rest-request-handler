<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\RestRequestHandler\HandlerGenerator\{ClassGenerator, Templates};

#[Service]
readonly class CreateGetCount extends BaseConsoleCommand
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
        return 'create-get-count';
    }

    public function aliases(): array
    {
        return ['c.get-count'];
    }

    public function description(): string
    {
        return 'Creates a GET collection count method for a given entity class';
    }

    public function process(array $arguments): void
    {
        $entityClassName = $arguments[1];

        $this->classGenerator->generate(
            $entityClassName,
            $this->templates->getCollectionCount(),
            'Get',
            'Count',
        );
    }
}
