<?php

namespace App\Services;

use App\Models\User;
use App\Models\Promotion;
use App\Models\PromotionReview;
use App\Models\AcademicSetting;
use App\Classes\AcademicGate;
use App\Services\PromotionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GraduationService
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * Evaluate graduation eligibility for a student.
     * 
     * @param User $student
     * @param int $sessionId
     * @return array
     */
    public function evaluate(User $student, int $sessionId): array
    {
        $promotion = Promotion::where('student_id', $student->id)
            ->where('session_id', $sessionId)
            ->with('schoolClass')
            ->first();

        if (!$promotion || !$promotion->schoolClass->is_final_grade) {
            return [
                'status' => 'not_eligible',
                'reason' => 'Student is not in a final class level.'
            ];
        }

        // Check Promotion status
        $review = PromotionReview::where('student_id', $student->id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$review) {
            return [
                'status' => 'academic_hold',
                'reason' => 'Promotion review not yet generated.'
            ];
        }

        if ($review->final_status !== 'promoted') {
            return [
                'status' => 'academic_hold',
                'reason' => "Student was marked as '{$review->final_status}' by promotion engine."
            ];
        }

        // Check Financial Withholding if enabled
        $withholdingEnabled = AcademicSetting::first()->enable_financial_withholding ?? false;
        if ($withholdingEnabled && $student->getTotalOutstandingBalance() > 0) {
            return [
                'status' => 'withheld_financial',
                'reason' => 'Outstanding balance prevents graduation.',
                'balance' => $student->getTotalOutstandingBalance()
            ];
        }

        return [
            'status' => 'eligible_for_graduation',
            'reason' => 'All criteria met.'
        ];
    }

    /**
     * Finalize graduation for a student.
     * 
     * @param User $student
     * @param int $deactivatorId
     * @return void
     */
    public function finalize(User $student, int $adminId)
    {
        DB::transaction(function () use ($student, $adminId) {
            $student->update([
                'status' => 'graduated',
                'graduated_at' => now(),
            ]);

            // Create audit log entry (Assuming there's an audit system, otherwise we rely on deactivation_reason or activity log)
            // Existing User model has LogsActivity trait.
        });
    }
}
