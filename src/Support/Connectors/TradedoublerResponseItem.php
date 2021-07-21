<?php

namespace Eelcol\LaravelTradedoubler\Support\Connectors;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

class TradedoublerResponseItem
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __get(string $key)
    {
        return $this->data[$key] ?? null;
    }
}