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
        Schema::create('mark_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('markId'); // Must match marks.markId type
            $table->string('subject');
            $table->string('grade');
            $table->timestamps();

            $table->foreign('markId')
                ->references('markId')->on('marks')
                ->onDelete('cascade');

            // Indexes for faster querying
            $table->index(['markId']);
            $table->index(['subject']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mark_grades');
    }
};
