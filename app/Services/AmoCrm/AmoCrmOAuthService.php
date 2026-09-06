<?php

namespace App\Services\AmoCrm;

use AmoCRM\OAuth\OAuthServiceInterface;
use App\Models\AmoCrmToken;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Illuminate\Support\Facades\Log;

class AmoCrmOAuthService implements OAuthServiceInterface
{
    public function getOAuthToken(): ?AccessTokenInterface
    {
        $tokenRecord = AmoCrmToken::getActiveToken();

        if (!$tokenRecord) {
            return null;
        }

        return new AccessToken([
            'access_token' => $tokenRecord->access_token,
            'refresh_token' => $tokenRecord->refresh_token,
            'expires' => $tokenRecord->expires_at,
            'baseDomain' => $tokenRecord->subdomain . '.amocrm.ru',
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

        Log::info('AmoCRM токен автоматически обновлен и сохранен через OAuthService', [
            'subdomain' => $subdomain,
            'new_expires_at' => $accessToken->getExpires(),
        ]);
    }
}
