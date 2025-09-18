<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\GamingPlatform;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GamingPlatformTest extends TestCase
{
    public function test_enum_has_correct_values(): void
    {
        $expectedValues = ['minecraft', 'steam', 'xbl'];

        $this->assertEquals($expectedValues, GamingPlatform::values());
        $this->assertEquals('minecraft,steam,xbl', GamingPlatform::implode(','));
    }

    public function test_from_string_creates_correct_enum(): void
    {
        $this->assertEquals(GamingPlatform::MINECRAFT, GamingPlatform::fromString('minecraft'));
        $this->assertEquals(GamingPlatform::STEAM, GamingPlatform::fromString('steam'));
        $this->assertEquals(GamingPlatform::XBOX_LIVE, GamingPlatform::fromString('xbl'));
    }

    public function test_from_string_throws_exception_for_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'sega-megadrive' is not a valid platform value for enum");

        GamingPlatform::fromString('sega-megadrive');
    }
}
