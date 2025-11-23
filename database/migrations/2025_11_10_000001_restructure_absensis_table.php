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
        Schema::dropIfExists('absensis');

        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_jadwal_id')->constrained('detail_jadwals')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mapels')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->date('tanggal_pertemuan');
            $table->unsignedInteger('pertemuan_ke')->default(1);
            $table->enum('status_absen', ['hadir', 'sakit', 'izin', 'alpa'])->default('hadir');
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['detail_jadwal_id', 'siswa_id', 'tanggal_pertemuan'], 'absensi_detail_siswa_tanggal_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');

        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->enum('status_absen', ['masuk', 'sakit', 'izin', 'tidak masuk'])->default('tidak masuk');
            $table->string('role');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }
};
