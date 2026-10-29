<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catatan opsional yang dititipkan perusahaan saat mengubah status lamaran,
     * misalnya instruksi tindak lanjut setelah pelamar diterima. Menjadi kanal
     * pesan satu arah dari perusahaan ke pelamar tanpa fitur percakapan.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('status_note')->nullable()->after('status_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('status_note');
        });
    }
};
