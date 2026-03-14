<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBulksmsFieldsToSiteSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('bulksms_base_url')->nullable()->after('late_time');
            $table->string('bulksms_api_token')->nullable()->after('bulksms_base_url');
            $table->string('bulksms_sender_id')->nullable()->after('bulksms_api_token');
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'bulksms_base_url',
                'bulksms_api_token',
                'bulksms_sender_id',
            ]);
        });
    }
}
