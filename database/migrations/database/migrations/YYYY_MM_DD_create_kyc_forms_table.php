<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_forms', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('account_no')->unique();
            $table->string('national_id_number')->unique();
            $table->string('phone_number');
            $table->text('address');
            $table->string('email')->nullable();
            $table->enum('occupancy_status', ['Tenant', 'Landlord']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_forms');
    }
};
