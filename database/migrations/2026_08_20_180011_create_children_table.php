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
        Schema::create('children', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->date('birth_date')->nullable();
            $table->string('avatar')->nullable();

            $table->unsignedInteger('daily_limit_minutes')
                ->default(60);

            $table->unsignedInteger('max_debt_minutes')
                ->default(60);

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
        Schema::dropIfExists('children');
    }
};
