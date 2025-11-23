@extends('components.main')
@section('breadcrumbs')
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Absensi</li>
    </ol>
    <h6 class="font-weight-bolder mb-0">Absensi Kelas yang Saya Ajar</h6>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Daftar Jadwal Mengajar</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive pb-2 px-3">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">No</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Kelas</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Mata Pelajaran</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Hari</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Jam</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($slots as $slot)
                                    @php
                                        $kelas = optional($slot->jadwal)->kelas;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $kelas?->nama_kelas ?? '-' }}</td>
                                        <td>{{ $slot->mapel->nama_mapel ?? '-' }}</td>
                                        <td>{{ strtoupper($slot->jadwal->hari ?? '-') }}</td>
                                        <td>{{ $slot->jam_mulai }} - {{ $slot->jam_selesai }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('guru.absensi.show', $slot->id) }}"
                                                class="btn btn-sm btn-primary">Ambil Absensi</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-sm text-muted">Belum ada jadwal mengajar.</td>
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
