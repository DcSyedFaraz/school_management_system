<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->double('average', 13, 6)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->double('average', 13, 6)->nullable(false)->default(0)->change();
        });
    }
};
