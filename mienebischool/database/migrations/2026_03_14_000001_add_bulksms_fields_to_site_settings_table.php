<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBulksmsFieldsToSiteSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'bulksms_base_url')) {
                $table->string('bulksms_base_url')->nullable()->after('late_time');
            }
            if (!Schema::hasColumn('site_settings', 'bulksms_api_token')) {
                $table->string('bulksms_api_token')->nullable()->after('bulksms_base_url');
            }
            if (!Schema::hasColumn('site_settings', 'bulksms_sender_id')) {
                $table->string('bulksms_sender_id')->nullable()->after('bulksms_api_token');
            }
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $columns = [];
            foreach (['bulksms_base_url', 'bulksms_api_token', 'bulksms_sender_id'] as $column) {
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
