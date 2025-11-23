<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Detail_jadwal;
use App\Models\Kelas;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AbsensiController extends Controller
{
    /**
     * Rekap absensi untuk admin berdasarkan kelas & mata pelajaran.
     */
    public function showAbsensiAdmin(Request $request)
    {
        $detailJadwals = Detail_jadwal::with(['mapel', 'guru', 'jadwal.kelas'])
            ->whereHas('jadwal.kelas')
            ->orderBy('id')
            ->get();

        $selectedDetailId = (int) $request->input('detail_jadwal_id', optional($detailJadwals->first())->id);
        $selectedDetail = $detailJadwals->firstWhere('id', $selectedDetailId);

        $records = collect();
        if ($selectedDetail) {
            $records = Absensi::with('siswa')
                ->where('detail_jadwal_id', $selectedDetail->id)
                ->orderByDesc('tanggal_pertemuan')
                ->get()
                ->groupBy('tanggal_pertemuan');
        }

        return view('pages.akademik.absensi.absensi-admin', [
            'detailJadwals' => $detailJadwals,
            'selectedDetail' => $selectedDetail,
            'records' => $records,
        ])->with('title', 'Rekap Absensi Kelas');
    }

    /**
     * Daftar kelas/mapel yang diajar guru (untuk pilihan absensi).
     */
    public function guruIndex()
    {
        $guru = optional(auth()->user())->guru;
        abort_if(!$guru, 403);

        $slots = Detail_jadwal::with(['mapel', 'jadwal.kelas', 'jadwal.akademik'])
            ->where('id_guru', $guru->id)
            ->whereHas('jadwal.kelas')
            ->whereHas('jadwal.akademik', function ($query) {
                $query->where('selected', 1);
            })
            ->orderBy('id_jadwal')
            ->get();

        return view('pages.akademik.absensi.guru.index', [
            'slots' => $slots,
        ])->with('title', 'Absensi Kelas Saya');
    }

    /**
     * Form absensi untuk kelas & mata pelajaran tertentu.
     */
    public function guruShow(Request $request, Detail_jadwal $detailJadwal)
    {
        $guru = $this->ensureGuruOwnsDetail($detailJadwal);
        $kelas = optional($detailJadwal->jadwal)->kelas;
        abort_if(!$kelas, 404);

        $selectedDate = $request->filled('tanggal')
            ? Carbon::parse($request->input('tanggal'))->toDateString()
            : Carbon::today()->toDateString();

        $students = $this->activeStudents($kelas);

        $existing = Absensi::where('detail_jadwal_id', $detailJadwal->id)
            ->where('tanggal_pertemuan', $selectedDate)
            ->get()
            ->keyBy('siswa_id');

        return view('pages.akademik.absensi.guru.form', [
            'slot' => $detailJadwal,
            'kelas' => $kelas,
            'students' => $students,
            'selectedDate' => $selectedDate,
            'existing' => $existing,
            'statuses' => $this->statusOptions(),
        ])->with('title', 'Absensi ' . $kelas->nama_kelas);
    }

    /**
     * Simpan absensi guru per kelas & tanggal.
     */
    public function guruStore(Request $request, Detail_jadwal $detailJadwal)
    {
        $guru = $this->ensureGuruOwnsDetail($detailJadwal);
        $kelas = optional($detailJadwal->jadwal)->kelas;
        abort_if(!$kelas, 404);

        $students = $this->activeStudents($kelas)->keyBy('id');

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'attendance' => ['required', 'array'],
        ]);

        $tanggal = Carbon::parse($validated['tanggal'])->toDateString();
        $pertemuanKe = $this->determinePertemuanKe($detailJadwal, $tanggal);

        foreach ($validated['attendance'] as $siswaId => $status) {
            if (!$students->has($siswaId)) {
                continue;
            }

            $statusValidated = $this->validateStatus($status);

            Absensi::updateOrCreate(
                [
                    'detail_jadwal_id' => $detailJadwal->id,
                    'siswa_id' => $siswaId,
                    'tanggal_pertemuan' => $tanggal,
                ],
                [
                    'kelas_id' => $kelas->id,
                    'mapel_id' => $detailJadwal->id_mapel,
                    'guru_id' => $guru->id,
                    'pertemuan_ke' => $pertemuanKe,
                    'status_absen' => $statusValidated,
                ]
            );
        }

        return back()->with('toast_success', 'Absensi kelas berhasil disimpan.');
    }

    /**
     * Pastikan guru yang login memiliki akses ke jadwal tersebut.
     */
    protected function ensureGuruOwnsDetail(Detail_jadwal $detailJadwal)
    {
        $guru = optional(auth()->user())->guru;
        abort_if(!$guru || $detailJadwal->id_guru !== $guru->id, 403);

        $akademikSelected = optional(optional($detailJadwal->jadwal)->akademik)->selected;
        abort_if(!$akademikSelected, 404);

        return $guru;
    }

    /**
     * Hitung pertemuan ke-berapa untuk jadwal tertentu.
     */
    protected function determinePertemuanKe(Detail_jadwal $detailJadwal, string $tanggal): int
    {
        $existing = Absensi::where('detail_jadwal_id', $detailJadwal->id)
            ->where('tanggal_pertemuan', $tanggal)
            ->first();

        if ($existing) {
            return $existing->pertemuan_ke;
        }

        $last = Absensi::where('detail_jadwal_id', $detailJadwal->id)->max('pertemuan_ke');

        return $last ? $last + 1 : 1;
    }

    /**
     * Daftar siswa aktif pada kelas yang dipilih.
     */
    protected function activeStudents(Kelas $kelas): Collection
    {
        return Siswa::where('id_kelas', $kelas->id)
            ->whereIn('status', ['bukan pindahan', 'pindahan'])
            ->orderBy('nama')
            ->get();
    }

    protected function statusOptions(): array
    {
        return [
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpa' => 'Alpha',
        ];
    }

    protected function validateStatus(string $status): string
    {
        $status = strtolower($status);

        abort_unless(array_key_exists($status, $this->statusOptions()), 422, 'Status absensi tidak valid.');

        return $status;
    }
}
