<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->integer('hisabati')->nullable()->default(null)->change();
            $table->integer('kiswahili')->nullable()->default(null)->change();
            $table->integer('sayansi')->nullable()->default(null)->change();
            $table->integer('english')->nullable()->default(null)->change();
            $table->integer('jamii')->nullable()->default(null)->change();
            $table->integer('maadili')->nullable()->default(null)->change();
            $table->integer('kuhesabu')->nullable()->default(null)->change();
            $table->integer('kusoma')->nullable()->default(null)->change();
            $table->integer('kuandika')->nullable()->default(null)->change();
            $table->integer('mazingira')->nullable()->default(null)->change();
            $table->integer('utamaduni')->nullable()->default(null)->change();
            $table->integer('michezo')->nullable()->default(null)->change();
            $table->integer('jiographia')->nullable()->default(null)->change();
            $table->integer('smichezo')->nullable()->default(null)->change();
            $table->integer('historia')->nullable()->default(null)->change();
            $table->integer('s_kazi')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->integer('hisabati')->nullable()->default(0)->change();
            $table->integer('kiswahili')->nullable()->default(0)->change();
            $table->integer('sayansi')->nullable()->default(0)->change();
            $table->integer('english')->default(0)->change();
            $table->integer('jamii')->nullable()->default(0)->change();
            $table->integer('maadili')->nullable()->default(0)->change();
            $table->integer('kuhesabu')->nullable()->default(0)->change();
            $table->integer('kusoma')->nullable()->default(0)->change();
            $table->integer('kuandika')->nullable()->default(0)->change();
            $table->integer('mazingira')->nullable()->default(0)->change();
            $table->integer('utamaduni')->nullable()->default(0)->change();
            $table->integer('michezo')->nullable()->default(0)->change();
            $table->integer('jiographia')->default(0)->change();
            $table->integer('smichezo')->default(0)->change();
            $table->integer('historia')->default(0)->change();
            $table->integer('s_kazi')->default(0)->change();
        });
    }
};
