<?php

namespace VoxDev\Core;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use VoxDev\Core\Helpers\VAuthHelper;

class Core {
     public function redirectUrl(Request $request): string
    {
        $request->session()->put('state', $state = Str::random(40));
        $request->session()->put('code_verifier', $codeVerifier = Str::random(128));

        $codeChallenge = strtr(rtrim(
            base64_encode(hash('sha256', $codeVerifier, true)),
            '='
        ), '+/', '-_');

        $query = http_build_query([
            'client_id' => config('vauth.client_id'),
            'redirect_uri' => config('vauth.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'user:read',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            // 'prompt' => '', // "none", "consent", or "login"
        ]);

        return VAuthHelper::getAuthorizeUrl($query);
    }

    public function getLocations(): array
    {
        $token = VAuthHelper::getValidToken();
        if (!$token) {
            return [];
        }
        $locations = Http::withToken($token)
            ->get(config('vauth.url') . '/api/locations')
            ->json();

        if (is_array($locations)) {
            return $locations;
        }
        return [];
    }
}
