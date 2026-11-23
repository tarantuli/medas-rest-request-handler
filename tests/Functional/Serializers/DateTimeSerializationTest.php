<?php

declare(strict_types=1);

namespace Medas\RestRequestHandlerTest\Functional\Serializers;

use Medas\Core\Types\DateTime;
use Medas\RestRequestHandler\{Exceptions\InvalidDateTimeFormat, Serializers\QueryDataSerializer};
use PHPUnit\Framework\{Attributes\DataProvider, TestCase};

class DateTimeSerializationTest extends TestCase
{
    public static function notQueryDateTimes(): array
    {
        return [
            'empty' => [''],
            'a day' => ['2026-11-19'],
            'without milliseconds' => ['2026-11-19T00:00:00+01:00'],
            'without offset' => ['2026-11-19T00:00:00.000'],
        ];
    }

    // The offset is kept, so the same moment written in two zones is the same moment.
    public function testADateTimeQueryValueIsReadWithItsOffset(): void
    {
        $serializer = service(QueryDataSerializer::class);
        $amsterdam = $serializer->unserialize('2026-11-19T00:00:00.000+01:00', new DateTime());
        $utc = $serializer->unserialize('2026-11-18T23:00:00.000+00:00', new DateTime());

        self::assertInstanceOf(\DateTime::class, $amsterdam);
        self::assertSame($utc->getTimestamp(), $amsterdam->getTimestamp());
    }

    #[DataProvider('notQueryDateTimes')]
    public function testAnyOtherDateTimeQueryValueIsRefused(string $value): void
    {
        $this->expectException(InvalidDateTimeFormat::class);

        service(QueryDataSerializer::class)->unserialize($value, new DateTime());
    }
}
