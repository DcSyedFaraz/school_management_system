<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('userId');
            $table->string('user_name')->nullable();
            $table->string('userName');
            $table->string('email');
            $table->string('mobile');
            $table->string('password');
            $table->string('token');
            $table->string('userType');
            $table->integer('schoolId')->nullable();
            $table->string('regionId')->nullable();
            $table->string('otp')->nullable();
            $table->string('districtId')->nullable();
            $table->string('wardId')->nullable();
            $table->string('registrationNumber')->nullable();
            $table->string('isActive')->default(1);
            $table->string('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
