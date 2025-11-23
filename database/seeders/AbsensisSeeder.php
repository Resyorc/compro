<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Detail_jadwal;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AbsensisSeeder extends Seeder
{
    public function run(): void
    {
        Absensi::truncate();

        $detailJadwals = Detail_jadwal::with([
            'guru',
            'mapel',
            'jadwal.kelas.siswa' => function ($query) {
                $query->whereIn('status', ['bukan pindahan', 'pindahan']);
            },
        ])->get();

        $statusOptions = ['hadir', 'izin', 'sakit', 'alpa'];

        foreach ($detailJadwals as $slot) {
            $kelas = optional($slot->jadwal)->kelas;
            if (!$kelas || !$slot->guru || !$slot->mapel) {
                continue;
            }

            $students = $kelas->siswa;
            if ($students->isEmpty()) {
                continue;
            }

            for ($week = 3; $week >= 1; $week--) {
                $tanggal = Carbon::today()->subWeeks($week)->startOfWeek()->addDays(mt_rand(0, 4));
                $pertemuanKe = $week;

                foreach ($students as $student) {
                    Absensi::create([
                        'detail_jadwal_id' => $slot->id,
                        'kelas_id' => $kelas->id,
                        'mapel_id' => $slot->id_mapel,
                        'guru_id' => $slot->id_guru,
                        'siswa_id' => $student->id,
                        'tanggal_pertemuan' => $tanggal->toDateString(),
                        'pertemuan_ke' => $pertemuanKe,
                        'status_absen' => $statusOptions[array_rand($statusOptions)],
                    ]);
                }
            }
        }
    }
}
