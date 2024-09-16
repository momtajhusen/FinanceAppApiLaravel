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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Ensure the 'users' table exists and is correctly referenced
            $table->string('name');
            $table->enum('type', ['Income', 'Expense']);
            $table->foreignId('icon_id')->constrained('icons'); // Make sure the 'icons' table name is correct
            $table->foreignId('parent_id')->nullable()->constrained('categories'); // Self-referencing for hierarchy
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
