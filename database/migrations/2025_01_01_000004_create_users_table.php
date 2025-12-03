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
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('number')->nullable();
            $table->string('designation')->nullable();
            $table->string('role')->nullable();
            $table->text('purpose')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->ulid('organization_id')->nullable();
            $table->foreignUlid('approved_by')->nullable()->constrained('users', 'id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('deactivated_by')->nullable()->constrained('users', 'id')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->softDeletes()->index();
            $table->timestamps();
        });

        Schema::create('resets', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('resets');
        Schema::dropIfExists('sessions');
    }
};
