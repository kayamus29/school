<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsvImport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'adapter_type',
        'status',
        'file_name',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'errors',
        'is_dry_run',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'errors' => 'array',
        'is_dry_run' => 'boolean',
        'total_rows' => 'integer',
        'successful_rows' => 'integer',
        'failed_rows' => 'integer',
    ];

    /**
     * Get the user who performed the import.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
