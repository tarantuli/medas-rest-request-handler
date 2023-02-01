<?php

declare(strict_types=1);

namespace Medas\RestRequestHandlerTest\Functional\Filtering;

use Medas\EntityManager\Selector\Sorting\SortDirection;
use Medas\RestRequestHandler\Filtering\SelectorBuilder;
use PHPUnit\Framework\TestCase;

class SelectorBuilderTest extends TestCase
{
    public function testEmptyFilters(): void
    {
        $selector = service(SelectorBuilder::class)->build(
            'Entity',
            []
        );

        self::assertEquals([], $selector->get()->conditions);
        self::assertEquals([], $selector->get()->parameters);
        self::assertEquals([], $selector->get()->relations);
        self::assertEquals([], $selector->get()->sorts);
    }

    public function testMultisortFilters(): void
    {
        $selector = service(SelectorBuilder::class)->build(
            'Entity',
            [
                'multisort' => '<name,>id',
            ]
        );

        self::assertEquals([], $selector->get()->conditions);
        self::assertEquals([], $selector->get()->parameters);
        self::assertEquals([], $selector->get()->relations);

        $sorts = $selector->get()->sorts;

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        self::assertEquals('name', $sorts[0]->operant->name);
        self::assertEquals(SortDirection::ASC, $sorts[0]->direction);

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        self::assertEquals('id', $sorts[1]->operant->name);
        self::assertEquals(SortDirection::DESC, $sorts[1]->direction);
    }
}
