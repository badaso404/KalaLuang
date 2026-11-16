<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The skill catalogue is grouped by sector on the profile form, and a
     * skill a job seeker types themselves has no sector yet.
     */
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name')->index();
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};
