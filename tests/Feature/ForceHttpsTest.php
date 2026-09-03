<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;

test('generated URLs use HTTPS when force HTTPS is enabled', function () {
    $originalValue = config('app.force_https');
    config()->set('app.force_https', true);

    try {
        (new AppServiceProvider($this->app))->boot();

        expect(URL::to('/react'))->toStartWith('https://');
    } finally {
        config()->set('app.force_https', $originalValue);
        URL::forceScheme(null);
    }
});
