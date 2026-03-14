<?php

namespace App\Models;

use App\Models\Mark;
use App\Models\Promotion;
use App\Models\ClassFee;
use App\Models\StudentFee;
use App\Models\StudentPayment;
use App\Models\StudentParentInfo;
use App\Models\StudentAcademicInfo;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'gender',
        'nationality',
        'phone',
        'address',
        'address2',
        'city',
        'zip',
        'photo',
        'birthday',
        'religion',
        'blood_type',
        'role',
        'status',
        'deactivated_at',
        'deactivated_by',
        'deactivation_reason',
        'graduated_at',
    ];

    public function deactivator()
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the parent_info.
     */
    public function parent_info()
    {
        return $this->hasOne(StudentParentInfo::class, 'student_id', 'id');
    }

    /**
     * Get the academic_info.
     */
    public function academic_info()
    {
        return $this->hasOne(StudentAcademicInfo::class, 'student_id', 'id');
    }

    /**
     * Get the marks.
     */
    public function marks()
    {
        return $this->hasMany(Mark::class, 'student_id', 'id');
    }

    /**
     * Get the promotions.
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'student_id', 'id');
    }

    public function getTotalOutstandingBalance()
    {
        return StudentFee::where('student_id', $this->id)->whereNull('transferred_to_id')->sum('balance');
    }

    public function getTotalFees()
    {
        return StudentFee::where('student_id', $this->id)->whereNull('transferred_to_id')->sum('amount');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'student_id');
    }
}
