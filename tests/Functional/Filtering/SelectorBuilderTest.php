<?php

declare(strict_types=1);

namespace Medas\RestRequestHandlerTest\Functional\Filtering;

use Medas\EntityManager\Selector\{Conditions\WhereIs, Sorting\SortDirection};
use Medas\RestRequestHandler\Filtering\SelectorBuilder;
use Medas\RestRequestHandlerTest\MockUps\Entities\Book;
use PHPUnit\Framework\TestCase;

class SelectorBuilderTest extends TestCase
{
    public function testEmptyFilters(): void
    {
        $selector = service(SelectorBuilder::class)->build(Book::class, []);

        self::assertEquals([], $selector->definition()->conditions);
        self::assertEquals([], $selector->definition()->parameters);
        self::assertEquals([], $selector->definition()->relations);
        self::assertEquals([], $selector->definition()->sorts);
    }

    public function testMultisortFilters(): void
    {
        $selector = service(SelectorBuilder::class)->build(
            Book::class,
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
            Book::class,
            ['page' => 2],
        );

        // The definition holds the page as a slice: the default page size, from the second page on
        self::assertEquals(30, $selector->definition()->slice->count);
        self::assertEquals(30, $selector->definition()->slice->from);
    }

    public function testPropertyFilters(): void
    {
        $selector = service(SelectorBuilder::class)->build(
            Book::class,
            ['name' => 'John'],
        );

        self::assertInstanceOf(WhereIs::class, $selector->definition()->conditions[0]);
    }
}
