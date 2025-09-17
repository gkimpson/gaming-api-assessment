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
use JsonException;

/**
 * Minecraft Lookup Service
 *
 * Handles user profile lookups for the Minecraft platform
 * Supports both username and UUID-based searches
 */
readonly class MinecraftLookupService implements GamingPlatformLookupInterface
{
    public function __construct(
        private Client $httpClient,
        private array $config
    ) {}

    /**
     * {@inheritDoc}
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function lookupByUsername(string $username): UserProfileDto
    {
        $url = "{$this->config['api_base']}/users/profiles/minecraft/$username";

        try {
            $response = $this->httpClient->get($url, [
                'timeout' => $this->config['timeout'],
            ]);

            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (empty($data) || ! isset($data['name'], $data['id'])) {
                throw new UserNotFoundException('minecraft', $username, 'username');
            }

            return new UserProfileDto(
                username: $data['name'],
                id: $data['id'],
                avatar: $this->config['avatar_base'].$data['id']
            );
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new UserNotFoundException('minecraft', $username, 'username');
            }
            throw new PlatformUnavailableException('minecraft', 'API returned error: '.$e->getMessage());
        } catch (ConnectException) {
            throw new PlatformUnavailableException('minecraft', 'Connection timeout or network error');
        } catch (RequestException $e) {
            throw new PlatformUnavailableException('minecraft', 'Request failed: '.$e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    public function lookupById(string $id): UserProfileDto
    {
        $url = "{$this->config['session_server']}/session/minecraft/profile/$id";

        try {
            $response = $this->httpClient->get($url, [
                'timeout' => $this->config['timeout'],
            ]);

            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (empty($data) || ! isset($data['name'], $data['id'])) {
                throw new UserNotFoundException('minecraft', $id, 'id');
            }

            return new UserProfileDto(
                username: $data['name'],
                id: $data['id'],
                avatar: $this->config['avatar_base'].$data['id']
            );
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new UserNotFoundException('minecraft', $id, 'id');
            }
            throw new PlatformUnavailableException('minecraft', 'API returned error: '.$e->getMessage());
        } catch (ConnectException) {
            throw new PlatformUnavailableException('minecraft', 'Connection timeout or network error');
        } catch (RequestException $e) {
            throw new PlatformUnavailableException('minecraft', 'Request failed: '.$e->getMessage());
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
        return 'minecraft';
    }
}
