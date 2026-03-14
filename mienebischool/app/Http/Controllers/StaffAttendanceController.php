<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\StaffAttendance;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StaffAttendanceController extends Controller
{
    public function index()
    {
        $todayAttendance = StaffAttendance::where('user_id', Auth::id())
            ->where('date', Carbon::today())
            ->first();

        $history = StaffAttendance::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->paginate(15);

        $settings = SiteSetting::first();
        $attendanceReady = $this->hasValidAttendanceSettings($settings);

        return view('staff.attendance.index', compact('todayAttendance', 'history', 'settings', 'attendanceReady'));
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
        ]);

        $settings = SiteSetting::first();
        $settingsError = $this->getAttendanceSettingsError($settings);

        if ($settingsError) {
            return response()->json([
                'success' => false,
                'message' => $settingsError,
            ], 422);
        }

        $userLat = $request->lat;
        $userLong = $request->long;

        // Calculate distance from office
        $distance = $this->haversineGreatCircleDistance(
            $settings->office_lat,
            $settings->office_long,
            $userLat,
            $userLong
        );

        if ($distance > $settings->geo_range) {
            return response()->json([
                'success' => false,
                'message' => "You are too far from the office (" . round($distance) . "m). Allowed range: {$settings->geo_range}m."
            ], 403);
        }

        $now = Carbon::now();
        $lateThreshold = $this->resolveLateThreshold($settings->late_time);
        $status = $now->toTimeString() > $lateThreshold->toTimeString() ? 'late' : 'on-time';

        StaffAttendance::updateOrCreate(
            ['user_id' => Auth::id(), 'date' => $now->toDateString()],
            [
                'check_in_at' => $now,
                'check_in_lat' => $userLat,
                'check_in_long' => $userLong,
                'status' => $status,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful! Status: ' . ucfirst($status)
        ]);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
        ]);

        $settings = SiteSetting::first();
        $settingsError = $this->getAttendanceSettingsError($settings);

        if ($settingsError) {
            return response()->json([
                'success' => false,
                'message' => $settingsError,
            ], 422);
        }

        $attendance = StaffAttendance::where('user_id', Auth::id())
            ->where('date', Carbon::today())
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'No check-in record found for today.'], 404);
        }

        $attendance->update([
            'check_out_at' => Carbon::now(),
            'check_out_lat' => $request->lat,
            'check_out_long' => $request->long,
        ]);

        return response()->json(['success' => true, 'message' => 'Check-out successful!']);
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @return float Distance in meters
     */
    private function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    private function resolveLateThreshold(?string $lateTime): Carbon
    {
        $lateTime = trim((string) $lateTime);

        if ($lateTime === '') {
            return Carbon::createFromTimeString('08:00:00');
        }

        if (preg_match('/^\d{2}:\d{2}$/', $lateTime)) {
            $lateTime .= ':00';
        }

        return Carbon::createFromTimeString($lateTime);
    }

    private function hasValidAttendanceSettings(?SiteSetting $settings): bool
    {
        return $this->getAttendanceSettingsError($settings) === null;
    }

    private function getAttendanceSettingsError(?SiteSetting $settings): ?string
    {
        if (!$settings) {
            return 'Attendance settings are missing. Ask admin to configure office location first.';
        }

        if ($settings->office_lat === null || $settings->office_long === null) {
            return 'Office latitude and longitude must be configured before staff can check in.';
        }

        if (!$settings->geo_range || (int) $settings->geo_range < 1) {
            return 'Geofencing radius must be configured before staff can check in.';
        }

        return null;
    }
}
