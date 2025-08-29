<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Setting::class);
        
        $settings = [
            'app_name' => Setting::getAppName(),
            'logo_path' => Setting::getLogoPath(),
        ];
        
        return view('settings.index', compact('settings'));
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
}
