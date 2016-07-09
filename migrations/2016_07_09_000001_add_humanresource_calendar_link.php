<?php

use Illuminate\Database\Migrations\Migration;

class AddHumanresourceCalendarLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('humanresources', function ($table) {
            $table->string('calendar_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('humanresources', function ($table) {
            $table->dropColumn('calendar_link');
        });
    }
}
