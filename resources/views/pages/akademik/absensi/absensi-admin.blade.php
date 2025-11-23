@extends('components.main')
@section('breadcrumbs')
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Absensi</li>
    </ol>
    <h6 class="font-weight-bolder mb-0">Rekap Absensi Kelas</h6>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Pilihan Kelas & Mata Pelajaran</h6>
                    </div>
                </div>
                <div class="card-body">
                    @if ($detailJadwals->isEmpty())
                        <div class="alert alert-warning mb-0">
                            Belum ada jadwal mengajar sehingga absensi tidak tersedia.
                        </div>
                    @else
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label for="detail_jadwal_id" class="form-label text-sm">Pilih Kelas</label>
                                <select name="detail_jadwal_id" id="detail_jadwal_id" class="form-select">
                                    @foreach ($detailJadwals as $slot)
                                        @php
                                            $kelas = optional($slot->jadwal)->kelas;
                                        @endphp
                                        <option value="{{ $slot->id }}" @selected(optional($selectedDetail)->id === $slot->id)>
                                            {{ $kelas?->nama_kelas ?? 'Tanpa Kelas' }} — {{ $slot->mapel->nama_mapel ?? 'Tanpa Mapel' }}
                                            ({{ $slot->guru->nama ?? 'Tanpa Guru' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Tampilkan Rekap</button>
                            </div>
                        </form>

                        @if (!$selectedDetail)
                            <div class="alert alert-info mt-4 mb-0">
                                Pilih salah satu kelas untuk melihat data absensi.
                            </div>
                        @else
                            @php
                                $kelas = optional($selectedDetail->jadwal)->kelas;
                            @endphp
                            <div class="mt-4">
                                <h6 class="text-secondary mb-1">Informasi Kelas</h6>
                                <p class="mb-2 text-sm">
                                    <strong>Kelas:</strong> {{ $kelas?->nama_kelas ?? '-' }} <br>
                                    <strong>Mata Pelajaran:</strong> {{ $selectedDetail->mapel->nama_mapel ?? '-' }} <br>
                                    <strong>Guru Pengampu:</strong> {{ $selectedDetail->guru->nama ?? '-' }}
                                </p>
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                                    Pertemuan
                                                </th>
                                                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                                    Tanggal
                                                </th>
                                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                                    Siswa
                                                </th>
                                                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($records as $tanggal => $items)
                                                @php
                                                    $pertemuanKe = $items->first()->pertemuan_ke ?? 1;
                                                    $rowspan = $items->count();
                                                    $formattedDate = \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');
                                                @endphp
                                                @foreach ($items as $index => $record)
                                                    <tr>
                                                        @if ($index === 0)
                                                            <td class="text-center" rowspan="{{ $rowspan }}">
                                                                Ke-{{ $pertemuanKe }}
                                                            </td>
                                                            <td class="text-center" rowspan="{{ $rowspan }}">
                                                                {{ $formattedDate }}
                                                            </td>
                                                        @endif
                                                        <td>{{ $record->siswa->nama ?? '-' }}</td>
                                                        <td class="text-center text-uppercase text-sm">
                                                            <span class="badge bg-{{ $record->status_absen === 'hadir' ? 'success' : ($record->status_absen === 'izin' ? 'warning' : ($record->status_absen === 'sakit' ? 'info' : 'danger')) }}">
                                                                {{ ucfirst($record->status_absen) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-sm text-muted py-4">
                                                        Belum ada data absensi untuk kelas ini.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
