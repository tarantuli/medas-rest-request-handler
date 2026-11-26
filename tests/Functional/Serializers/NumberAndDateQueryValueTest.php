<?php

declare(strict_types=1);

namespace Medas\RestRequestHandlerTest\Functional\Serializers;

use Medas\Core\{Date, Types\Date as DateType, Types\FloatingPoint, Types\Integer};
use Medas\EntityManager\Selector\{
    Conditions\WhereContains,
    Conditions\WhereIn,
    Conditions\WhereIs,
    Conditions\WhereIsAtLeast,
    Operants\Value,
    Operants\Values
};
use Medas\RestRequestHandler\Exceptions\{
    QueryValueIsNotADate,
    QueryValueIsNotAnInteger,
    QueryValueIsNotANumber
};
use Medas\RestRequestHandler\Filtering\FilterParser\ComparisonParser;
use Medas\RestRequestHandler\Serializers\QueryDataSerializer;
use PHPUnit\Framework\{Attributes\DataProvider, TestCase};

// A query value for a number or a date is read as one or refused (#407): it used to be
// cast, so "abc" became 0, or passed on as text for the database to compare as it saw fit
class NumberAndDateQueryValueTest extends TestCase
{
    public static function notIntegers(): array
    {
        return [
            'letters' => ['abc'],
            'trailing letters' => ['12abc'],
            'decimal' => ['1.5'],
            'empty' => [''],
            'too long for an int' => ['1234567890123456789'],
        ];
    }

    public static function notDates(): array
    {
        return [
            'letters' => ['abc'],
            'day first' => ['01-03-2027'],
            'year and month' => ['2027-03'],
            'does not exist' => ['2027-02-30'],
            'with a time' => ['2027-03-01 12:00'],
        ];
    }

    public function testAnIntegerIsRead(): void
    {
        $serializer = service(QueryDataSerializer::class);

        self::assertSame(42, $serializer->unserialize('42', new Integer()));
        self::assertSame(-3, $serializer->unserialize('-3', new Integer()));
    }

    // A filter outside the type's range is a valid question: a count is never below 0
    public function testAnIntegerOutsideTheTypesRangeIsRead(): void
    {
        self::assertSame(-1, service(QueryDataSerializer::class)->unserialize('-1', new Integer()));
    }

    #[DataProvider('notIntegers')]
    public function testAnythingElseForAnIntegerIsRefused(string $value): void
    {
        $this->expectException(QueryValueIsNotAnInteger::class);

        service(QueryDataSerializer::class)->unserialize($value, new Integer());
    }

    public function testANumberIsRead(): void
    {
        self::assertSame(
            1.5,
            service(QueryDataSerializer::class)->unserialize('1.5', new FloatingPoint())
        );
    }

    public function testAnythingElseForANumberIsRefused(): void
    {
        $this->expectException(QueryValueIsNotANumber::class);

        service(QueryDataSerializer::class)->unserialize('abc', new FloatingPoint());
    }

    public function testADateIsReadAsADate(): void
    {
        self::assertEquals(
            new Date(2027, 3, 1),
            service(QueryDataSerializer::class)->unserialize('2027-03-01', new DateType())
        );
    }

    #[DataProvider('notDates')]
    public function testAnythingElseForADateIsRefused(string $value): void
    {
        $this->expectException(QueryValueIsNotADate::class);

        service(QueryDataSerializer::class)->unserialize($value, new DateType());
    }

    public function testNullIsStillNull(): void
    {
        self::assertNull(service(QueryDataSerializer::class)->unserialize('null', new Integer()));
        self::assertNull(service(QueryDataSerializer::class)->unserialize('null', new DateType()));
    }

    // Each value of an "in" list is read the same way
    public function testEachValueOfAListIsRead(): void
    {
        $parser = service(ComparisonParser::class);

        /** @var WhereIn $element */
        $element = $parser->parse('Entity', 'quantity', WhereIn::class, '1,2', new Integer());

        self::assertInstanceOf(Values::class, $element->value);
        self::assertSame([1, 2], $element->value->value);

        $this->expectException(QueryValueIsNotAnInteger::class);

        $parser->parse('Entity', 'quantity', WhereIn::class, '1,x', new Integer());
    }

    public function testAComparisonReadsTheValueWithTheFieldsType(): void
    {
        /** @var WhereIsAtLeast $element */
        $element = service(ComparisonParser::class)->parse(
            'Entity',
            'startDate',
            WhereIsAtLeast::class,
            '2027-03-01',
            new DateType()
        );

        self::assertInstanceOf(Value::class, $element->value);
        self::assertEquals(new Date(2027, 3, 1), $element->value->value);
    }

    // A contains match compares text, so part of a date stays text and is not refused
    public function testATextMatchKeepsTheValueAsText(): void
    {
        /** @var WhereContains $element */
        $element = service(ComparisonParser::class)->parse(
            'Entity',
            'startDate',
            WhereContains::class,
            '2027-03',
            new DateType()
        );

        self::assertSame('2027-03', $element->value->value);
    }

    public function testAnEqualityOnANumberIsStillRefusedForText(): void
    {
        $this->expectException(QueryValueIsNotAnInteger::class);

        service(ComparisonParser::class)->parse(
            'Entity',
            'quantity',
            WhereIs::class,
            'abc',
            new Integer()
        );
    }
}
