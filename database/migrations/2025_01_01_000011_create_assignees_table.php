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
        Schema::create('assignees', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('request_id')->constrained('requests')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignUlid('assigned_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignUlid('assigner_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->unique(['request_id', 'assigned_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignee');
    }
};
