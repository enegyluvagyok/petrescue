<?php

use Illuminate\Support\Facades\Route;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

// /r/{type}/{encodedUrl}
Route::get('/r/{type}/{encoded}', function (string $type, string $encoded) {
    $decodedUrl = AuthServiceProvider::expandUrl($encoded);

    // extra biztonság: csak a backend saját URL-jeire engedjük
    if (!str_starts_with($decodedUrl, config('app.url'))) {
        Log::warning("❌ Illegális rövidlink próbálkozás: {$decodedUrl}");
        abort(403, 'Érvénytelen rövidített link.');
    }

    return redirect($decodedUrl);
});
