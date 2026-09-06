<?php

namespace App\Services\AmoCrm;

use AmoCRM\OAuth\OAuthConfigInterface;

class AmoCrmConfig implements OAuthConfigInterface
{
    public function getIntegrationId(): string
    {
        return (string) config('services.amocrm.client_id');
    }

    public function getSecretKey(): string
    {
        return (string) config('services.amocrm.client_secret');
    }

    public function getRedirectDomain(): string
    {
        return (string) config('services.amocrm.redirect_uri');
    }
}
