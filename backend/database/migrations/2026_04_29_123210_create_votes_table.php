<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('option_id')
                  ->constrained()
                  ->cascadeOnDelete();
            // Store the voter's IP address for duplicate-vote prevention
            $table->string('ip_address', 45); // 45 chars covers IPv6
            $table->timestamp('created_at')->useCurrent();

            // Enforce one vote per IP per poll at the database level
            $table->unique(['poll_id', 'ip_address'], 'votes_poll_ip_unique');

            // Index for fast vote count aggregation by option
            $table->index('option_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
