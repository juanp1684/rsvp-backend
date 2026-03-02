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
        Schema::table('events', function (Blueprint $table) {
            $table->string('couple_image')->nullable()->after('notes');
            $table->string('ceremony_image')->nullable()->after('couple_image');
            $table->string('reception_image')->nullable()->after('ceremony_image');
            $table->string('invitation_image')->nullable()->after('reception_image');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['couple_image', 'ceremony_image', 'reception_image', 'invitation_image']);
        });
    }
};
