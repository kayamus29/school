<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'student_identifier_format',
        'school_logo_path',
        'login_background_path',
        'primary_color',
        'secondary_color',
        'office_lat',
        'office_long',
        'geo_range',
        'late_time',
        'bulksms_base_url',
        'bulksms_api_token',
        'bulksms_sender_id',
        'imap_host',
        'imap_port',
        'imap_username',
        'imap_password',
        'imap_encryption',
        'imap_validate_cert',
        'imap_mailbox',
    ];
}
