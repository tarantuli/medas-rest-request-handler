<?php

declare(strict_types=1);

namespace Medas\RestRequestHandlerTest\Functional\Serializers;

use Medas\Core\Types\Boolean;
use Medas\RestRequestHandler\{
    Exceptions\QueryValueIsNotABoolean,
    Serializers\QueryDataSerializer,
    Serializers\RestSerializer
};
use PHPUnit\Framework\{Attributes\DataProvider, TestCase};

class BooleanSerializationTest extends TestCase
{
    public static function queryBooleans(): array
    {
        return [
            'true' => ['true', true],
            'false' => ['false', false],
            '1' => ['1', true],
            '0' => ['0', false],
        ];
    }

    public static function notQueryBooleans(): array
    {
        return [
            'empty' => [''],
            'yes' => ['yes'],
            'capitalised' => ['True'],
            '2' => ['2'],
        ];
    }

    public function testAResponseServesABooleanAsABoolean(): void
    {
        $serializer = service(RestSerializer::class);

        self::assertTrue($serializer->serialize(true));
        self::assertFalse($serializer->serialize(false));
    }

    public function testABooleanInARequestBodyStaysABoolean(): void
    {
        $serializer = service(RestSerializer::class);

        self::assertTrue($serializer->unserialize(true, new Boolean()));
        self::assertFalse($serializer->unserialize(false, new Boolean()));
    }

    #[DataProvider('queryBooleans')]
    public function testABooleanQueryValueIsRead(string $value, bool $expected): void
    {
        self::assertSame(
            $expected,
            service(QueryDataSerializer::class)->unserialize($value, new Boolean())
        );
    }

    #[DataProvider('notQueryBooleans')]
    public function testAnyOtherBooleanQueryValueIsRefused(string $value): void
    {
        $this->expectException(QueryValueIsNotABoolean::class);

        service(QueryDataSerializer::class)->unserialize($value, new Boolean());
    }
}
