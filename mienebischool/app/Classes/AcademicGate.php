<?php

namespace App\Classes;

use App\Models\User;
use App\Models\AcademicSetting;
use Illuminate\Support\Facades\Auth;

class AcademicGate
{
    /**
     * Determine if the user can view results.
     * 
     * @param User $student
     * @return bool
     */
    public static function canViewResults(User $student): bool
    {
        $user = Auth::user();

        // Staff (Admins, Teachers, Accountants) are always allowed to view
        if ($user->hasAnyRole(['Admin', 'Teacher', 'Accountant'])) {
            return true;
        }

        // Apply financial withholding policy
        if (self::isWithholdingEnabled() && $student->getTotalOutstandingBalance() > 0) {
            return false;
        }

        // Check student status
        if ($student->status === 'deactivated') {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can view attendance.
     * 
     * @param User $student
     * @return bool
     */
    public static function canViewAttendance(User $student): bool
    {
        $user = Auth::user();

        // Staff are always allowed
        if ($user->hasAnyRole(['Admin', 'Teacher', 'Accountant'])) {
            return true;
        }

        // Apply financial withholding policy
        if (self::isWithholdingEnabled() && $student->getTotalOutstandingBalance() > 0) {
            return false;
        }

        // Check student status
        if ($student->status === 'deactivated') {
            return false;
        }

        return true;
    }

    /**
     * Check if financial withholding is enabled in settings.
     * 
     * @return bool
     */
    private static function isWithholdingEnabled(): bool
    {
        $setting = AcademicSetting::first();
        return $setting ? (bool) $setting->enable_financial_withholding : false;
    }
}
