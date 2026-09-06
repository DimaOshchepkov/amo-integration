<?php

namespace App\Http\Controllers;

use AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\OAuth\OAuthConfigInterface;
use AmoCRM\OAuth\OAuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AmoCrmOAuthController extends Controller
{
    public function __construct(
        private readonly OAuthConfigInterface $config,
        private readonly OAuthServiceInterface $oauthService
    ) {}

    public function connect(Request $request)
    {
        $existingToken = $this->oauthService->getOAuthToken();

        if ($existingToken && ! $existingToken->hasExpired()) {
            return view('amocrm.already-connected', [
                'expires' => date('d.m.Y H:i', $existingToken->getExpires()),
            ]);
        }

        $state = bin2hex(random_bytes(16));
        session(['oauth2state' => $state]);

        $factory = new AmoCRMApiClientFactory($this->config, $this->oauthService);
        $apiClient = $factory->make();

        if ($request->has('button')) {
            $oauthButton = $apiClient->getOAuthClient()->getOAuthButton([
                'title' => 'Установить интеграцию',
                'compact' => true,
                'class_name' => 'amocrm-oauth-button',
                'color' => 'default',
                'error_callback' => 'handleOauthError',
                'state' => $state,
            ]);

            return view('amocrm.connect-widget', [
                'oauthButton' => $oauthButton,
            ]);
        }

        $authorizationUrl = $apiClient->getOAuthClient()->getAuthorizeUrl([
            'state' => $state,
            'mode' => 'post_message',
        ]);

        return redirect()->away($authorizationUrl);
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');
        $referer = $request->input('referer');

        session()->forget('oauth2state');
        if (empty($state) || $state !== session('oauth2state')) {
            Log::warning('AmoCRM OAuth: Invalid state', ['state' => $state]);

            return response('Invalid state parameter', 400);
        }

        if (! $code) {
            Log::error('AmoCRM OAuth: Code not received');

            return response('Authorization code not received', 400);
        }

        try {
            $factory = new AmoCRMApiClientFactory($this->config, $this->oauthService);
            $apiClient = $factory->make();

            if ($referer) {
                $apiClient->setAccountBaseDomain($referer);
            }

            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($code);

            if (! $accessToken->hasExpired()) {
                $ownerDetails = $apiClient->getOAuthClient()->getResourceOwner($accessToken);

                Log::info('AmoCRM OAuth: Успешная авторизация', [
                    'user' => $ownerDetails->getName(),
                    'email' => $ownerDetails->getEmail(),
                ]);

                if ($request->isMethod('post') || $request->has('from_widget')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Авторизация успешна!',
                        'user' => $ownerDetails->getName(),
                    ]);
                }

                return redirect()
                    ->route('dashboard')
                    ->with('success', "AmoCRM успешно подключена! Привет, {$ownerDetails->getName()}!");
            }

            throw new \Exception('Token has expired immediately after receiving');
        } catch (\Exception $e) {
            Log::error('AmoCRM OAuth error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->isMethod('post') || $request->has('from_widget')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ошибка авторизации: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->route('amocrm.connect')
                ->withErrors(['oauth' => 'Ошибка авторизации: '.$e->getMessage()]);
        }
    }
}
