<?php

namespace App\Services;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\OAuth\OAuthConfigInterface;
use AmoCRM\OAuth\OAuthServiceInterface;
use App\Exceptions\AmoCrmNotAuthorizedException;
use Exception;

class AmoCrmService
{
    private ?AmoCRMApiClient $client = null;

    public function __construct(
        private readonly OAuthConfigInterface $config,
        private readonly OAuthServiceInterface $oauthService
    ) {}


    /**
     * @throws AmoCrmNotAuthorizedException
     */
    public function getClient(): AmoCRMApiClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        if ($this->oauthService->getOAuthToken() === null) {
            throw new AmoCrmNotAuthorizedException();
        }

        $factory = new AmoCRMApiClientFactory($this->config, $this->oauthService);
        $this->client = $factory->make();

        return $this->client;
    }
}
