<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('room_number', 20);
            $table->unsignedTinyInteger('capacity');
            $table->decimal('price_per_semester', 10, 2);
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->unique(['hostel_id', 'room_number']); // room numbers are unique within a hostel
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
