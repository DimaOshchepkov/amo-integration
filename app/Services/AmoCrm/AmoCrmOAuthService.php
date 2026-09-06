<?php

namespace App\Services\AmoCrm;

use AmoCRM\OAuth\AmoCRMOAuth;
use AmoCRM\OAuth\OAuthConfigInterface;
use AmoCRM\OAuth\OAuthServiceInterface;
use AmoCRM\OAuth2\Client\Provider\AmoCRMResourceOwner;
use App\Exceptions\AmoAuth\AuthorizationCodeMissingException;
use App\Exceptions\AmoAuth\InvalidOAuthStateException;
use App\Exceptions\AmoAuth\TokenExchangeException;
use App\Models\AmoCrmToken;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Throwable;

class AmoCrmOAuthService implements OAuthServiceInterface
{
    public function __construct(
        private readonly OAuthConfigInterface $config,
        private ?AmoCRMOAuth $oauthClient = null,
    ) {}

    public function getOAuthToken(): ?AccessTokenInterface
    {
        $tokenRecord = AmoCrmToken::getActiveToken();

        if (! $tokenRecord) {
            return null;
        }

        return new AccessToken([
            'access_token' => $tokenRecord->access_token,
            'refresh_token' => $tokenRecord->refresh_token,
            'expires' => $tokenRecord->expires_at,
            'baseDomain' => $tokenRecord->subdomain.'.amocrm.ru',
        ]);
    }

    public function saveOAuthToken(AccessTokenInterface $accessToken, string $baseDomain): void
    {
        $subdomain = str_replace('.amocrm.ru', '', $baseDomain);

        AmoCrmToken::updateOrCreate(
            ['subdomain' => $subdomain],
            [
                'access_token' => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'expires_at' => $accessToken->getExpires(),
            ]
        );

        Log::info('AmoCRM токен сохранен через OAuthService', [
            'subdomain' => $subdomain,
            'new_expires_at' => $accessToken->getExpires(),
        ]);
    }

    /** @param array<string, mixed> $options */
    public function getAuthorizeUrl(array $options = []): string
    {
        return $this->oauthClient()->getAuthorizeUrl($options);
    }

    /**
     * Завершает OAuth-колбэк: проверяет state, обменивает код на токен,
     * сохраняет токен и возвращает владельца аккаунта.
     * @throws AuthorizationCodeMissingException
     * @throws TokenExchangeException
     * @throws InvalidOAuthStateException
     */
    public function completeCallback(
        ?string $code,
        ?string $referer,
        ?string $state,
    ): AmoCRMResourceOwner {
        $sessionState = session('oauth2state');

        if (empty($state) || empty($sessionState) || $state !== $sessionState) {
            session()->forget('oauth2state');

            throw new InvalidOAuthStateException('State mismatch');
        }

        session()->forget('oauth2state');

        if (! $code) {
            throw new AuthorizationCodeMissingException('Code is empty');
        }

        $accessToken = $this->exchangeCodeForToken($code, $referer);

        /** @var AmoCRMResourceOwner $owner */
        $owner = $this->oauthClient()->getResourceOwner($accessToken);

        return $owner;
    }

    /**
     * @throws TokenExchangeException
     */
    private function exchangeCodeForToken(string $code, ?string $referer): AccessTokenInterface
    {
        $baseDomain = $referer
            ? $this->normalizeBaseDomain($referer)
            : (string) config('services.amocrm.subdomain').'.amocrm.ru';

        $client = $this->oauthClient();

        if ($referer) {
            $client->setBaseDomain($baseDomain);
        }

        try {
            $accessToken = $client->getAccessTokenByCode($code);
        } catch (Throwable $e) {
            throw new TokenExchangeException('Token exchange failed: '.$e->getMessage(), $e);
        }

        if ($accessToken->hasExpired()) {
            throw new TokenExchangeException('Token expired immediately');
        }

        $this->saveOAuthToken($accessToken, $baseDomain);

        return $accessToken;
    }

    private function normalizeBaseDomain(string $referer): string
    {
        $host = parse_url($referer, PHP_URL_HOST);

        return rtrim(is_string($host) && $host !== '' ? $host : $referer, '/');
    }

    private function oauthClient(): AmoCRMOAuth
    {
        return $this->oauthClient ??= new AmoCRMOAuth(
            $this->config->getIntegrationId(),
            $this->config->getSecretKey(),
            $this->config->getRedirectDomain(),
        );
    }
}
