<?php

declare(strict_types=1);

namespace Medas\RestRequestHandlerTest\Functional\Filtering;

use Medas\EntityManager\Selector\Conditions\WhereIs;
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

        self::assertEquals([], $selector->definition()->conditions);
        self::assertEquals([], $selector->definition()->parameters);
        self::assertEquals([], $selector->definition()->relations);
        self::assertEquals([], $selector->definition()->sorts);
    }

    public function testMultisortFilters(): void
    {
        $selector = service(SelectorBuilder::class)->build(
            'Entity',
            ['multisort' => '<name,>id']
        );

        self::assertEquals([], $selector->definition()->conditions);
        self::assertEquals([], $selector->definition()->parameters);
        self::assertEquals([], $selector->definition()->relations);

        $sorts = $selector->definition()->sorts;

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        self::assertEquals('name', $sorts[0]->operant->name);
        self::assertEquals(SortDirection::ASC, $sorts[0]->direction);

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        self::assertEquals('id', $sorts[1]->operant->name);
        self::assertEquals(SortDirection::DESC, $sorts[1]->direction);
    }

    public function testOffsetLimitFilters(): void
    {
        $selector = service(SelectorBuilder::class)->build(
            'Entity',
            ['page' => 1],
        );

        self::assertEquals(1, $selector->definition()->pagination->page);
        self::assertEquals(30, $selector->definition()->pagination->perPage);
    }

    public function testPropertyFilters(): void
    {
        $selector = service(SelectorBuilder::class)->build(
            'Entity',
            ['name' => 'John'],
        );

        self::assertInstanceOf(WhereIs::class, $selector->definition()->conditions[0]);
    }
}
