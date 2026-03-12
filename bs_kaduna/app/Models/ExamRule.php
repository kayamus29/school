<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_marks',
        'pass_marks',
        'exam_weight',
        'ca1_weight',
        'ca2_weight',
        'marks_distribution_note',
        'marks_breakdown',
        'exam_id',
        'session_id'
    ];

    protected $casts = [
        'marks_breakdown' => 'array',
    ];
}
