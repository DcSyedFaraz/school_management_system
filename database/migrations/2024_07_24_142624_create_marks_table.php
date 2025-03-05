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
        Schema::create('marks', function (Blueprint $table) {
            // $table->id();
            $table->id('markId');
            $table->string('examDate');
            $table->integer('classId');
            $table->string('studentName');
            $table->string('gender')->nullable();
            $table->integer('hisabati')->nullable()->default(0);
            $table->integer('kiswahili')->nullable()->default(0);
            $table->integer('sayansi')->nullable()->default(0);
            $table->integer('english')->default(0);
            $table->integer('jamii')->nullable()->default(0);
            $table->integer('maadili')->nullable()->default(0);
            $table->integer('kuhesabu')->nullable()->default(0);
            $table->integer('kusoma')->nullable()->default(0);
            $table->integer('kuandika')->nullable()->default(0);
            $table->integer('mazingira')->nullable()->default(0);
            $table->integer('utamaduni')->nullable()->default(0);
            $table->integer('michezo')->nullable()->default(0);
            $table->integer('jiographia')->default(0);
            $table->integer('smichezo')->default(0);
            $table->integer('historia')->default(0);
            $table->integer('s_kazi')->default(0);
            $table->double('total', 13, 6)->default(0.000000);
            $table->double('average', 13, 6)->default(0.000000);
            $table->string('examId');
            $table->integer('userId');
            $table->integer('regionId');
            $table->integer('districtId');
            $table->integer('wardId')->nullable();
            $table->integer('schoolId');
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
        Schema::dropIfExists('marks');
    }
};
