<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeoAndLateSettingsToSiteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('office_lat', 10, 8)->nullable();
            $table->decimal('office_long', 11, 8)->nullable();
            $table->integer('geo_range')->nullable()->default(500); // 500 meters default
            $table->time('late_time')->nullable()->default('08:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['office_lat', 'office_long', 'geo_range', 'late_time']);
        });
    }
}
