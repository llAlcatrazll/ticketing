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
        Schema::create('organizations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('room')->nullable();
            $table->string('building')->nullable();
            $table->jsonb('settings')->nullable();
            $table->softDeletes()->index();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('organization_id');
        });
    }
};
