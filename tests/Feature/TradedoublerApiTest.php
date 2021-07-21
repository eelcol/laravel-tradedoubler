<?php

namespace Eelcol\LaravelTradedoubler\Tests\Feature;

use Carbon\Carbon;
use Eelcol\LaravelTradedoubler\Support\Facades\Tradedoubler;
use Eelcol\LaravelTradedoubler\Support\Settings\TradedoublerSettings;
use Eelcol\LaravelTradedoubler\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use function json_encode;

class TradedoublerApiTest extends TestCase
{
    use RefreshDatabase;

	/** @test */
	function it_should_request_a_bearer_token()
    {
        $this->setConfig();

        TradedoublerSettings::fake([
            'expires_at' => now(),
            'access_token' => '',
            'refresh_token' => '',
        ]);

        // now request transactions
        Tradedoubler::getTransactions(now());

        // an access token should be acquired
        $authToken = base64_encode("client_id:client_secret");
        Http::assertSent(function (Request $request) use ($authToken) {
            return $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded') &&
                $request->hasHeader('Authorization', 'Basic ' . $authToken) &&
                $request->url() == 'https://connect.tradedoubler.com/uaa/oauth/token' &&
                $request['grant_type'] == 'password' &&
                $request['username'] == 'username' &&
                $request['password'] == 'password';
        });
	}

    /** @test */
    function it_should_request_a_refresh_token()
    {
        $this->setConfig();

        TradedoublerSettings::fake([
            'expires_at' => now()->subSeconds(100),
            'access_token' => 'token-a',
            'refresh_token' => 'token-r',
        ]);

        // now request transactions
        Tradedoubler::getTransactions(now());

        // a refresh token should be acquired
        $authToken = base64_encode("client_id:client_secret");
        Http::assertSent(function (Request $request) use ($authToken) {
            return $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded') &&
                $request->hasHeader('Authorization', 'Basic ' . $authToken) &&
                $request->url() == 'https://connect.tradedoubler.com/uaa/oauth/token' &&
                $request['grant_type'] == 'refresh_token' &&
                $request['refresh_token'] == 'token-r';
        });
    }

    /** @test */
    function it_should_get_transactions()
    {
        $this->setConfig();

        $startDate = now()->subDays(7);
        $endDate = now();

        TradedoublerSettings::fake([
            'expires_at' => now()->addSeconds(100),
            'access_token' => 'token-a',
            'refresh_token' => 'token-r',
        ]);

        // now request transactions
        Tradedoubler::getTransactions($startDate, $endDate);

        // check the request
        Http::assertSent(function (Request $request) use ($startDate, $endDate) {
            return $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded') &&
                $request->hasHeader('Authorization', 'Bearer token-a') &&
                $request->url() == 'https://connect.tradedoubler.com/publisher/report/transactions?fromDate='.$startDate->format('Ymd').'&toDate='.$endDate->format('Ymd').'&limit=100';
        });
    }

    protected function setConfig()
    {
        Config::set('tradedoubler', [
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'username' => 'username',
            'password' => 'password'
        ]);

        // include Spatie settings config
        Config::set('settings', File::getRequire(dirname(__FILE__) . "/../config/settings.php"));

        Http::fake(function ($request) {
            if ($request->url() == 'https://connect.tradedoubler.com/uaa/oauth/token') {
                return Http::response(json_encode(
                    ['access_token' => 'token-a', 'refresh_token' => 'token-r', 'expires_in' => 90]
                ), 200);
            }

            // mock all other requests too
            return Http::response(json_encode(['status' => 'OK']), 200);
        });
    }
}