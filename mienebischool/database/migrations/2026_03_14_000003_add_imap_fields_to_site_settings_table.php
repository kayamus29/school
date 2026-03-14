<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImapFieldsToSiteSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'imap_host')) {
                $table->string('imap_host')->nullable()->after('bulksms_sender_id');
            }
            if (!Schema::hasColumn('site_settings', 'imap_port')) {
                $table->unsignedInteger('imap_port')->nullable()->after('imap_host');
            }
            if (!Schema::hasColumn('site_settings', 'imap_username')) {
                $table->string('imap_username')->nullable()->after('imap_port');
            }
            if (!Schema::hasColumn('site_settings', 'imap_password')) {
                $table->string('imap_password')->nullable()->after('imap_username');
            }
            if (!Schema::hasColumn('site_settings', 'imap_encryption')) {
                $table->string('imap_encryption')->nullable()->after('imap_password');
            }
            if (!Schema::hasColumn('site_settings', 'imap_validate_cert')) {
                $table->boolean('imap_validate_cert')->default(true)->after('imap_encryption');
            }
            if (!Schema::hasColumn('site_settings', 'imap_mailbox')) {
                $table->string('imap_mailbox')->nullable()->after('imap_validate_cert');
            }
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $columns = [];
            foreach ([
                'imap_host',
                'imap_port',
                'imap_username',
                'imap_password',
                'imap_encryption',
                'imap_validate_cert',
                'imap_mailbox',
            ] as $column) {
                if (Schema::hasColumn('site_settings', $column)) {
                    $columns[] = $column;
                }
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
}
