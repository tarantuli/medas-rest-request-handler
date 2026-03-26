<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, CommandInput, ConsoleCommandGroup, Option, Range};
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

    public function allowedArgumentCount(): Range
    {
        return new Range(1);
    }

    public function options(): array
    {
        return [new Option('id')];
    }

    public function process(CommandInput $input): void
    {
        $entityClassName = $input->getArgument(1);

        $this->classGenerator->generate(
            $entityClassName,
            $input->hasOption('id')
                ? $this->templates->deleteInstanceByUuid()
                : $this->templates->deleteInstanceByInteger(),
            'Delete',
            '',
        );
    }
}
