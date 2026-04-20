<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, CommandInput, ConsoleCommandGroup, Option, Range};
use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\RestRequestHandler\ConfigOptions\ClassGenerators\{
    CreateCountController,
    CreateDeleteController
};
use Medas\RestRequestHandler\HandlerGenerator\{ClassGenerator, Templates};

#[Service]
readonly class CreateAllControllers extends BaseConsoleCommand
{
    public function __construct(
        private ClassGenerator          $classGenerator,
        private RestRequestHandlerGroup $group,
        private Templates               $templates,

        #[ConfigValue(CreateDeleteController::class)]
        private bool                    $createDeleteController = true,

        #[ConfigValue(CreateCountController::class)]
        private bool                    $createCountController = false,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'create-all-controllers';
    }

    public function aliases(): array
    {
        return ['c.rest-controllers'];
    }

    public function description(): string
    {
        return 'Creates all CRUD controllers for a given entity class';
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

        $this->requestHandlers($entityClassName, $input->hasOption('id'));
        $this->helpers($entityClassName);
        $this->authorization($entityClassName);
    }

    private function requestHandlers(string $entityClassName, bool $useUuid): void
    {
        $replacements = [
            '{{createVoteClassName}}' => $this->classGenerator->generateClassName(
                $entityClassName,
                'Authorization\\Create',
                'Vote',
            ),
            '{{readVoteClassName}}' => $this->classGenerator->generateClassName(
                $entityClassName,
                'Authorization\\Read',
                'Vote',
            ),
            '{{updateVoteClassName}}' => $this->classGenerator->generateClassName(
                $entityClassName,
                'Authorization\\Update',
                'Vote',
            ),
            '{{deleteVoteClassName}}' => $this->classGenerator->generateClassName(
                $entityClassName,
                'Authorization\\Delete',
                'Vote',
            ),
        ];

        $this->classGenerator->generate(
            $entityClassName,
            $useUuid ? $this->templates->getInstanceByUuid() : $this->templates->getInstanceByInteger(),
            'Get',
            'Instance',
            $replacements
        );

        $this->classGenerator->generate(
            $entityClassName,
            $useUuid ? $this->templates->putInstanceByUuid() : $this->templates->putInstanceByInteger(),
            'Put',
            'Instance',
            $replacements
        );

        $this->classGenerator->generate(
            $entityClassName,
            $this->templates->createInstance(),
            'Create',
            'Instance',
            $replacements
        );

        if ($this->createDeleteController) {
            $this->classGenerator->generate(
                $entityClassName,
                $useUuid
                    ? $this->templates->deleteInstanceByUuid()
                    : $this->templates->deleteInstanceByInteger(),
                'Delete',
                'Instance',
                $replacements
            );
        }

        $this->classGenerator->generate(
            $entityClassName,
            $this->templates->getCollection(),
            'Get',
            'Collection',
            $replacements
        );

        if ($this->createCountController) {
            $this->classGenerator->generate(
                $entityClassName,
                $this->templates->getCollectionCount(),
                'Get',
                'Count',
                $replacements
            );
        }
    }

    private function helpers(string $entityClassName): void
    {
        $this->classGenerator->generate(
            $entityClassName,
            $this->templates->entityNormalizer(),
            'Helpers\\',
            'Normalizer',
            isNormalizer: true
        );
    }

    private function authorization(string $entityClassName): void
    {
        $prefixes = [
            'Create' => $this->templates->createVote(),
            'Read' => $this->templates->readDeleteVote(),
            'Update' => $this->templates->updateVote(),
            'Delete' => $this->templates->readDeleteVote(),
        ];

        if (!$this->createDeleteController) {
            unset($prefixes['Delete']);
        }

        foreach ($prefixes as $prefix => $template) {
            $voteClassName = $this->classGenerator->generate(
                $entityClassName,
                $template,
                'Authorization\\' . $prefix,
                'Vote',
            );

            $this->classGenerator->generate(
                $entityClassName,
                $this->templates->crudVoteHandler(),
                'Authorization\\' . $prefix,
                'VoteHandler',
                ['{{voteClassName}}' => $voteClassName]
            );
        }
    }
}
