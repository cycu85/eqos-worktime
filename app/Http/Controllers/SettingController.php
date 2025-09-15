<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class SettingController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Setting::class);
        
        $settings = [
            'app_name' => Setting::getAppName(),
            'logo_path' => Setting::getLogoPath(),
        ];
        
        $smtpSettings = Setting::getSmtpSettings();
        
        return view('settings.index', compact('settings', 'smtpSettings'));
    }

    public function update(Request $request)
    {
        $this->authorize('update', Setting::class);
        
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'remove_logo' => 'sometimes|boolean'
        ]);

        // Update app name
        Setting::set('app_name', $validated['app_name']);

        // Handle logo removal
        if ($request->has('remove_logo') && $request->remove_logo) {
            $currentLogo = Setting::getLogoPath();
            if ($currentLogo && Storage::disk('public')->exists($currentLogo)) {
                Storage::disk('public')->delete($currentLogo);
            }
            Setting::set('logo_path', null);
        }
        // Handle logo upload
        elseif ($request->hasFile('logo')) {
            // Remove old logo
            $currentLogo = Setting::getLogoPath();
            if ($currentLogo && Storage::disk('public')->exists($currentLogo)) {
                Storage::disk('public')->delete($currentLogo);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('logos', 'public');
            Setting::set('logo_path', $logoPath);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Ustawienia zostały zaktualizowane pomyślnie.');
    }

    public function updateSmtp(Request $request)
    {
        $this->authorize('update', Setting::class);
        
        $validated = $request->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        // Add mailer type
        $validated['mail_mailer'] = 'smtp';
        
        Setting::setSmtpSettings($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Ustawienia SMTP zostały zaktualizowane pomyślnie.');
    }

    public function testSmtp(Request $request)
    {
        $this->authorize('update', Setting::class);
        
        $validated = $request->validate([
            'test_email' => 'required|email',
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        try {
            // Temporarily apply SMTP settings for testing
            $testSettings = [
                'mail_mailer' => 'smtp',
                'mail_host' => $validated['mail_host'],
                'mail_port' => $validated['mail_port'],
                'mail_username' => $validated['mail_username'],
                'mail_password' => $validated['mail_password'],
                'mail_encryption' => $validated['mail_encryption'],
                'mail_from_address' => $validated['mail_from_address'],
                'mail_from_name' => $validated['mail_from_name'],
            ];

            // Apply test settings to config
            config([
                'mail.default' => $testSettings['mail_mailer'],
                'mail.mailers.smtp.host' => $testSettings['mail_host'],
                'mail.mailers.smtp.port' => (int) $testSettings['mail_port'],
                'mail.mailers.smtp.username' => $testSettings['mail_username'],
                'mail.mailers.smtp.password' => $testSettings['mail_password'],
                'mail.mailers.smtp.encryption' => $testSettings['mail_encryption'],
                'mail.from.address' => $testSettings['mail_from_address'],
                'mail.from.name' => $testSettings['mail_from_name'],
            ]);

            // Send test email
            Mail::raw('To jest testowa wiadomość email z aplikacji EQOS WorkTime. Jeśli otrzymałeś tę wiadomość, konfiguracja SMTP działa poprawnie.', function (Message $message) use ($validated) {
                $message->to($validated['test_email'])
                    ->subject('Test konfiguracji SMTP - EQOS WorkTime')
                    ->from($validated['mail_from_address'], $validated['mail_from_name']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Email testowy został wysłany pomyślnie!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Błąd wysyłania emaila: ' . $e->getMessage()
            ], 400);
        }
    }
}
