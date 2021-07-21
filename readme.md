# Installation

```
composer require eelcol/laravel-tradedoubler
```

### Setup .env
Change your .env to include the following variables:
```
TRADEDOUBLER_CLIENT_ID=...
TRADEDOUBLER_CLIENT_SECRET=...
TRADEDOUBLER_USERNAME=...
TRADEDOUBLER_PASSWORD=... 
```

Read the following docs to get a clientId and clientSecret:

```
https://tradedoubler.docs.apiary.io/#/reference/o-auth-2-0/bearer-and-refresh-token
```

### Publish assets

```
php artisan vendor:publish --tag=laravel-tradedoubler
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
```

Also run the migrations after publishing:

```
php artisan migrate
```

### Fetch data
#### Load transactions
````
use Eelcol\LaravelTradedoubler\Support\Facades\Tradedoubler;

// last 7 days
Tradedoubler::getTransactions(now()->subDays(7), now());

// today only
Tradedoubler::getTransactions(now());
````

#### Make another GET call
Currently, only the call to load transactions is build-in. To make another GET call:

````
use Eelcol\LaravelTradedoubler\Support\Facades\Tradedoubler;

Tradedoubler::get('path', ['param1' => 123]);
````