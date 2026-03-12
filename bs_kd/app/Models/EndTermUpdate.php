<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EndTermUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'semester_id',
        'title',
        'content_format',
        'content_body',
        'newsletter_url',
        'next_term_label',
        'next_resumption_date',
        'fee_deadline',
        'resumption_note',
        'published_by',
    ];

    protected $casts = [
        'next_resumption_date' => 'date',
        'fee_deadline' => 'date',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
