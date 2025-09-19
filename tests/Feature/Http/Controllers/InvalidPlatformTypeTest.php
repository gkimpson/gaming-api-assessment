<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

class InvalidPlatformTypeTest extends TestCase
{
    public function test_invalid_platform_type_returns_helpful_error_message(): void
    {
        $response = $this->getJson('/lookup?type=invalid-platform&id=123');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type'])
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'type' => [
                        'The selected platform type is invalid. Supported platforms are: minecraft, steam, xbl.',
                    ],
                ],
            ]);
    }
}
