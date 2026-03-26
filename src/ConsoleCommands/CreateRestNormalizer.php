<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, CommandInput, ConsoleCommandGroup, Range};
use Medas\Core\Attributes\Service;
use Medas\RestRequestHandler\HandlerGenerator\{ClassGenerator, Templates};

#[Service]
readonly class CreateRestNormalizer extends BaseConsoleCommand
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
        return 'create-rest-normalizer';
    }

    public function aliases(): array
    {
        return ['c.rest-normalizer'];
    }

    public function description(): string
    {
        return 'Creates a REST normalizer for the given entity';
    }

    public function allowedArgumentCount(): Range
    {
        return new Range(1);
    }

    public function process(CommandInput $input): void
    {
        $entityClassName = $input->getArgument(1);

        $this->classGenerator->generate(
            $entityClassName,
            $this->templates->entityNormalizer(),
            '',
            'Normalizer',
        );
    }
}
