<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Akademik;
use App\Models\Detail_jadwal;
use App\Models\Detail_nilai;
use App\Models\Mapel;
use App\Models\Nilai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $siswa = optional(auth()->user())->siswa;
        abort_if(!$siswa, 403);

        $activeAkademik = Akademik::where('selected', 1)->first();
        $availableSemesters = [1, 2];
        $selectedSemester = (int) $request->input('semester', $this->defaultSemester());

        if (!in_array($selectedSemester, $availableSemesters, true)) {
            $selectedSemester = $this->defaultSemester();
        }

        $detailSlots = Detail_jadwal::with(['mapel', 'guru', 'jadwal.akademik'])
            ->whereHas('jadwal', function ($query) use ($siswa, $activeAkademik) {
                $query->where('id_kelas', $siswa->id_kelas);
                if ($activeAkademik) {
                    $query->where('id_akademik', $activeAkademik->id);
                }
            })
            ->get();

        $gradeData = $this->gradeRecordsFor($siswa, $selectedSemester);
        $nilaiRecords = $gradeData['records'];

        $absensiMapelColumn = $this->absensiMapelColumn();
        $absensiSiswaColumn = $this->absensiSiswaColumn();
        $absensiKelasColumn = $this->absensiKelasColumn();
        $attendanceStats = Absensi::select(
                DB::raw("{$absensiMapelColumn} as mapel_reference"),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status_absen = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN status_absen = 'izin' THEN 1 ELSE 0 END) as izin"),
                DB::raw("SUM(CASE WHEN status_absen = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN status_absen = 'alpa' THEN 1 ELSE 0 END) as alpa")
            )
            ->where($absensiSiswaColumn, $siswa->id)
            ->where($absensiKelasColumn, $siswa->id_kelas)
            ->whereNotNull($absensiMapelColumn)
            ->groupBy($absensiMapelColumn)
            ->get()
            ->keyBy('mapel_reference');

        $mapelIds = collect()
            ->merge($detailSlots->pluck('id_mapel'))
            ->merge($gradeData['mapel_ids'])
            ->merge($attendanceStats->keys())
            ->filter()
            ->unique()
            ->values();

        $mapels = $mapelIds->isNotEmpty()
            ? Mapel::whereIn('id', $mapelIds)->get()->keyBy('id')
            : collect();

        $summaries = $mapelIds->map(function ($mapelId) use ($mapels, $detailSlots, $nilaiRecords, $attendanceStats) {
            $mapel = $mapels->get($mapelId);
            if (!$mapel) {
                return null;
            }

            $guruNames = $detailSlots->where('id_mapel', $mapelId)
                ->map(fn ($slot) => optional($slot->guru)->nama)
                ->filter()
                ->unique()
                ->implode(', ');

            $attendance = $attendanceStats->get($mapelId);
            $totalSessions = (int) data_get($attendance, 'total', 0);
            $hadir = (int) data_get($attendance, 'hadir', 0);
            $izin = (int) data_get($attendance, 'izin', 0);
            $sakit = (int) data_get($attendance, 'sakit', 0);
            $alpa = (int) data_get($attendance, 'alpa', 0);
            $attendanceRate = $totalSessions > 0 ? round(($hadir / $totalSessions) * 100, 2) : 0;

            return [
                'mapel' => $mapel,
                'guru' => $guruNames ?: '-',
                'nilai' => $nilaiRecords->get($mapelId),
                'attendance' => [
                    'total' => $totalSessions,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpa' => $alpa,
                    'rate' => $attendanceRate,
                ],
            ];
        })->filter();

        return view('pages.siswa.progress', [
            'siswa' => $siswa,
            'summaries' => $summaries,
            'selectedSemester' => $selectedSemester,
            'availableSemesters' => $availableSemesters,
            'activeAkademik' => $activeAkademik,
        ])->with('title', 'Performa Kelas Saya');
    }

    protected function defaultSemester(): int
    {
        return now()->month >= 7 ? 1 : 2;
    }

    protected function absensiMapelColumn(): string
    {
        static $column = null;
        if ($column === null) {
            $absensi = new Absensi();
            if ($this->columnExists($absensi, 'mapel_id')) {
                $column = 'mapel_id';
            } elseif ($this->columnExists($absensi, 'id_mapel')) {
                $column = 'id_mapel';
            } else {
                $column = 'mapel_id';
            }
        }

        return $column;
    }

    protected function absensiSiswaColumn(): string
    {
        static $column = null;
        if ($column === null) {
            $absensi = new Absensi();
            $column = $this->columnExists($absensi, 'siswa_id') ? 'siswa_id' : 'id_siswa';
        }

        return $column;
    }

    protected function absensiKelasColumn(): string
    {
        static $column = null;
        if ($column === null) {
            $absensi = new Absensi();
            $column = $this->columnExists($absensi, 'kelas_id') ? 'kelas_id' : 'id_kelas';
        }

        return $column;
    }

    protected function detailNilaiMapelColumn(): string
    {
        static $column = null;
        if ($column === null) {
            $detail = new Detail_nilai();
            if ($this->columnExists($detail, 'id_mapel')) {
                $column = 'id_mapel';
            } elseif ($this->columnExists($detail, 'mapel_id')) {
                $column = 'mapel_id';
            } else {
                $column = 'id_mapel';
            }
        }

        return $column;
    }

    protected function gradeRecordsFor($siswa, int $semester): array
    {
        $mapelColumn = $this->nilaiMapelColumn();
        $siswaColumn = $this->nilaiSiswaColumn();

        if ($mapelColumn) {
            $records = Nilai::where($siswaColumn, $siswa->id)
                ->whereNotNull($mapelColumn)
                ->where('semester', $semester)
                ->get()
                ->mapWithKeys(function ($nilai) use ($mapelColumn) {
                    $mapelKey = $nilai->{$mapelColumn};
                    if (!$mapelKey) {
                        return [];
                    }

                    return [$mapelKey => [
                        'rata_nilai' => $nilai->rata_nilai ?? null,
                        'nilai_huruf' => $nilai->nilai_huruf ?? null,
                        'source' => $nilai,
                    ]];
                });

            return [
                'records' => $records,
                'mapel_ids' => $records->keys()->values(),
            ];
        }

        $detailMapelColumn = $this->detailNilaiMapelColumn();
        $records = Detail_nilai::with('nilai')
            ->whereHas('nilai', function ($query) use ($siswaColumn, $siswa, $semester) {
                $query->where($siswaColumn, $siswa->id)
                    ->where('semester', $semester);
            })
            ->get()
            ->groupBy($detailMapelColumn)
            ->map(function ($group) {
                $score = round($group->avg('nilai_akademik'), 2);

                return [
                    'rata_nilai' => $score,
                    'nilai_huruf' => $this->letterGrade($score),
                    'source' => $group->first(),
                ];
            });

        return [
            'records' => $records,
            'mapel_ids' => $records->keys()->values(),
        ];
    }

    protected function letterGrade(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        if ($score >= 85) {
            return 'A';
        }

        if ($score >= 70) {
            return 'B';
        }

        if ($score >= 55) {
            return 'C';
        }

        if ($score >= 40) {
            return 'D';
        }

        return 'E';
    }

    protected function nilaiMapelColumn(): ?string
    {
        static $column = null;
        if ($column === null) {
            $nilai = new Nilai();
            if ($this->columnExists($nilai, 'mapel_id')) {
                $column = 'mapel_id';
            } elseif ($this->columnExists($nilai, 'id_mapel')) {
                $column = 'id_mapel';
            } else {
                $column = null;
            }
        }

        return $column;
    }

    protected function nilaiSiswaColumn(): string
    {
        static $column = null;
        if ($column === null) {
            $nilai = new Nilai();
            $column = $this->columnExists($nilai, 'siswa_id') ? 'siswa_id' : 'id_siswa';
        }

        return $column;
    }

    protected function columnExists(Model $model, string $column): bool
    {
        $connection = $model->getConnectionName() ?: config('database.default');
        return Schema::connection($connection)->hasColumn($model->getTable(), $column);
    }
}
