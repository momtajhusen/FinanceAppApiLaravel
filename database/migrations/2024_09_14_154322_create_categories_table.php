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
            $table->foreignId('user_id')->constrained('users'); // Add this line for user relationship
            $table->string('name');
            $table->enum('type', ['Income', 'Expense']);
            $table->foreignId('icon_id')->constrained('icones'); // Ensure the correct table name
            $table->foreignId('parent_id')->nullable()->constrained('categories'); // For hierarchy
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
