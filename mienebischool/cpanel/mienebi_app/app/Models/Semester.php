<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = ['semester_name', 'start_date', 'end_date', 'session_id'];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($semester) {
            if (BillingBatch::existsForTerm($semester->session_id, $semester->id)) {
                throw new \Exception("Term dates are immutable after the first finalized billing batch.");
            }
        });

        static::deleting(function ($semester) {
            if (BillingBatch::existsForTerm($semester->session_id, $semester->id)) {
                throw new \Exception("Terms cannot be deleted after the first finalized billing batch.");
            }
        });
    }
}
