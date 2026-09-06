<?php

namespace App\Services\AmoCrm;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\OAuth\OAuthConfigInterface;
use App\Exceptions\AmoAuth\AmoCrmNotAuthorizedException;

class AmoCrmService
{
    private ?AmoCRMApiClient $client = null;

    public function __construct(
        private readonly OAuthConfigInterface $config,
        private readonly AmoCrmOAuthService $oauthService
    ) {}

    /**
     * Авторизованный API-клиент для работы с данными amoCRM.
     *
     * @throws AmoCrmNotAuthorizedException
     */
    public function getClient(): AmoCRMApiClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        if ($this->oauthService->getOAuthToken() === null) {
            throw new AmoCrmNotAuthorizedException;
        }

        $factory = new AmoCRMApiClientFactory($this->config, $this->oauthService);
        $this->client = $factory->make();

        return $this->client;
    }
}
