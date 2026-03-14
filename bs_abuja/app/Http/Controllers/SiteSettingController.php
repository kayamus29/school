<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Services\StudentIdentifierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SiteSettingController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(
        private StudentIdentifierService $studentIdentifierService,
        SchoolSessionInterface $schoolSessionRepository
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function edit()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Only Admins can manage site settings.');
        }

        $setting = SiteSetting::first();
        if (!$setting) {
            $setting = SiteSetting::create([
                'school_name' => config('app.name', 'Auracle Technologies'),
                'student_identifier_format' => 'STU/{year}/xxx',
                'primary_color' => '#3490dc',
                'secondary_color' => '#ffffff',
            ]);
        }
        $currentSessionId = $this->getSchoolCurrentSession();

        return view('settings.site', [
            'setting' => $setting,
            'studentIdentifierPreview' => $this->studentIdentifierService->previewForSession($currentSessionId),
        ]);
    }

    public function update(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Only Admins can manage site settings.');
        }

        $request->validate([
            'school_name' => 'required|string|max:255',
            'student_identifier_format' => 'required|string|max:255',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'login_background' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'primary_color' => 'required|string|size:7', // Hex code
            'secondary_color' => 'nullable|string|size:7',
            'office_lat' => 'nullable|numeric|between:-90,90',
            'office_long' => 'nullable|numeric|between:-180,180',
            'geo_range' => 'nullable|integer|min:1',
            'late_time' => 'nullable|date_format:H:i',
            'bulksms_base_url' => 'nullable|url|max:255',
            'bulksms_api_token' => 'nullable|string|max:255',
            'bulksms_sender_id' => 'nullable|string|max:20',
            'imap_host' => 'nullable|string|max:255',
            'imap_port' => 'nullable|integer|min:1|max:65535',
            'imap_username' => 'nullable|string|max:255',
            'imap_password' => 'nullable|string|max:255',
            'imap_encryption' => 'nullable|in:ssl,tls,notls',
            'imap_validate_cert' => 'nullable|boolean',
            'imap_mailbox' => 'nullable|string|max:255',
        ]);

        $setting = SiteSetting::first();
        if (!$setting) {
            $setting = new SiteSetting();
        }

        $setting->school_name = $request->school_name;
        $setting->student_identifier_format = $request->student_identifier_format;
        $setting->primary_color = $request->primary_color;
        $setting->secondary_color = $request->secondary_color;
        $setting->office_lat = $request->office_lat;
        $setting->office_long = $request->office_long;
        $setting->geo_range = $request->geo_range;
        $setting->late_time = $request->late_time;
        $setting->bulksms_base_url = $request->bulksms_base_url;
        $setting->bulksms_api_token = $request->bulksms_api_token;
        $setting->bulksms_sender_id = $request->bulksms_sender_id;
        $setting->imap_host = $request->imap_host;
        $setting->imap_port = $request->imap_port;
        $setting->imap_username = $request->imap_username;
        $setting->imap_password = $request->imap_password;
        $setting->imap_encryption = $request->imap_encryption;
        $setting->imap_validate_cert = $request->boolean('imap_validate_cert', false);
        $setting->imap_mailbox = $request->imap_mailbox;

        if ($request->hasFile('school_logo')) {
            $path = $request->file('school_logo')->store('uploads/logos', 'public');
            $setting->school_logo_path = 'storage/' . $path;
        }

        if ($request->hasFile('login_background')) {
            $path = $request->file('login_background')->store('uploads/backgrounds', 'public');
            $setting->login_background_path = 'storage/' . $path;
        }

        $setting->save();

        return redirect()->route('settings.site.edit')->with('success', 'Site settings updated successfully.');
    }
}
