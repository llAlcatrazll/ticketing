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
        Schema::create('actions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('request_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->text('remarks')->nullable();
            $table->string('status');
            $table->string('resolution')->default('');
            $table->boolean('system')->default(false);
            $table->boolean('check')->virtualAs("(`status` = 'closed' OR `resolution` = '') AND ((`system` = 1 AND `user_id` IS NULL) OR (`system` = 0))");
            $table->timestamps();

            $table->check('`check` = 1', 'actions_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
