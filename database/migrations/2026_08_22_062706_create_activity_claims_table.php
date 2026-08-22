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
        Schema::create('activity_claims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('activity_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('scheduled_date');

            $table->timestamp('claimed_at')->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'expired',
            ])->default('pending');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique([
                'child_id',
                'activity_id',
                'scheduled_date',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_claims');
    }
};
