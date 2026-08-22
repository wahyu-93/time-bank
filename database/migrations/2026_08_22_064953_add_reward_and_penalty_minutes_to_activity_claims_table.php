<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_claims', function (Blueprint $table) {
            $table->unsignedInteger('reward_minutes')
                ->default(0)
                ->after('scheduled_date');

            $table->unsignedInteger('penalty_minutes')
                ->default(0)
                ->after('reward_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_claims', function (Blueprint $table) {
            //
        });
    }
};
