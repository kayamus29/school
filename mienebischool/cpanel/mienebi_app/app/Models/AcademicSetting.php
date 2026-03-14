<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_type',
        'marks_submission_status',
        'default_exam_weight',
        'default_ca1_weight',
        'marks_breakdown',
        'enable_financial_withholding'
    ];

    protected $casts = [
        'marks_breakdown' => 'array',
    ];

    public static function current()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'attendance_type' => 'section',
                'marks_submission_status' => 0,
                'default_exam_weight' => 70,
                'default_ca1_weight' => 30,
            ]
        );
    }
}
