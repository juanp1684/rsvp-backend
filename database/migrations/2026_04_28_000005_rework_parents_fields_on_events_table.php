<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['partner1_parents', 'partner2_parents']);
            $table->string('partner1_parent1')->nullable()->after('name');
            $table->string('partner1_parent2')->nullable()->after('partner1_parent1');
            $table->string('partner2_parent1')->nullable()->after('partner1_parent2');
            $table->string('partner2_parent2')->nullable()->after('partner2_parent1');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['partner1_parent1', 'partner1_parent2', 'partner2_parent1', 'partner2_parent2']);
            $table->string('partner1_parents')->nullable()->after('name');
            $table->string('partner2_parents')->nullable()->after('partner1_parents');
        });
    }
};
