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

abstract readonly class AbstractGamingPlatformLookupService implements GamingPlatformLookupInterface
{
    public function __construct(
        protected Client $httpClient,
        protected array $config
    ) {}

    /**
     * {@inheritDoc}
     *
     * Lookup user by username
     *
     * @throws GuzzleException
     * @throws JsonException
     * @throws PlatformUnavailableException
     * @throws UserNotFoundException
     */
    public function lookupByUsername(string $username): UserProfileDto
    {
        $this->validateLookupSupport('username');
        $url = $this->buildUsernameUrl($username);

        return $this->executeLookup($url, $username, 'username');
    }

    /**
     * {@inheritDoc}
     *
     * Lookup user by id
     *
     * @throws GuzzleException
     * @throws JsonException
     * @throws PlatformUnavailableException
     * @throws UserNotFoundException
     */
    public function lookupById(string $id): UserProfileDto
    {
        $this->validateLookupSupport('id');
        $url = $this->buildIdUrl($id);

        return $this->executeLookup($url, $id, 'id');
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
     * Validate that the platform supports the requested lookup type
     *
     * @param  string  $type  The lookup type ('username' or 'id')
     *
     * @throws InvalidArgumentException If the platform doesn't support the lookup type
     */
    private function validateLookupSupport(string $type): void
    {
        if ($type === 'username' && ! $this->supportsUsernameSearch()) {
            throw new InvalidArgumentException(
                "{$this->getPlatformName()} does not support username lookups"
            );
        }

        if ($type === 'id' && ! $this->supportsIdSearch()) {
            throw new InvalidArgumentException(
                "{$this->getPlatformName()} does not support ID lookups"
            );
        }
    }

    /**
     * Execute HTTP lookup request and process response
     *
     * @param  string  $url  The API endpoint URL to call
     * @param  string  $identifier  The username or ID being looked up
     * @param  string  $type  The type of lookup ('username' or 'id')
     * @return UserProfileDto The user profile data
     *
     * @throws GuzzleException
     * @throws JsonException
     * @throws PlatformUnavailableException
     * @throws UserNotFoundException
     */
    protected function executeLookup(string $url, string $identifier, string $type): UserProfileDto
    {
        try {
            $response = $this->httpClient->get($url, [
                'timeout' => $this->config['timeout'],
            ]);

            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (! $this->isValidResponse($data)) {
                throw new UserNotFoundException($this->getPlatformName(), $identifier, $type);
            }

            return $this->createUserProfileDto($data);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new UserNotFoundException($this->getPlatformName(), $identifier, $type);
            }
            throw new PlatformUnavailableException($this->getPlatformName(), 'API returned error: '.$e->getMessage());
        } catch (ConnectException) {
            throw new PlatformUnavailableException($this->getPlatformName(), 'Connection timeout or network error');
        } catch (RequestException $e) {
            throw new PlatformUnavailableException($this->getPlatformName(), 'Request failed: '.$e->getMessage());
        }
    }

    /**
     * Build URL for username lookup
     *
     * @param  string  $username  The username to lookup
     * @return string The constructed API URL
     */
    abstract protected function buildUsernameUrl(string $username): string;

    /**
     * Build URL for ID lookup
     *
     * @param  string  $id  The user ID to lookup
     * @return string The constructed API URL
     */
    abstract protected function buildIdUrl(string $id): string;

    /**
     * Validate API response data
     *
     * @param  array<string, mixed>  $data  The response data from API
     * @return bool True if response contains valid user data
     */
    abstract protected function isValidResponse(array $data): bool;

    /**
     * Create UserProfileDto from API response
     *
     * @param  array<string, mixed>  $data  The validated response data
     * @return UserProfileDto The constructed user profile
     */
    abstract protected function createUserProfileDto(array $data): UserProfileDto;

    /**
     * Get the platform identifier name
     *
     * @return string The platform name
     */
    abstract public function getPlatformName(): string;
}
