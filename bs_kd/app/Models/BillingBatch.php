<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'semester_id',
        'processed_by',
        'student_count',
        'total_amount',
        'status',
        'batch_meta',
    ];

    protected $casts = [
        'batch_meta' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class, 'billing_batch_id');
    }

    /**
     * Check if a finalized or locked batch exists for the given term.
     */
    public static function existsForTerm($sessionId, $semesterId)
    {
        return self::where('session_id', $sessionId)
            ->where('semester_id', $semesterId)
            ->whereIn('status', ['finalized', 'locked'])
            ->exists();
    }
}
