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

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $emailHash = sha1($notifiable->getEmailForVerification());

            $mobileUrl = "hadhazszeku://verify?token={$emailHash}";
            $webUrl = url("/r/verify/{$emailHash}");

            return (new MailMessage)
                ->subject('📬 Email-cím megerősítése')
                ->view('emails.verify', [
                    'user' => $notifiable,
                    'verifyUrl' => $mobileUrl,
                    'fallbackUrl' => $webUrl,
                ]);
        });

        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $payload = [
                'token' => $token,
                'email' => $notifiable->email,
            ];

            $encoded = self::urlSafeEncode($payload);

            $mobileUrl = "hadhazszeku://reset?token={$token}&email={$notifiable->email}";
            $fallbackUrl = url("/r/reset/{$encoded}");

            return (new MailMessage)
                ->subject('🔑 Jelszó visszaállítása')
                ->view('emails.reset', [
                    'user' => $notifiable,
                    'resetUrl' => $mobileUrl,
                    'fallbackUrl' => $fallbackUrl,
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

    public static function urlSafeEncode(array $data): string
    {
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    }

    public static function urlSafeDecode(string $data): ?array
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'));
        return json_decode($decoded, true);
    }
}
