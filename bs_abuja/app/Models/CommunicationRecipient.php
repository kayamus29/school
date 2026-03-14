<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunicationRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'communication_id',
        'student_id',
        'channel',
        'recipient_name',
        'destination',
        'status',
        'error_message',
        'provider_message_id',
        'provider_response',
        'sent_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'sent_at' => 'datetime',
    ];

    public function communication()
    {
        return $this->belongsTo(Communication::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
