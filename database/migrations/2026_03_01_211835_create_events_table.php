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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('ceremony_at');
            $table->dateTime('reception_at');
            $table->string('ceremony_location');
            $table->string('ceremony_url')->nullable();
            $table->string('reception_location');
            $table->string('reception_url')->nullable();
            $table->string('dress_code')->nullable();
            $table->dateTime('rsvp_deadline');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
