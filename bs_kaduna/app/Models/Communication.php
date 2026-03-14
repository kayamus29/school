<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Communication extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'audience_type',
        'session_id',
        'class_id',
        'section_id',
        'created_by',
        'sender_role',
        'subject',
        'message',
        'message_html',
        'status',
        'total_recipients',
        'successful_recipients',
        'failed_recipients',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function recipients()
    {
        return $this->hasMany(CommunicationRecipient::class);
    }
}
