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
        // Create 'mail_logs' table
        Schema::create('mail_logs', function (Blueprint $table) {

            $table->id(); 
            // Primary key 'id', auto-increment

            $table->string('email'); 
            // Recipient email address

            $table->string('subject'); 
            // Email subject

            $table->longText('message'); 
            // Full email message content

            // Extra tracking fields
            $table->unsignedBigInteger('created_by')->nullable(); 
            // ID of user who created the mail log, nullable

            $table->unsignedBigInteger('updated_by')->nullable(); 
            // ID of user who last updated the mail log, nullable

            $table->softDeletes(); 
            // Adds 'deleted_at' column for soft deletes

            $table->tinyInteger('status')->default(1); 
            // Status: 1=Active, 0=Inactive, default is Active

            $table->timestamps(); 
            // Adds 'created_at' and 'updated_at' columns automatically
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop 'mail_logs' table if it exists
        Schema::dropIfExists('mail_logs');
    }
};
