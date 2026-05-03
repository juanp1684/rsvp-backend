<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('civil_ceremony_same_venue')->default(false);
            $table->boolean('civil_reception_same_venue')->default(false);
            $table->boolean('ceremony_reception_same_venue')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['civil_ceremony_same_venue', 'civil_reception_same_venue', 'ceremony_reception_same_venue']);
        });
    }
};
