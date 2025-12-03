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
        Schema::create('feedback', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('email')->nullable();
            $table->string('client_type')->nullable();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('residence');
            $table->string('expectation')->nullable();
            $table->string('strength')->nullable();
            $table->string('improvement')->nullable();
            $table->string('service_type');
            $table->foreignUlid('category_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignUlid('request_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignUlid('organization_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
