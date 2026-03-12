<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassFee extends Model
{
    use HasFactory;

    protected $fillable = ['class_id', 'fee_head_id', 'session_id', 'semester_id', 'amount', 'description'];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($classFee) {
            if (BillingBatch::existsForTerm($classFee->session_id, $classFee->semester_id)) {
                throw new \Exception("Class fee amounts are immutable after the first finalized billing batch.");
            }
        });

        static::deleting(function ($classFee) {
            if (BillingBatch::existsForTerm($classFee->session_id, $classFee->semester_id)) {
                throw new \Exception("Class fees cannot be deleted after the first finalized billing batch.");
            }
        });
    }

    public function feeHead()
    {
        return $this->belongsTo(FeeHead::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}
