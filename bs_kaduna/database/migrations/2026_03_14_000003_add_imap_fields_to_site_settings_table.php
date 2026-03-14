<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImapFieldsToSiteSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('imap_host')->nullable()->after('bulksms_sender_id');
            $table->unsignedInteger('imap_port')->nullable()->after('imap_host');
            $table->string('imap_username')->nullable()->after('imap_port');
            $table->string('imap_password')->nullable()->after('imap_username');
            $table->string('imap_encryption')->nullable()->after('imap_password');
            $table->boolean('imap_validate_cert')->default(true)->after('imap_encryption');
            $table->string('imap_mailbox')->nullable()->after('imap_validate_cert');
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'imap_host',
                'imap_port',
                'imap_username',
                'imap_password',
                'imap_encryption',
                'imap_validate_cert',
                'imap_mailbox',
            ]);
        });
    }
}
