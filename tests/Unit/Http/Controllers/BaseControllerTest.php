<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Exceptions\UnsupportedPlatformException;
use App\Exceptions\UserNotFoundException;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Throwable;

class BaseControllerTest extends TestCase
{
    private BaseController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new class extends BaseController
        {
            public function test_handle_api_exceptions(Throwable $exception): JsonResponse
            {
                return $this->handleApiExceptions($exception);
            }
        };
    }

    /**
     * @throws JsonException
     */
    public function test_handles_unsupported_platform_exception(): void
    {
        $exception = new UnsupportedPlatformException('invalid-platform');
        $response = $this->controller->test_handle_api_exceptions($exception);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $data['code']);
    }

    /**
     * @throws JsonException
     */
    public function test_handles_user_not_found_exception(): void
    {
        $exception = new UserNotFoundException('Steam', 'test-user', 'username');
        $response = $this->controller->test_handle_api_exceptions($exception);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $data['code']);
    }

    /**
     * @throws JsonException
     */
    public function test_handles_invalid_argument_exception(): void
    {
        $exception = new InvalidArgumentException('Invalid parameter');
        $response = $this->controller->test_handle_api_exceptions($exception);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals('Invalid parameter', $data['error']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $data['code']);
    }
}
