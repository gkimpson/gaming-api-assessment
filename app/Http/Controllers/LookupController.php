<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\UserProfileDto;
use App\Exceptions\PlatformUnavailableException;
use App\Exceptions\UserNotFoundException;
use App\Factories\GamingPlatformLookupFactory;
use App\Http\Requests\LookupRequest;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;

class LookupController extends BaseController
{
    public function __invoke(LookupRequest $request, GamingPlatformLookupFactory $factory): JsonResponse
    {
        try {
            $profile = $this->performUserLookup($request, $factory);

            return response()->json($profile->toArray());
        } catch (Throwable $e) {
            return $this->handleApiExceptions($e);
        }
    }

    /**
     * @throws PlatformUnavailableException
     * @throws UserNotFoundException
     */
    private function performUserLookup(LookupRequest $request, GamingPlatformLookupFactory $factory): UserProfileDto
    {
        $service = $factory->make($request->get('type'));

        if ($username = $request->get('username')) {
            return $service->lookupByUsername($username);
        }

        if ($id = $request->get('id')) {
            return $service->lookupById($id);
        }

        throw new InvalidArgumentException('Either username or id parameter is required');
    }
}
