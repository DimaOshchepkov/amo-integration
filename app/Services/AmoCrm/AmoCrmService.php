<?php

namespace App\Services\AmoCrm;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\OAuth\OAuthConfigInterface;
use App\Exceptions\AmoAuth\AmoCrmNotAuthorizedException;
use League\OAuth2\Client\Token\AccessToken;

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

        $token = $this->oauthService->getOAuthToken();

        if ($token === null) {
            throw new AmoCrmNotAuthorizedException;
        }

        /** @var AccessToken $token */
        $client = (new AmoCRMApiClientFactory($this->config, $this->oauthService))->make();

        $client->setAccessToken($token)
            ->setAccountBaseDomain(
                (string) ($token->getValues()['baseDomain'] ?? config('services.amocrm.subdomain').'.amocrm.ru')
            );

        return $this->client = $client;
    }
}
