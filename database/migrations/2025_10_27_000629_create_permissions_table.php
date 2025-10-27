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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('can_create_post')->default(true);
            $table->boolean('can_update_post')->default(true);
            $table->boolean('can_delete_post')->default(true);
            $table->boolean('can_create_comment')->default(true);
            $table->boolean('can_update_comment')->default(true);
            $table->boolean('can_delete_comment')->default(true);
            $table->boolean('can_like_post')->default(true);
            $table->boolean('can_unlike_post')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
