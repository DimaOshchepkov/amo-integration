<?php

namespace App\Http\Controllers;

use App\Exceptions\AmoAuth\AmoCrmOAuthException;
use App\Services\AmoCrm\AmoCrmOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmoCrmOAuthController extends Controller
{
    public function __construct(
        private readonly AmoCrmOAuthService $oauthService,
    ) {}

    public function connect(): RedirectResponse|View
    {
        $existingToken = $this->oauthService->getOAuthToken();

        if ($existingToken && ! $existingToken->hasExpired()) {
            return view('already-connected', [
                'expires' => date('d.m.Y H:i', $existingToken->getExpires()),
            ]);
        }

        $state = bin2hex(random_bytes(16));
        session(['oauth2state' => $state]);

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
            );
        } catch (AmoCrmOAuthException $e) {
            return redirect()->route('home')->with('error', $e->getUserMessage());
        }

        return redirect()
            ->route('home')
            ->with('success', "AmoCRM успешно подключена! Привет, {$owner->getName()}!");
    }
}
