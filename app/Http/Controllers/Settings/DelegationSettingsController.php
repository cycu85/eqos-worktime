<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\DelegationSetting;
use Illuminate\Http\Request;

class DelegationSettingsController extends Controller
{
    public function index()
    {
        // Get current settings
        $settings = [
            'delegation_rate_poland' => DelegationSetting::get('delegation_rate_poland', 45.00),
            'delegation_rate_abroad' => DelegationSetting::get('delegation_rate_abroad', 50.00),
            'default_project' => DelegationSetting::get('default_project', ''),
            'default_travel_purpose' => DelegationSetting::get('default_travel_purpose', ''),
            'default_country' => DelegationSetting::get('default_country', 'Polska'),
            'default_city' => DelegationSetting::get('default_city', ''),
        ];

        $countries = [
            'Polska', 'Niemcy', 'Francja', 'Włochy', 'Hiszpania', 
            'Czechy', 'Słowacja', 'Austria', 'Holandia', 'Belgia'
        ];

        return view('settings.delegations.index', compact('settings', 'countries'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'delegation_rate_poland' => 'required|numeric|min:0',
            'delegation_rate_abroad' => 'required|numeric|min:0',
        ]);

        DelegationSetting::set('delegation_rate_poland', $request->delegation_rate_poland, 'decimal', 'Stawka diety dla delegacji krajowych (PLN)');
        DelegationSetting::set('delegation_rate_abroad', $request->delegation_rate_abroad, 'decimal', 'Stawka diety dla delegacji zagranicznych (EUR)');

        return redirect()->route('settings.delegations.index')
            ->with('success', 'Stawki diet zostały zaktualizowane.');
    }

    public function updateDefaults(Request $request)
    {
        $request->validate([
            'default_project' => 'nullable|string|max:255',
            'default_travel_purpose' => 'nullable|string',
            'default_country' => 'nullable|string|max:100',
            'default_city' => 'nullable|string|max:255',
        ]);

        DelegationSetting::set('default_project', $request->default_project ?? '', 'string', 'Domyślny projekt');
        DelegationSetting::set('default_travel_purpose', $request->default_travel_purpose ?? '', 'string', 'Domyślny cel podróży');
        DelegationSetting::set('default_country', $request->default_country ?? '', 'string', 'Domyślny kraj');
        DelegationSetting::set('default_city', $request->default_city ?? '', 'string', 'Domyślna miejscowość');

        return redirect()->route('settings.delegations.index')
            ->with('success', 'Domyślne wartości zostały zaktualizowane.');
    }
}
