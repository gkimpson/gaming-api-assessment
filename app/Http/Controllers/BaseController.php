<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\PlatformUnavailableException;
use App\Exceptions\UnsupportedPlatformException;
use App\Exceptions\UserNotFoundException;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Base Controller
 *
 * Provides common functionality for controllers
 */
abstract class BaseController extends Controller
{
    /**
     * Handle common API exceptions and return formatted JSON responses
     *
     * @param  Throwable  $exception  The exception to handle
     * @return JsonResponse Formatted JSON error response
     */
    protected function handleApiExceptions(Throwable $exception): JsonResponse
    {
        $statusMap = [
            UnsupportedPlatformException::class => Response::HTTP_BAD_REQUEST,
            UserNotFoundException::class => Response::HTTP_NOT_FOUND,
            PlatformUnavailableException::class => Response::HTTP_SERVICE_UNAVAILABLE,
            InvalidArgumentException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        ];

        $status = $statusMap[get_class($exception)] ?? Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json([
            'error' => $exception->getMessage(),
            'code' => $status,
        ], $status);
    }
}
