<?php

namespace App\Services;

use App\Models\warehouse\SupplyRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SupplyRequestMailService
{
    /**
     * @return array{sent: bool, reason: string|null}
     */
    public function notifyRequesterReadyForPickup(SupplyRequest $supplyRequest): array
    {
        if (!$this->isMailConfigured()) {
            Log::info('No se envió correo de solicitud lista: configuración de correo inactiva.', [
                'supply_request_id' => $supplyRequest->id,
            ]);

            return [
                'sent' => false,
                'reason' => 'La configuración de correo no está activa.',
            ];
        }

        $supplyRequest->loadMissing(['requester', 'organizationalUnit']);

        $requester = $supplyRequest->requester;
        $email = $requester?->email;

        if (!filled($email)) {
            Log::warning('No se envió correo de solicitud lista: el solicitante no tiene correo.', [
                'supply_request_id' => $supplyRequest->id,
                'requester_id' => $supplyRequest->requester_id,
            ]);

            return [
                'sent' => false,
                'reason' => 'El solicitante no tiene correo electrónico registrado.',
            ];
        }

        try {
            MailConfigurationService::applyFromDatabase();
            $this->refreshMailer();

            Mail::send(
                'emails.supply_request_ready_for_pickup',
                ['supplyRequest' => $supplyRequest],
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Su solicitud de insumos está lista para retirar');
                }
            );

            Log::info('Correo de solicitud lista enviado correctamente.', [
                'supply_request_id' => $supplyRequest->id,
                'requester_email' => $email,
            ]);

            return ['sent' => true, 'reason' => null];
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de solicitud lista para retirar.', [
                'supply_request_id' => $supplyRequest->id,
                'requester_email' => $email,
                'exception' => $e->getMessage(),
            ]);

            return [
                'sent' => false,
                'reason' => 'Error SMTP: ' . $e->getMessage(),
            ];
        }
    }

    private function isMailConfigured(): bool
    {
        if (!Schema::hasTable('adm_mail_settings')) {
            return false;
        }

        return \App\Models\MailSetting::query()
            ->where('is_active', true)
            ->exists();
    }

    private function refreshMailer(): void
    {
        $mailer = config('mail.default');

        if (is_string($mailer) && $mailer !== '') {
            app('mail.manager')->purge($mailer);
        }
    }
}
