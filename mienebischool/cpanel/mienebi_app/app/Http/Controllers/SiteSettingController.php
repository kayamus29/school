<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SiteSettingController extends Controller
{
    public function __construct()
    {
        // 
    }

    public function edit()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Only Admins can manage site settings.');
        }

        $setting = SiteSetting::first();
        if (!$setting) {
            $setting = SiteSetting::create([
                'school_name' => config('app.name', 'Unifiedtransform'),
                'primary_color' => '#3490dc',
                'secondary_color' => '#ffffff',
            ]);
        }
        return view('settings.site', compact('setting'));
    }

    public function update(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Only Admins can manage site settings.');
        }

        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'login_background' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'primary_color' => 'required|string|size:7', // Hex code
            'secondary_color' => 'nullable|string|size:7',
            'office_lat' => 'nullable|numeric|between:-90,90',
            'office_long' => 'nullable|numeric|between:-180,180',
            'geo_range' => 'nullable|integer|min:1',
            'late_time' => 'nullable|date_format:H:i',
        ]);

        $setting = SiteSetting::first();
        if (!$setting) {
            $setting = new SiteSetting();
        }

        $setting->school_name = $request->school_name;
        $setting->primary_color = $request->primary_color;
        $setting->secondary_color = $request->secondary_color;
        $setting->office_lat = $request->office_lat;
        $setting->office_long = $request->office_long;
        $setting->geo_range = $request->geo_range;
        $setting->late_time = $request->late_time;

        if ($request->hasFile('school_logo')) {
            $path = $request->file('school_logo')->store('uploads/logos', 'public');
            $setting->school_logo_path = asset('storage/' . $path);
        }

        if ($request->hasFile('login_background')) {
            $path = $request->file('login_background')->store('uploads/backgrounds', 'public');
            $setting->login_background_path = asset('storage/' . $path);
        }

        $setting->save();

        return redirect()->route('settings.site.edit')->with('success', 'Site settings updated successfully.');
    }
}
