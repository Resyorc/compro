@extends('components.main')
@section('breadcrumbs')
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="/guru/absensi">Absensi</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Input</li>
    </ol>
    <h6 class="font-weight-bolder mb-0">Absensi {{ $kelas->nama_kelas }}</h6>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Informasi Jadwal</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p class="text-secondary text-sm mb-1">Kelas</p>
                            <h6>{{ $kelas->nama_kelas }}</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="text-secondary text-sm mb-1">Mata Pelajaran</p>
                            <h6>{{ $slot->mapel->nama_mapel ?? '-' }}</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="text-secondary text-sm mb-1">Hari & Jam</p>
                            <h6>{{ strtoupper($slot->jadwal->hari ?? '-') }} | {{ $slot->jam_mulai }} - {{ $slot->jam_selesai }}</h6>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('guru.absensi.show', $slot->id) }}" class="d-flex gap-2">
                                <input type="date" name="tanggal" value="{{ $selectedDate }}" class="form-control"
                                    max="{{ now()->format('Y-m-d') }}">
                                <button type="submit" class="btn btn-outline-primary">Ganti Tanggal</button>
                            </form>
                        </div>
                        <div class="col-md-6 text-md-end text-sm-start mt-3 mt-md-0">
                            <span class="badge bg-secondary text-sm">Pertemuan aktif otomatis berdasarkan tanggal</span>
                        </div>
                    </div>
                    <form action="{{ route('guru.absensi.store', $slot->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $selectedDate }}">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">No</th>
                                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Nama Siswa</th>
                                        <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($students as $student)
                                        @php
                                            $current = $existing->get($student->id);
                                            $currentStatus = $current->status_absen ?? 'hadir';
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="text-sm font-weight-bold">{{ $student->nama }}</span>
                                                <small class="d-block text-muted">NIS: {{ $student->nis }}</small>
                                            </td>
                                            <td class="text-center">
                                                <select name="attendance[{{ $student->id }}]" class="form-select">
                                                    @foreach ($statuses as $value => $label)
                                                        <option value="{{ $value }}" @selected($currentStatus === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-sm text-muted py-4">
                                                Belum ada siswa pada kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($students->isNotEmpty())
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary">Simpan Absensi</button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
