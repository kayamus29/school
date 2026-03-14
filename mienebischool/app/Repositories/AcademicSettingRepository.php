<?php

namespace App\Repositories;

use App\Models\AcademicSetting;
use App\Interfaces\AcademicSettingInterface;

class AcademicSettingRepository implements AcademicSettingInterface
{
    public function getAcademicSetting()
    {
        return AcademicSetting::current();
    }

    public function updateAttendanceType($request)
    {
        try {
            AcademicSetting::current()->update($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update attendance type. ' . $e->getMessage());
        }
    }

    public function updateFinalMarksSubmissionStatus($request)
    {
        $status = "off";
        if (isset($request['marks_submission_status'])) {
            $status = "on";
        }
        try {
            AcademicSetting::current()->update(['marks_submission_status' => $status]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update final marks submission status. ' . $e->getMessage());
        }
    }

    public function updateDefaultWeights($request)
    {
        try {
            AcademicSetting::current()->update($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update default weights. ' . $e->getMessage());
        }
    }

    public function updateFinancialWithholding($request)
    {
        $status = false;
        if (isset($request['enable_financial_withholding'])) {
            $status = true;
        }
        try {
            AcademicSetting::current()->update(['enable_financial_withholding' => $status]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update financial withholding status. ' . $e->getMessage());
        }
    }
}