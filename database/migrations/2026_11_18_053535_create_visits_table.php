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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('route_name')->nullable();
            /**
             * A salted hash of IP + user agent instead of the raw IP: enough to
             * count unique visitors, but not personal data we have to safeguard.
             */
            $table->string('visitor_hash', 64);
            $table->string('device', 20);
            $table->string('referrer_host')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->index();

            $table->index(['created_at', 'visitor_hash']);
            $table->index(['created_at', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
