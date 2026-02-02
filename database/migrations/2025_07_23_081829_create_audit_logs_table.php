<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->integer('users_id'); // ID from users table
        $table->string('action'); // e.g., "Approved KYC", "Rejected KYC"
        $table->unsignedBigInteger('kyc_form_id')->nullable(); // KYC form affected
        $table->text('description')->nullable(); // e.g., remarks or extra info
        $table->string('ip_address')->nullable();
        $table->timestamp('created_at')->useCurrent();

        $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
