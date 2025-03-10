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
        Schema::create('slides', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('imageLink');
            $table->text('description')->nullable();
            $table->string('textColor');
            $table->string('bgColor');
            $table->string('textPosition');
            $table->timestamp('startDate');
            $table->timestamp('endDate');
            $table->json('selectedScreens');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
