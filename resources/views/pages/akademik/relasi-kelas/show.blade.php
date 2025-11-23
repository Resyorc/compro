@extends('components.main')

@section('breadcrumbs')
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('class-grade.index') }}">Relasi
                Kelas</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">{{ $kelas->nama_kelas }}</li>
    </ol>
    <h6 class="font-weight-bolder mb-0">Kelola Nilai {{ $kelas->nama_kelas }}</h6>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Informasi Kelas</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Nama Kelas</p>
                            <h6>{{ $kelas->nama_kelas }}</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Wali Kelas</p>
                            <h6>{{ optional($kelas->guru)->nama ?? '-' }}</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Jumlah Siswa</p>
                            <h6>{{ $kelas->siswa->count() }}</h6>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Tahun Ajaran Aktif</p>
                            <h6>{{ optional($akademik)->tahun_ajaran ?? '-' }} ({{ optional($akademik)->semester ?? '-' }})</h6>
                        </div>
                        <div class="col-md-8">
                            <p class="text-sm text-secondary mb-1">Catatan</p>
                            <p class="text-sm text-muted mb-0">Perbarui nilai per mata pelajaran. Nilai otomatis dibuat
                                apabila belum tersedia.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($mapels->isEmpty())
        <div class="alert alert-warning" role="alert">
            Belum ada mata pelajaran yang terhubung dengan kelas ini.
        </div>
    @endif

    @foreach ($mapels as $mapel)
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-secondary shadow-secondary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                            <h6 class="text-white text-capitalize mb-0">{{ $mapel->nama_mapel }}</h6>
                            <span class="text-xs text-white-50">Nilai per siswa</span>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-3">
                        <form action="{{ route('class-grade.store', [$kelas, $mapel]) }}" method="POST">
                            @csrf
                            <div class="table-responsive px-3">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                No
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Siswa
                                            </th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Nilai UTS
                                            </th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Nilai UAS
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($kelas->siswa as $index => $siswa)
                                            @php
                                                $mapelScores = $scores[$mapel->id][$siswa->id] ?? [];
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <span class="text-sm text-secondary">{{ $loop->iteration }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-sm font-weight-bold">{{ $siswa->nama }}</span>
                                                        <small class="text-xs text-secondary">NIS: {{ $siswa->nis }}</small>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" name="grades[{{ $siswa->id }}][uts]" min="0"
                                                        max="100" step="1" class="form-control text-center"
                                                        value="{{ $mapelScores['uts'] ?? '' }}" placeholder="-">
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" name="grades[{{ $siswa->id }}][uas]" min="0"
                                                        max="100" step="1" class="form-control text-center"
                                                        value="{{ $mapelScores['uas'] ?? '' }}" placeholder="-">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end px-3">
                                <button type="submit" class="btn btn-primary mt-3">Simpan Nilai {{ $mapel->nama_mapel }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
