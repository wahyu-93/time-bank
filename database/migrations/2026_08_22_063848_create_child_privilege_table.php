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
        Schema::create('child_privilege', function (Blueprint $table) {
            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('privilege_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('custom_cost_minutes')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->primary([
                'child_id',
                'privilege_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_privilege');
    }
};
