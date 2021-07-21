<?php

namespace Eelcol\LaravelTradedoubler\Support\Settings;

use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCasts\DateTimeInterfaceCast;

class TradedoublerSettings extends Settings
{
    public string $access_token;
    
    public string $refresh_token;

    public \DateTime $expires_at;
    
    public static function group(): string
    {
        return 'tradedoubler';
    }

    public static function casts(): array
    {
        return [
            'expires_at' => DateTimeInterfaceCast::class
        ];
    }
}