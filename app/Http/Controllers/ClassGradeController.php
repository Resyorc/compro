<?php

namespace App\Http\Controllers;

use App\Models\Akademik;
use App\Models\Detail_jadwal;
use App\Models\Detail_nilai;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Nilai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ClassGradeController extends Controller
{
    public function index(): View
    {
        $classes = Kelas::withCount(['siswa as siswa_active_count' => function ($query) {
            $query->whereIn('status', ['bukan pindahan', 'pindahan']);
        }])->with('guru')->orderBy('nama_kelas')->get();

        return view('pages.akademik.relasi-kelas.index', [
            'classes' => $classes,
            'title' => 'Relasi Siswa & Nilai',
        ]);
    }

    public function show(Kelas $kelas): View
    {
        $kelas->load([
            'siswa' => function ($query) {
                $query->whereIn('status', ['bukan pindahan', 'pindahan'])->orderBy('nama');
            },
            'siswa.user',
        ]);
        $mapelIds = $this->mapelIdsForClass($kelas->id);
        $mapels = $mapelIds->isNotEmpty()
            ? Mapel::whereIn('id', $mapelIds)->orderBy('nama_mapel')->get()
            : Mapel::orderBy('nama_mapel')->get();

        $akademik = $this->activeAkademik();
        $scores = $this->buildScoreMatrix($kelas->siswa, $mapels->pluck('id'), $akademik);

        return view('pages.akademik.relasi-kelas.show', [
            'kelas' => $kelas,
            'mapels' => $mapels,
            'scores' => $scores,
            'akademik' => $akademik,
            'title' => 'Kelola Nilai ' . $kelas->nama_kelas,
        ]);
    }

    public function store(Request $request, Kelas $kelas, Mapel $mapel): RedirectResponse
    {
        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.uts' => 'nullable|numeric|min:0|max:100',
            'grades.*.uas' => 'nullable|numeric|min:0|max:100',
        ]);

        $akademik = $this->activeAkademik();
        if (!$akademik) {
            return back()->with('toast_error', 'Tahun ajaran aktif belum disetel.');
        }

        $guruId = $this->resolveGuruId($kelas->id, $mapel->id) ?? Guru::value('id');
        $kelasKe = $this->guessKelasKe($kelas->nama_kelas);

        foreach ($kelas->siswa as $siswa) {
            $studentGrades = $validated['grades'][$siswa->id] ?? null;
            if (!$studentGrades) {
                continue;
            }

            foreach (['uts', 'uas'] as $jenis) {
                $score = $studentGrades[$jenis] ?? null;
                if ($score === null || $score === '') {
                    continue;
                }

                $nilai = Nilai::firstOrCreate(
                    [
                        'jenis_nilai' => $jenis,
                        'kelas_ke' => $kelasKe,
                        'id_siswa' => $siswa->id,
                        'id_akademik' => $akademik->id,
                    ],
                    [
                        'sakit' => 0,
                        'izin' => 0,
                        'tanpa_keterangan' => 0,
                    ]
                );

                Detail_nilai::updateOrCreate(
                    [
                        'id_nilai' => $nilai->id,
                        'id_mapel' => $mapel->id,
                    ],
                    [
                        'nilai_akademik' => $score,
                        'id_guru' => $guruId,
                    ]
                );
            }
        }

        return back()->with('toast_success', 'Nilai kelas berhasil diperbarui.');
    }

    protected function activeAkademik(): ?Akademik
    {
        return Akademik::where('selected', 1)->latest('updated_at')->first()
            ?? Akademik::latest('tahun_ajaran')->first();
    }

    protected function guessKelasKe(?string $namaKelas): string
    {
        if (!$namaKelas) {
            return 'X';
        }

        $upper = Str::upper($namaKelas);
        if (Str::contains($upper, 'XII')) {
            return 'XII';
        }
        if (Str::contains($upper, 'XI')) {
            return 'XI';
        }

        return 'X';
    }

    protected function mapelIdsForClass(int $kelasId)
    {
        return Detail_jadwal::whereHas('jadwal', function ($query) use ($kelasId) {
            $query->where('id_kelas', $kelasId);
        })->pluck('id_mapel')->unique();
    }

    protected function buildScoreMatrix($students, $mapelIds, ?Akademik $akademik): array
    {
        if (!$akademik || $students->isEmpty()) {
            return [];
        }

        $records = Nilai::with(['detail_nilai' => function ($query) use ($mapelIds) {
            if ($mapelIds->isNotEmpty()) {
                $query->whereIn('id_mapel', $mapelIds);
            }
        }])->where('id_akademik', $akademik->id)
            ->whereIn('id_siswa', $students->pluck('id'))
            ->get();

        $scores = [];
        foreach ($records as $record) {
            $jenis = Str::lower($record->jenis_nilai);
            foreach ($record->detail_nilai as $detail) {
                $scores[$detail->id_mapel][$record->id_siswa][$jenis] = $detail->nilai_akademik;
            }
        }

        return $scores;
    }

    protected function resolveGuruId(int $kelasId, int $mapelId): ?int
    {
        return Detail_jadwal::where('id_mapel', $mapelId)
            ->whereHas('jadwal', function ($query) use ($kelasId) {
                $query->where('id_kelas', $kelasId);
            })
            ->value('id_guru');
    }
}
