<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 📬 Verify Email
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $shortUrl = self::shortenUrl($url, 'verify');
            return (new MailMessage)
                ->subject('📬 Email-cím megerősítése')
                ->view('emails.verify', [
                    'user' => $notifiable,
                    'verifyUrl' => $shortUrl,
                ]);
        });

        // 🔑 Reset Password
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url("/api/auth/password/reset/confirm?email={$notifiable->email}&token={$token}");
            $shortUrl = self::shortenUrl($url, 'reset');
            return (new MailMessage)
                ->subject('🔑 Jelszó visszaállítása')
                ->view('emails.reset', [
                    'user' => $notifiable,
                    'resetUrl' => $shortUrl,
                ]);
        });
    }

    /**
     * Rövid URL generálás – base64 + típus prefix
     */
    public static function shortenUrl(string $url, string $type): string
    {
        $encoded = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');
        $base = config('app.url'); // pl. http://192.168.1.110:8000
        return "{$base}/r/{$type}/{$encoded}";
    }

    /**
     * Visszafejtés
     */
    public static function expandUrl(string $encoded): string
    {
        $decoded = base64_decode(strtr($encoded, '-_', '+/'));
        return $decoded ?: '/';
    }
}
