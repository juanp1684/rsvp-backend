<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('confirm_attending_message')->nullable()->after('confirm_attending_image');
            $table->string('confirm_declined_message')->nullable()->after('confirm_declined_image');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['confirm_attending_message', 'confirm_declined_message']);
        });
    }
};
