<?php

namespace App\Services;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class MailConfigurationService
{
    public static function getOrCreate(): MailSetting
    {
        return MailSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'mailer' => 'smtp',
                'from_address' => config('mail.from.address', 'noreply@example.com'),
                'from_name' => config('mail.from.name', 'Sistema'),
                'is_active' => false,
            ]
        );
    }

    public static function applyFromDatabase(): void
    {
        if (!Schema::hasTable('adm_mail_settings')) {
            return;
        }

        $settings = MailSetting::query()
            ->where('is_active', true)
            ->first();

        if (!$settings) {
            return;
        }

        Config::set('mail.default', $settings->mailer);

        if ($settings->mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', $settings->host);
            Config::set('mail.mailers.smtp.port', $settings->port);
            Config::set('mail.mailers.smtp.encryption', $settings->encryption ?: null);
            Config::set('mail.mailers.smtp.username', $settings->username);

            if (filled($settings->password)) {
                Config::set('mail.mailers.smtp.password', $settings->password);
            }
        }

        Config::set('mail.from.address', $settings->from_address);
        Config::set('mail.from.name', $settings->from_name);
    }

    /**
     * @return array<string, mixed>
     */
    public static function toPublicArray(MailSetting $settings): array
    {
        return [
            'id' => $settings->id,
            'mailer' => $settings->mailer,
            'host' => $settings->host,
            'port' => $settings->port,
            'encryption' => $settings->encryption,
            'username' => $settings->username,
            'has_password' => filled($settings->getRawOriginal('password')),
            'from_address' => $settings->from_address,
            'from_name' => $settings->from_name,
            'is_active' => $settings->is_active,
            'updated_at' => $settings->updated_at,
        ];
    }
}
