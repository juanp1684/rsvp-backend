<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->date('late_rsvp_deadline')->nullable()->after('rsvp_deadline');
        });

        Schema::table('invitees', function (Blueprint $table) {
            $table->enum('type', ['regular', 'late'])->default('regular')->after('event_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('late_rsvp_deadline');
        });

        Schema::table('invitees', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
