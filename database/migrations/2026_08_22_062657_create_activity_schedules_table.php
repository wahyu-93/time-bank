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
        Schema::create('activity_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('frequency', [
                'daily',
                'weekly',
                'once',
            ])->default('daily');

            $table->json('days_of_week')->nullable();

            $table->time('start_time')->nullable();
            $table->time('due_time')->nullable();

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_schedules');
    }
};
