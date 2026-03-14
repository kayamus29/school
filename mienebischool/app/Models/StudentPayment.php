<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StudentPayment extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'student_id',
        'student_fee_id',
        'class_id',
        'school_session_id',
        'semester_id',
        'amount_paid',
        'payment_method',
        'transaction_date',
        'reference_no',
        'received_by'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'school_session_id');
    }

    public function semester() // Term
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function studentFee()
    {
        return $this->belongsTo(StudentFee::class, 'student_fee_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function transaction()
    {
        return $this->morphOne(WalletTransaction::class, 'reference');
    }
}
