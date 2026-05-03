<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('civil_at')->nullable()->after('reception_url');
            $table->string('civil_location')->nullable()->after('civil_at');
            $table->string('civil_url')->nullable()->after('civil_location');
            $table->string('civil_image')->nullable()->after('invitation_image');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['civil_at', 'civil_location', 'civil_url', 'civil_image']);
        });
    }
};
