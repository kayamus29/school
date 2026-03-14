<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboundEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'message_id',
        'mailbox',
        'from_name',
        'from_email',
        'to_email',
        'subject',
        'body_text',
        'body_html',
        'received_at',
        'is_seen',
        'raw_headers',
        'metadata',
        'synced_by',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'is_seen' => 'boolean',
        'metadata' => 'array',
    ];

    public function syncedBy()
    {
        return $this->belongsTo(User::class, 'synced_by');
    }
}
