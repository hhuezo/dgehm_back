<?php

namespace App\Http\Controllers\configuration;

use App\Http\Controllers\Controller;
use App\Services\MailConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class MailSettingsController extends Controller
{
    public function show()
    {
        $settings = MailConfigurationService::getOrCreate();

        return response()->json([
            'success' => true,
            'data' => MailConfigurationService::toPublicArray($settings),
        ]);
    }

    public function update(Request $request)
    {
        $settings = MailConfigurationService::getOrCreate();

        $validated = $request->validate([
            'mailer' => ['required', 'string', Rule::in(['smtp', 'log'])],
            'host' => ['nullable', 'string', 'max:255', 'required_if:mailer,smtp'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535', 'required_if:mailer,smtp'],
            'encryption' => ['nullable', 'string', Rule::in(['tls', 'ssl', ''])],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:500'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ], [
            'mailer.required' => 'El tipo de envío es obligatorio.',
            'mailer.in' => 'El tipo de envío seleccionado no es válido.',
            'host.required_if' => 'El servidor SMTP es obligatorio.',
            'port.required_if' => 'El puerto SMTP es obligatorio.',
            'port.integer' => 'El puerto debe ser un número entero.',
            'port.min' => 'El puerto debe ser mayor a cero.',
            'port.max' => 'El puerto no es válido.',
            'encryption.in' => 'El tipo de cifrado no es válido.',
            'from_address.required' => 'El correo remitente es obligatorio.',
            'from_address.email' => 'El correo remitente no es válido.',
            'from_name.required' => 'El nombre del remitente es obligatorio.',
            'is_active.required' => 'Debe indicar si la configuración está activa.',
            'is_active.boolean' => 'El estado activo no es válido.',
        ], [
            'mailer' => 'tipo de envío',
            'host' => 'servidor SMTP',
            'port' => 'puerto',
            'encryption' => 'cifrado',
            'username' => 'usuario SMTP',
            'password' => 'contraseña SMTP',
            'from_address' => 'correo remitente',
            'from_name' => 'nombre del remitente',
            'is_active' => 'configuración activa',
        ]);

        $payload = [
            'mailer' => $validated['mailer'],
            'host' => $validated['host'] ?? null,
            'port' => $validated['port'] ?? null,
            'encryption' => filled($validated['encryption'] ?? null) ? $validated['encryption'] : null,
            'username' => $validated['username'] ?? null,
            'from_address' => $validated['from_address'],
            'from_name' => $validated['from_name'],
            'is_active' => (bool) $validated['is_active'],
            'updated_by' => $request->user()?->id,
        ];

        if (filled($validated['password'] ?? null)) {
            $payload['password'] = $validated['password'];
        }

        $settings->update($payload);
        $settings->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Configuración de correo guardada correctamente.',
            'data' => MailConfigurationService::toPublicArray($settings),
        ]);
    }

    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'El correo de destino es obligatorio.',
            'email.email' => 'El correo de destino no es válido.',
        ], [
            'email' => 'correo de destino',
        ]);

        $settings = MailConfigurationService::getOrCreate();

        if (!$settings->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Active la configuración de correo antes de enviar una prueba.',
            ], 422);
        }

        try {
            MailConfigurationService::applyFromDatabase();

            Mail::raw(
                'Este es un correo de prueba enviado desde la configuración del sistema.',
                function ($message) use ($validated, $settings) {
                    $message->to($validated['email'])
                        ->subject('Prueba de configuración de correo');
                }
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el correo de prueba: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Correo de prueba enviado correctamente.',
        ]);
    }
}
