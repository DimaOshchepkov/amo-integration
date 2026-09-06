<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

class AmoCrmToken extends Model
{
    protected $fillable = ['access_token', 'refresh_token', 'expires_at', 'subdomain'];

    protected $casts = [
        'expires_at' => 'integer',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    public static function getActiveToken(): ?self
    {
        return self::where('subdomain', config('services.amocrm.subdomain'))
            ->orderBy('id', 'desc')
            ->first();
    }

    public function toAccessToken(): AccessTokenInterface
    {
        return new AccessToken([
            'access_token' => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'expires' => $this->expires_at,
            'baseDomain' => $this->subdomain.'.amocrm.ru',
        ]);
    }
}
