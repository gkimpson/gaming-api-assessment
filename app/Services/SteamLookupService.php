<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GamingPlatformLookupInterface;
use App\DTOs\UserProfileDto;
use App\Exceptions\PlatformUnavailableException;
use App\Exceptions\UserNotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use JsonException;

/**
 * Steam Lookup Service
 *
 * Handles user profile lookups for the Steam platform via Tebex API (should this not th use Steam API??)
 * Only supports ID-based searches as per Steam platform limitations
 */
readonly class SteamLookupService implements GamingPlatformLookupInterface
{
    public function __construct(
        private Client $httpClient,
        private array $config
    ) {}

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException Steam does not support username searches
     */
    public function lookupByUsername(string $username): UserProfileDto
    {
        throw new InvalidArgumentException('Steam only supports ID-based lookups');
    }

    /**
     * {@inheritDoc}
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function lookupById(string $id): UserProfileDto
    {
        $url = "{$this->config['api_base']}/username/$id";

        try {
            $response = $this->httpClient->get($url, [
                'timeout' => $this->config['timeout'],
            ]);

            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (empty($data) || ! isset($data['username'], $data['id'], $data['meta']['avatar'])) {
                throw new UserNotFoundException('steam', $id, 'id');
            }

            return new UserProfileDto(
                username: $data['username'],
                id: $data['id'],
                avatar: $data['meta']['avatar']
            );
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new UserNotFoundException('steam', $id, 'id');
            }
            throw new PlatformUnavailableException('steam', 'API returned error: '.$e->getMessage());
        } catch (ConnectException) {
            throw new PlatformUnavailableException('steam', 'Connection timeout or network error');
        } catch (RequestException $e) {
            throw new PlatformUnavailableException('steam', 'Request failed: '.$e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supportsUsernameSearch(): bool
    {
        return $this->config['supports_username'];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsIdSearch(): bool
    {
        return $this->config['supports_id'];
    }

    /**
     * {@inheritDoc}
     */
    public function getPlatformName(): string
    {
        return 'steam';
    }
}
