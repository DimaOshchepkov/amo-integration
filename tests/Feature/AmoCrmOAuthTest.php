<?php

use AmoCRM\OAuth\AmoCRMOAuth;
use AmoCRM\OAuth2\Client\Provider\AmoCRMResourceOwner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use League\OAuth2\Client\Token\AccessToken;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.amocrm.subdomain', 'testsub');
});

function validAccessToken(): AccessToken
{
    return new AccessToken([
        'access_token' => 'test-access-token',
        'refresh_token' => 'test-refresh-token',
        'expires' => time() + 3600,
    ]);
}

test('connect redirects to authorize url and stores state when no token exists', function () {
    $response = get(route('amocrm.connect'));

    $response->assertRedirect();
    $response->assertSessionHas('oauth2state');
});

test('connect with button renders widget view', function () {
    $response = get(route('amocrm.connect', ['button' => 1]));

    $response->assertOk();
    $response->assertViewIs('connect-widget');
});

test('redirect callback completes oauth and redirects home with success flash', function () {
    $oauthClient = Mockery::mock(AmoCRMOAuth::class);
    $oauthClient->shouldReceive('getAccessTokenByCode')
        ->once()
        ->with('test-code')
        ->andReturn(validAccessToken());
    $oauthClient->shouldReceive('getResourceOwner')
        ->once()
        ->andReturn(new AmoCRMResourceOwner(['id' => 1, 'name' => 'Иван']));

    $this->instance(AmoCRMOAuth::class, $oauthClient);

    $response = $this->withSession(['oauth2state' => 'expected-state'])
        ->get(route('amocrm.callback', ['code' => 'test-code', 'state' => 'expected-state']));

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('success', fn (string $message) => str_contains($message, 'Иван'));
    $this->assertDatabaseHas('amo_crm_tokens', ['subdomain' => 'testsub']);
});

test('redirect callback with invalid state redirects home with error flash', function () {
    $response = $this->withSession(['oauth2state' => 'expected-state'])
        ->get(route('amocrm.callback', ['code' => 'test-code', 'state' => 'bad-state']));

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('error', 'Неверный параметр безопасности');
});

test('redirect callback without code redirects home with error flash', function () {
    $response = $this->withSession(['oauth2state' => 'expected-state'])
        ->get(route('amocrm.callback', ['state' => 'expected-state']));

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('error', 'Код авторизации не получен');
});

test('widget callback failure returns json without accept header', function () {
    $oauthClient = Mockery::mock(AmoCRMOAuth::class);
    $oauthClient->shouldReceive('setBaseDomain')
        ->once()
        ->with('widget.amocrm.ru')
        ->andReturnSelf();
    $oauthClient->shouldReceive('getAccessTokenByCode')
        ->once()
        ->with('test-code')
        ->andThrow(new Exception('boom'));

    $this->instance(AmoCRMOAuth::class, $oauthClient);

    $response = post(route('amocrm.callback.widget'), [
        'code' => 'test-code',
        'referer' => 'widget.amocrm.ru',
        'from_widget' => 1,
    ]);

    $response->assertStatus(502);
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertJson([
        'success' => false,
        'error' => 'Не удалось получить токен доступа',
    ]);
});

test('widget callback completes oauth without session state', function () {
    $oauthClient = Mockery::mock(AmoCRMOAuth::class);
    $oauthClient->shouldReceive('setBaseDomain')
        ->once()
        ->with('widget.amocrm.ru')
        ->andReturnSelf();
    $oauthClient->shouldReceive('getAccessTokenByCode')
        ->once()
        ->with('test-code')
        ->andReturn(validAccessToken());
    $oauthClient->shouldReceive('getResourceOwner')
        ->once()
        ->andReturn(new AmoCRMResourceOwner(['id' => 1, 'name' => 'Иван']));

    $this->instance(AmoCRMOAuth::class, $oauthClient);

    $response = post(route('amocrm.callback.widget'), [
        'code' => 'test-code',
        'referer' => 'widget.amocrm.ru',
        'from_widget' => 1,
    ]);

    $response->assertJson([
        'success' => true,
        'user' => 'Иван',
    ]);
    $this->assertDatabaseHas('amo_crm_tokens', ['subdomain' => 'widget']);
});
