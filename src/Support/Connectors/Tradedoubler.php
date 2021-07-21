<?php

namespace Eelcol\LaravelTradedoubler\Support\Connectors;

use Carbon\Carbon;
use Eelcol\LaravelTradedoubler\Exceptions\TradedoublerBadCredentials;
use Eelcol\LaravelTradedoubler\Support\Settings\TradedoublerSettings;
use Illuminate\Support\Facades\Http;
use function http_build_query;

class Tradedoubler
{
    protected array $data;

    protected TradedoublerSettings $settings;

    public function __construct(array $data)
    {
        $this->data = $data;

        $this->settings = app(TradedoublerSettings::class);
    }

    public function getTransactions(Carbon $startDate, ?Carbon $endDate = null)
    {
        if (is_null($endDate)) {
            $endDate = $startDate;
        }

        return $this->request(
            'GET',
            'publisher/report/transactions',
            [
                'fromDate' => $startDate->format('Ymd'),
                'toDate' => $endDate->format('Ymd'),
                'limit' => 100,
            ]
        );
    }

    public function get(string $path, array $params = [])
    {
        return $this->request(
            'GET',
            $path,
            $params
        );
    }

    protected function getToken()
    {
        if ($this->settings->expires_at < now()) {
            if (!$this->settings->access_token) {
                // generate a new bearer token
                $this->getBearerToken();
            } else {
                $this->refreshToken();
            }
        }

        return $this->settings->access_token;
    }

    protected function request(string $method, string $path, array $data = [])
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer ' . $this->getToken()
        ])->send($method, 'https://connect.tradedoubler.com/' . $path, ['query' => $data]);

        return new TradedoublerResponse($response);
    }

    protected function getBearerToken($refresh = false)
    {
        if ($refresh === true) {
            $params = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->settings->refresh_token
            ];
        } else {
            $params = [
                'grant_type' => 'password',
                'username' => $this->data['username'],
                'password' => $this->data['password'],
            ];
        }

        $request = Http::asForm()->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . $this->getAuthCode()
        ])->post('https://connect.tradedoubler.com/uaa/oauth/token', $params);

        if ($request->clientError()) {
            $body = $request->json();
            
            if ($body['error_description'] && $body['error_description'] == 'Bad credentials') {
                throw new TradedoublerBadCredentials("Bad credentials given");
            }
        }

        $request->throw();

        $json = $request->json();

        $this->settings->access_token = $json['access_token'];
        $this->settings->refresh_token = $json['refresh_token'];
        $this->settings->expires_at = now()->addSeconds($json['expires_in']);
        $this->settings->save();
    }

    protected function refreshToken()
    {
        $this->getBearerToken(true);
    }

    protected function getAuthCode()
    {
        return base64_encode($this->data['client_id'] . ":" . $this->data['client_secret']);
    }
}