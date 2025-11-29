<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Mapel;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiProfilingClient
{
    protected string $baseUrl;
    protected int $timeoutSeconds;

    public function __construct(?string $baseUrl = null, ?int $timeoutSeconds = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.ai_profiling.base_url'), '/');
        $this->timeoutSeconds = $timeoutSeconds ?? (int) config('services.ai_profiling.timeout', 60);
    }

    /**
     * Panggil layanan AI untuk mengambil hasil profiling seorang siswa.
     */
    public function profileStudent(Siswa $siswa): array
    {
        // Pastikan semua relasi dipreload untuk menghindari N+1
        $siswa->loadMissing(['kelas', 'nilai.detail_nilai', 'absensi']);

        $payload = [
            'student'        => $this->buildStudentContext($siswa),
            'academic'       => $this->buildAcademicPayload($siswa),
            'attendance'     => $this->buildAttendancePayload($siswa),
            // 'achievements'   => $this->buildAchievementsPayload($siswa),
            // 'behavior_notes' => $this->buildBehaviorNotesPayload($siswa),
        ];

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->acceptJson()
                ->asJson()
                ->post($this->endpoint('/api/v1/summarize-profile'), $payload)
                ->throw();

            return $response->json();

        } catch (RequestException $exception) {

            throw new RuntimeException(
                sprintf(
                    "Gagal menghubungi layanan AI Profiling (%s): %s",
                    $this->endpoint('/api/v1/summarize-profile'),
                    $exception->getMessage()
                ),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Helper endpoint builder
     */
    protected function endpoint(string $path): string
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Bangun konteks identitas siswa
     */
    private function buildStudentContext(Siswa $siswa): array
    {
        return [
            'id'         => $siswa->id,
            'name'       => $siswa->nama,
            'class_name' => $siswa->kelas->nama_kelas ?? '-',
            'gender'     => $siswa->jenis_kelamin ?? null,
        ];
    }

    /**
     * Ringkasan akademik siswa
     */
    private function buildAcademicPayload(Siswa $siswa): array
    {
        $detailNilai = collect($siswa->nilai)
            ->flatMap(fn ($n) => $n['detail_nilai'])
            ->filter(fn ($dn) => isset($dn['nilai_akademik']))
            ->values();

            
            if ($detailNilai->isEmpty()) {
            return [
                'average_score' => 0,
                'subjects' => [],
            ];
        }

        $avg = round($detailNilai->avg('nilai_akademik'), 2);
        
        $subjects = $detailNilai->map(function ($dn) {
            $mapel = Mapel::find($dn['id_mapel']);
            return [
                'subject_id' => $dn['id_mapel'],
                'name'       => $mapel->nama_mapel ?? 'Unknown',
                'score'      => (float) $dn['nilai_akademik'],
            ];
        })->values()->all();
        
        dd($avg, $subjects);
        return [
            'average_score' => $avg,
            'subjects' => $subjects,
        ];

    }

    /**
     * Ringkasan kehadiran siswa
     */
    private function buildAttendancePayload(Siswa $siswa): array
    {
        $total = $siswa->absensi->count();
        $hadir = $siswa->absensi->where('status_absen', 'hadir')->count();

        $percentage = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

        return [
            'present_percentage' => $percentage,
        ];
    }

    /**
     * Prestasi siswa
     */
    // private function buildAchievementsPayload(Siswa $siswa): array
    // {
    //     return $siswa->prestasis->pluck('nama')->values()->all() ?? [];
    // }

    /**
     * Catatan perilaku siswa
     */
    // private function buildBehaviorNotesPayload(Siswa $siswa): array
    // {
    //     return $siswa->perilaku->pluck('catatan')->values()->all() ?? [];
    // }

    // protected function buildStudentContext(Siswa $siswa): array
    // {
    //     return [
    //         'id' => $siswa->id,
    //         'name' => $siswa->nama,
    //         'class_name' => optional($siswa->kelas)->nama_kelas,
    //         'gender' => $siswa->jenis_kelamin,
    //     ];
    // }

    /**
     * Bentuk data nilai per mata pelajaran.
     */
    // protected function buildGradesPayload(Siswa $siswa): array
    // {
    //     $nilaiRecords = $siswa->nilai()
    //         ->with(['detail_nilai.mapel'])
    //         ->latest()
    //         ->get();

    //     return $nilaiRecords->flatMap(function ($nilai) {
    //         $weight = $this->weightForExamType($nilai->jenis_nilai ?? null);

    //         if ($nilai->relationLoaded('detail_nilai') && $nilai->detail_nilai->isNotEmpty()) {
    //             return $nilai->detail_nilai->map(function ($detail) use ($weight) {
    //                 return [
    //                     'subject' => optional($detail->mapel)->nama_mapel ?? 'Mapel ' . $detail->id_mapel,
    //                     'score' => (float) ($detail->nilai_akademik ?? 0),
    //                     'weight' => $weight,
    //                 ];
    //             });
    //         }

    //         $aggregatedScore = data_get($nilai, 'rata_nilai');
    //         if (is_null($aggregatedScore)) {
    //             return [];
    //         }

    //         return [[
    //             'subject' => 'Ringkasan ' . strtoupper($nilai->jenis_nilai ?? 'nilai'),
    //             'score' => (float) $aggregatedScore,
    //             'weight' => $weight,
    //         ]];
    //     })->values()->all();
    // }

    // /**
    //  * Hitung ringkasan absensi siswa.
    //  */
    // protected function buildAttendancePayload(Siswa $siswa): array
    // {
    //     $stats = $siswa->absensi()
    //         ->selectRaw('LOWER(status_absen) as status, COUNT(*) as total')
    //         ->groupByRaw('LOWER(status_absen)')
    //         ->pluck('total', 'status');

    //     $totalSessions = (int) $stats->sum();
    //     $present = (int) ($stats['hadir'] ?? 0);
    //     $sick = (int) ($stats['sakit'] ?? 0);
    //     $excused = (int) ($stats['izin'] ?? 0);
    //     $unexcused = (int) ($stats['alpa'] ?? 0) + (int) ($stats['alpha'] ?? 0) + (int) ($stats['tanpa_keterangan'] ?? 0);

    //     if ($totalSessions === 0) {
    //         $totalSessions = 1;
    //         $present = 1;
    //     }

    //     return [
    //         'total_sessions' => $totalSessions,
    //         'present' => $present,
    //         'sick' => $sick,
    //         'excused' => $excused,
    //         'unexcused' => $unexcused,
    //     ];
    // }

    // protected function weightForExamType(?string $jenisNilai): float
    // {
    //     return match ($jenisNilai) {
    //         'uas' => 1.2,
    //         'uts' => 1.0,
    //         default => 0.8,
    //     };
    // }
}
