<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_head_id',
        'session_id',
        'semester_id',
        'fee_type',
        'reference',
        'amount',
        'amount_paid',
        'balance',
        'status',
        'transferred_to_id',
        'description'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function feeHead()
    {
        return $this->belongsTo(FeeHead::class);
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function payments()
    {
        return $this->hasMany(StudentPayment::class, 'student_fee_id');
    }

    public function transferredTo()
    {
        return $this->belongsTo(StudentFee::class, 'transferred_to_id');
    }

    public function transferredFrom()
    {
        return $this->hasOne(StudentFee::class, 'transferred_to_id');
    }

    public function transaction()
    {
        return $this->morphOne(WalletTransaction::class, 'reference');
    }
}
