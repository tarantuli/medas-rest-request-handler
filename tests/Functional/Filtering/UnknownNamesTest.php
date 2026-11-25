<?php

declare(strict_types=1);

namespace Medas\RestRequestHandlerTest\Functional\Filtering;

use Medas\Core\{Interfaces\BadRequestException, Types\Integer};
use Medas\EntityManager\Selector\{
    Definition,
    DerivedValues\CountOf,
    DerivedValues\DerivedValue,
    Operants\Property,
    Relations\Join,
    Sorting\SortDirection
};
use Medas\RestRequestHandler\{
    Exceptions\CannotParseQueryValue,
    Exceptions\InvalidSortDirection,
    Filtering\SelectorBuilder
};
use Medas\RestRequestHandlerTest\MockUps\Entities\{Author, Book};
use PHPUnit\Framework\{Attributes\DataProvider, TestCase};

/**
 * A sort names a property the same way a filter does, and is refused the same way when
 * it names none (#397): with a 400, before anything reaches the database.
 */
class UnknownNamesTest extends TestCase
{
    public static function unknownNames(): array
    {
        return [
            'not a property' => ['nonsense'],
            'an unknown store' => ['publishers.name'],
            'not a property of a known store' => ['authors.nonsense'],
            'more than one dot' => ['authors.name.first'],
        ];
    }

    #[DataProvider('unknownNames')]
    public function testASortOnAnUnknownNameIsRefused(string $name): void
    {
        $this->expectException(CannotParseQueryValue::class);
        $this->expectExceptionMessage(sprintf('cannot parse query part "multisort" with value "<%s"', $name));

        service(SelectorBuilder::class)->build(Book::class, ['multisort' => '>id,<' . $name]);
    }

    // An unknown store failed with a TypeError (a 500) before the check was shared
    #[DataProvider('unknownNames')]
    public function testAFilterOnAnUnknownNameIsRefused(string $name): void
    {
        $this->expectException(CannotParseQueryValue::class);
        $this->expectExceptionMessage(sprintf('cannot parse query part "%s" with value "1"', $name));

        service(SelectorBuilder::class)->build(Book::class, [$name => '1']);
    }

    public function testTheRefusalIsABadRequest(): void
    {
        try {
            service(SelectorBuilder::class)->build(Book::class, ['multisort' => '<nonsense']);

            self::fail('The sort was not refused');
        }
        catch (CannotParseQueryValue $exception) {
            self::assertInstanceOf(BadRequestException::class, $exception);
        }
    }

    public function testASortWithoutADirectionIsABadRequest(): void
    {
        try {
            service(SelectorBuilder::class)->build(Book::class, ['multisort' => 'name']);

            self::fail('The sort was not refused');
        }
        catch (InvalidSortDirection $exception) {
            self::assertInstanceOf(BadRequestException::class, $exception);
        }
    }

    public function testASortOnAPropertyOfAnotherEntityIsAccepted(): void
    {
        $definition = service(SelectorBuilder::class)
            ->build(Book::class, ['multisort' => '<authors.name'])
            ->definition();

        $operant = $definition->sorts[0]->operant;

        self::assertInstanceOf(Property::class, $operant);
        self::assertSame('name', $operant->name);
        self::assertSame(Author::class, $operant->entity);
        self::assertSame(SortDirection::ASC, $definition->sorts[0]->direction);
        self::assertEquals([Join::c(Author::class, 'author', 'id')], $definition->relations);
    }

    public function testASortOnAPropertyOfItsOwnStoreIsAccepted(): void
    {
        $definition = service(SelectorBuilder::class)
            ->build(Book::class, ['multisort' => '<books.name'])
            ->definition();

        $operant = $definition->sorts[0]->operant;

        self::assertInstanceOf(Property::class, $operant);
        self::assertSame('name', $operant->name);
        self::assertSame(Book::class, $operant->entity);
        self::assertSame([], $definition->relations);
    }

    public function testASortOnADerivedValueIsAccepted(): void
    {
        $bookCount = DerivedValue::c(
            'bookCount',
            CountOf::c(Definition::create(Book::class), 'author'),
            new Integer()
        );

        $definition = service(SelectorBuilder::class)
            ->build(Author::class, ['multisort' => '>bookCount'], ['bookCount' => $bookCount])
            ->definition();

        self::assertSame($bookCount, $definition->sorts[0]->operant);
        self::assertSame(SortDirection::DESC, $definition->sorts[0]->direction);
    }

    // The derived value is only known to the request that offers it
    public function testASortOnADerivedValueThatIsNotOfferedIsRefused(): void
    {
        $this->expectException(CannotParseQueryValue::class);

        service(SelectorBuilder::class)->build(Author::class, ['multisort' => '>bookCount']);
    }
}
