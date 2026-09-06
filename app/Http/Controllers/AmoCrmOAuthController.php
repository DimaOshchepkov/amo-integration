<?php

namespace App\Http\Controllers;

use App\Exceptions\AmoAuth\AmoCrmOAuthException;
use App\Exceptions\AmoAuth\AuthorizationCodeMissingException;
use App\Exceptions\AmoAuth\InvalidOAuthStateException;
use App\Exceptions\AmoAuth\TokenExchangeException;
use App\Services\AmoCrm\AmoCrmOAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmoCrmOAuthController extends Controller
{
    public function __construct(
        private readonly AmoCrmOAuthService $oauthService,
    ) {}

    public function connect(Request $request): RedirectResponse|View
    {
        $existingToken = $this->oauthService->getOAuthToken();

        if ($existingToken && ! $existingToken->hasExpired()) {
            return view('already-connected', [
                'expires' => date('d.m.Y H:i', $existingToken->getExpires()),
            ]);
        }

        $state = bin2hex(random_bytes(16));
        session(['oauth2state' => $state]);

        if ($request->has('button')) {
            return view('connect-widget', [
                'oauthButton' => $this->oauthService->getOAuthButton([
                    'title' => 'Установить интеграцию',
                    'compact' => true,
                    'class_name' => 'amocrm-oauth-button',
                    'color' => 'default',
                    'error_callback' => 'handleOauthError',
                    'state' => $state,
                ]),
            ]);
        }

        return redirect()->away(
            $this->oauthService->getAuthorizeUrl(['state' => $state])
        );
    }

    public function handleRedirectCallback(Request $request): RedirectResponse
    {
        try {
            $owner = $this->oauthService->completeCallback(
                code: $request->query('code'),
                referer: $request->query('referer'),
                state: $request->query('state'),
                fromWidget: $request->has('from_widget'),
            );
        } catch (AmoCrmOAuthException $e) {
            return redirect()->route('home')->with('error', $e->getUserMessage());
        }

        return redirect()
            ->route('home')
            ->with('success', "AmoCRM успешно подключена! Привет, {$owner->getName()}!");
    }

    public function handleWidgetCallback(Request $request): JsonResponse
    {
        try {
            $owner = $this->oauthService->completeCallback(
                code: $request->input('code'),
                referer: $request->input('referer'),
                state: $request->input('state'),
                fromWidget: $request->has('from_widget'),
            );
        } catch (AmoCrmOAuthException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getUserMessage(),
            ], $this->httpStatus($e));
        }

        return response()->json([
            'success' => true,
            'message' => 'Авторизация успешна!',
            'user' => $owner->getName(),
        ]);
    }

    /**
     * HTTP-статус для доменного исключения — маппинг живёт в HTTP-слое,
     * сами исключения про HTTP ничего не знают.
     */
    private function httpStatus(AmoCrmOAuthException $e): int
    {
        return match (true) {
            $e instanceof InvalidOAuthStateException,
            $e instanceof AuthorizationCodeMissingException => 400,
            $e instanceof TokenExchangeException => 502,
            default => 500,
        };
    }
}
