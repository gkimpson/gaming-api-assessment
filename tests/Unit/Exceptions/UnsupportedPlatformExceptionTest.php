<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\UnsupportedPlatformException;
use PHPUnit\Framework\TestCase;

class UnsupportedPlatformExceptionTest extends TestCase
{
    public function test_exception_message_format_for_single_platform(): void
    {
        $exception = new UnsupportedPlatformException('invalid-platform');

        $expectedMessage = "Gaming platform 'invalid-platform' is not supported. Supported platforms: minecraft, steam, xbl";

        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function test_exception_message_includes_all_supported_platforms(): void
    {
        $exception = new UnsupportedPlatformException('unknown');

        $message = $exception->getMessage();

        $this->assertStringContainsString('minecraft', $message);
        $this->assertStringContainsString('steam', $message);
        $this->assertStringContainsString('xbl', $message);
        $this->assertStringContainsString('Supported platforms:', $message);
    }

    public function test_exception_message_format_with_different_platforms(): void
    {
        $testCases = [
            'nintendo' => "Gaming platform 'nintendo' is not supported. Supported platforms: minecraft, steam, xbl",
            'epic' => "Gaming platform 'epic' is not supported. Supported platforms: minecraft, steam, xbl",
        ];

        foreach ($testCases as $platform => $expectedMessage) {
            $exception = new UnsupportedPlatformException($platform);

            $this->assertEquals($expectedMessage, $exception->getMessage());
        }
    }

    public function test_exception_message_with_empty_platform_name(): void
    {
        $exception = new UnsupportedPlatformException('');

        $expectedMessage = "Gaming platform '' is not supported. Supported platforms: minecraft, steam, xbl";

        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function test_exception_previous_is_null(): void
    {
        $exception = new UnsupportedPlatformException('invalid-platform');

        $this->assertNull($exception->getPrevious());
    }
}
