<?php

namespace App\Services;

use App\Models\Siswa;
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
        $this->timeoutSeconds = $timeoutSeconds ?? (int) config('services.ai_profiling.timeout', 5);
    }

    /**
     * Panggil layanan AI untuk mengambil hasil profiling seorang siswa.
     */
    public function profileStudent(Siswa $siswa): array
    {
        $payload = [
            'student' => $this->buildStudentContext($siswa),
            'grades' => $this->buildGradesPayload($siswa),
            'attendance' => $this->buildAttendancePayload($siswa),
        ];

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->acceptJson()
                ->post($this->endpoint('/api/v1/profile'), $payload)
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Gagal menghubungi layanan AI Profiling: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return $response->json();
    }

    protected function endpoint(string $path): string
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    protected function buildStudentContext(Siswa $siswa): array
    {
        return [
            'id' => $siswa->id,
            'name' => $siswa->nama,
            'class_name' => optional($siswa->kelas)->nama_kelas,
            'gender' => $siswa->jenis_kelamin,
        ];
    }

    /**
     * Bentuk data nilai per mata pelajaran.
     */
    protected function buildGradesPayload(Siswa $siswa): array
    {
        $nilaiRecords = $siswa->nilai()
            ->with(['detail_nilai.mapel'])
            ->latest()
            ->get();

        return $nilaiRecords->flatMap(function ($nilai) {
            $weight = $this->weightForExamType($nilai->jenis_nilai ?? null);

            if ($nilai->relationLoaded('detail_nilai') && $nilai->detail_nilai->isNotEmpty()) {
                return $nilai->detail_nilai->map(function ($detail) use ($weight) {
                    return [
                        'subject' => optional($detail->mapel)->nama_mapel ?? 'Mapel ' . $detail->id_mapel,
                        'score' => (float) ($detail->nilai_akademik ?? 0),
                        'weight' => $weight,
                    ];
                });
            }

            $aggregatedScore = data_get($nilai, 'rata_nilai');
            if (is_null($aggregatedScore)) {
                return [];
            }

            return [[
                'subject' => 'Ringkasan ' . strtoupper($nilai->jenis_nilai ?? 'nilai'),
                'score' => (float) $aggregatedScore,
                'weight' => $weight,
            ]];
        })->values()->all();
    }

    /**
     * Hitung ringkasan absensi siswa.
     */
    protected function buildAttendancePayload(Siswa $siswa): array
    {
        $stats = $siswa->absensi()
            ->selectRaw('LOWER(status_absen) as status, COUNT(*) as total')
            ->groupByRaw('LOWER(status_absen)')
            ->pluck('total', 'status');

        $totalSessions = (int) $stats->sum();
        $present = (int) ($stats['hadir'] ?? 0);
        $sick = (int) ($stats['sakit'] ?? 0);
        $excused = (int) ($stats['izin'] ?? 0);
        $unexcused = (int) ($stats['alpa'] ?? 0) + (int) ($stats['alpha'] ?? 0) + (int) ($stats['tanpa_keterangan'] ?? 0);

        if ($totalSessions === 0) {
            $totalSessions = 1;
            $present = 1;
        }

        return [
            'total_sessions' => $totalSessions,
            'present' => $present,
            'sick' => $sick,
            'excused' => $excused,
            'unexcused' => $unexcused,
        ];
    }

    protected function weightForExamType(?string $jenisNilai): float
    {
        return match ($jenisNilai) {
            'uas' => 1.2,
            'uts' => 1.0,
            default => 0.8,
        };
    }
}
