<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateTradedoublerSettings extends SettingsMigration
{
    public function up(): void
    {
    	$this->migrator->add('tradedoubler.access_token', '');
    	$this->migrator->add('tradedoubler.refresh_token', '');
    	$this->migrator->add('tradedoubler.expires_at', now());
    }
}
