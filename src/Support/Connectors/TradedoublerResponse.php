<?php

namespace Eelcol\LaravelTradedoubler\Support\Connectors;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

class TradedoublerResponse
{
    protected Response $response;

    protected Collection $items;

    public function __construct(Response $response)
    {
        $this->response = $response;

        $this->items = collect($response->json()['items'] ?? []);
    }

    public function successful()
    {
        return $this->response->successful();
    }

    public function items()
    {
        return $this->items;
    }
}