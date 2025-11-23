@extends('components.main')

@section('breadcrumbs')
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Performa Kelas</li>
    </ol>
    <h6 class="font-weight-bolder mb-0">Performa Kelas Saya</h6>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Ringkasan Performa</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <p class="text-secondary text-sm mb-1">Nama</p>
                            <h6 class="mb-0">{{ $siswa->nama }}</h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <p class="text-secondary text-sm mb-1">Kelas</p>
                            <h6 class="mb-0">{{ $siswa->kelas->nama_kelas ?? '-' }}</h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <p class="text-secondary text-sm mb-1">Tahun Ajaran Aktif</p>
                            <h6 class="mb-0">
                                @if ($activeAkademik)
                                    {{ $activeAkademik->tahun_ajaran }} | Semester {{ ucfirst($activeAkademik->semester) }}
                                @else
                                    -
                                @endif
                            </h6>
                        </div>
                    </div>
                    <form method="GET" class="row g-3 align-items-end mb-4">
                        <div class="col-sm-4 col-md-3">
                            <label for="semester" class="form-label text-sm">Semester</label>
                            <select id="semester" name="semester" class="form-select" onchange="this.form.submit()">
                                @foreach ($availableSemesters as $semester)
                                    <option value="{{ $semester }}" @selected($selectedSemester === $semester)>
                                        Semester {{ $semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Perbarui</button>
                        </div>
                        <div class="col-12 col-md-6 text-md-end text-sm-start">
                            <span class="badge bg-secondary text-sm">Nilai dan absensi dihitung per mata pelajaran.</span>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Mata Pelajaran</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Guru</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Rata Nilai</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Nilai Huruf</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Hadir</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Izin</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Sakit</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Alpha</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Total</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">% Hadir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($summaries as $summary)
                                    @php
                                        $nilai = $summary['nilai'];
                                        $attendance = $summary['attendance'];
                                        $averageScore = data_get($nilai, 'rata_nilai');
                                        $gradeLetter = data_get($nilai, 'nilai_huruf');
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="text-sm font-weight-bold">{{ $summary['mapel']->nama_mapel ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm">{{ $summary['guru'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            {{ $averageScore !== null ? number_format($averageScore, 2) : '-' }}
                                        </td>
                                        <td class="text-center text-uppercase">{{ $gradeLetter ?? '-' }}</td>
                                        <td class="text-center">{{ $attendance['hadir'] }}</td>
                                        <td class="text-center">{{ $attendance['izin'] }}</td>
                                        <td class="text-center">{{ $attendance['sakit'] }}</td>
                                        <td class="text-center">{{ $attendance['alpa'] }}</td>
                                        <td class="text-center">{{ $attendance['total'] }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $attendance['rate'] >= 90 ? 'bg-gradient-success' : ($attendance['rate'] >= 75 ? 'bg-gradient-warning' : 'bg-gradient-danger') }}">
                                                {{ number_format($attendance['rate'], 2) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            Belum ada data nilai atau absensi untuk semester ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
