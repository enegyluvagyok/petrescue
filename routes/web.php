<?php

use Illuminate\Support\Facades\Route;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AppBuildController;

Route::get('/builds', [AppBuildController::class, 'index'])->name('builds.index');
Route::post('/builds', [AppBuildController::class, 'store'])->name('builds.store');
Route::get('/builds/{build}/download', [AppBuildController::class, 'download'])->name('builds.download');
Route::delete('/builds/{build}', [AppBuildController::class, 'destroy'])->name('builds.destroy');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/r/verify/{token}', function ($token) {
    return redirect("hadhazszeku://verify?token={$token}");
});

Route::get('/r/reset/{encoded}', function ($encoded) {
    $payload = AuthServiceProvider::urlSafeDecode($encoded);

    if (!isset($payload['token'], $payload['email'])) {
        return 'Érvénytelen vagy lejárt link.';
    }

    $mobileLink = "hadhazszeku://reset?token={$payload['token']}&email={$payload['email']}";
    return redirect($mobileLink);
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
