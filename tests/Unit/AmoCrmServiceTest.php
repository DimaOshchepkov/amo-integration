<?php

use AmoCRM\OAuth\OAuthConfigInterface;
use App\Exceptions\AmoAuth\AmoCrmNotAuthorizedException;
use App\Services\AmoCrm\AmoCrmOAuthService;
use App\Services\AmoCrm\AmoCrmService;
use League\OAuth2\Client\Token\AccessToken;
use Tests\TestCase;

uses(TestCase::class);

test('getClient returns authorized client with token and base domain', function () {
    $token = new AccessToken([
        'access_token' => 'token-123',
        'refresh_token' => 'refresh-123',
        'expires' => time() + 3600,
        'baseDomain' => 'testsub.amocrm.ru',
    ]);

    $oauth = Mockery::mock(AmoCrmOAuthService::class);
    $oauth->shouldReceive('getOAuthToken')->once()->andReturn($token);

    $config = Mockery::mock(OAuthConfigInterface::class);
    $config->shouldReceive('getIntegrationId')->andReturn('uuid');
    $config->shouldReceive('getSecretKey')->andReturn('secret');
    $config->shouldReceive('getRedirectDomain')->andReturn('https://example.com/amocrm/callback');

    $service = new AmoCrmService($config, $oauth);

    $client = $service->getClient();

    expect($client->isAccessTokenSet())->toBeTrue()
        ->and($client->getAccountBaseDomain())->toBe('testsub.amocrm.ru')
        ->and($service->getClient())->toBe($client);
});

test('getClient throws when no token exists', function () {
    $oauth = Mockery::mock(AmoCrmOAuthService::class);
    $oauth->shouldReceive('getOAuthToken')->once()->andReturnNull();

    $config = Mockery::mock(OAuthConfigInterface::class);

    $service = new AmoCrmService($config, $oauth);

    expect(fn () => $service->getClient())->toThrow(AmoCrmNotAuthorizedException::class);
});
