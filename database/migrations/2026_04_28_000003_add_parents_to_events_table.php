<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('partner1_parents')->nullable()->after('name');
            $table->string('partner2_parents')->nullable()->after('partner1_parents');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['partner1_parents', 'partner2_parents']);
        });
    }
};
