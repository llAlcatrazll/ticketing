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
        Schema::create('requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('class');
            $table->char('code', 10)->unique();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->smallInteger('priority')->nullable();
            $table->smallInteger('difficulty')->nullable();
            $table->datetime('availability')->nullable();
            $table->boolean('declination')->default(true);
            $table->foreignUlid('organization_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('category_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('subcategory_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('from_id')->nullable()->constrained('organizations', 'id')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
