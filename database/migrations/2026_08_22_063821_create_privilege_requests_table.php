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
        Schema::create('privilege_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('privilege_id')
                ->constrained()
                ->cascadeOnDelete();

            // Simpan harga saat request dibuat.
            // Jangan mengambil harga privilege lagi ketika approval.
            $table->unsignedInteger('cost_minutes');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->timestamp('requested_at');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privilege_requests');
    }
};
